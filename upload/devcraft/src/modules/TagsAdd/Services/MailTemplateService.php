<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Services;

use DLEPlugins;
use DevCraft\Core\Support\ParseTemplateTags;

/**
 * PM через DLE 20 conversations API + ParseFilter.
 */
final class MailTemplateService {

	/**
	 * Рендер шаблона: теги новости DLE + модульные плейсхолдеры.
	 *
	 * @param   array<string, string>  $vars  Модульные {user} / {suggested_tags} / …
	 * @param   array<string, mixed>   $news  Строка post (если есть — ParseTemplateTags)
	 */
	public function render(string $template, array $vars, array $news = []): string {
		if($news !== []) {
			return ParseTemplateTags::apply($template, $news, $vars);
		}

		$out = $template;

		foreach($vars as $key => $value) {
			$out = str_replace($key, $value, $out);
		}

		return $out;
	}

	/**
	 * Отправляет ЛС получателю. Возвращает false, если пользователь не найден.
	 */
	public function sendPm(string $toName, string $fromName, string $subject, string $body): bool {
		global $db, $config, $member_id;

		$toName   = trim($toName);
		$fromName = trim($fromName);

		if($toName === '') {
			return false;
		}

		$to = $db->super_query(
			'SELECT user_id, name, email FROM ' . USERPREFIX . "_users WHERE name='" . $db->safesql($toName) . "'",
		);

		if(empty($to['user_id'])) {
			return false;
		}

		$from = $db->super_query(
			'SELECT user_id, name FROM ' . USERPREFIX . "_users WHERE name='" . $db->safesql($fromName) . "'",
		);

		$senderId = (int) ($from['user_id'] ?? ($member_id['user_id'] ?? 0));

		if($senderId <= 0) {
			$senderId = (int) $to['user_id'];
		}

		// Как в DLE autoload (plugins.class.php): HTMLPurifier до ParseFilter
		if(!class_exists('HTMLPurifier_Config', false)) {
			require_once DLEPlugins::Check(ENGINE_DIR . '/classes/htmlpurifier/HTMLPurifier.standalone.php');
		}

		if(!class_exists('ParseFilter', false)) {
			require_once DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php');
		}

		$wysiwyg = !empty($config['allow_pm_wysiwyg']);

		if($wysiwyg) {
			// Как engine/ajax/pm.php
			$allowedTags    = [
				'dlehide[class|data-allowed-groups|contenteditable]',
				'div[id|align|style|class|data-commenttime|data-commentuser|data-commentid|data-commentpostid|data-commentgast|contenteditable]',
				'span[style|class|data-userurl|data-username|contenteditable]',
				'p[align|style|class]',
				'pre[class]',
				'code',
				'br',
				'strong',
				'em',
				'ul',
				'li',
				'ol',
				'b',
				'u',
				'i',
				's',
				'hr',
				'a[href|target|style|class|title|data-encode]',
			];
			$parse          = new \ParseFilter($allowedTags);
			$parse->wysiwyg = true;
		} else {
			$parse = new \ParseFilter();
		}

		$parse->safe_mode   = true;
		$parse->remove_html = false;
		$body               = trim($body);

		// Шаблоны с \n + HTML-ссылками: без br строки схлопываются в HTML-ЛС
		if($wysiwyg && $body !== '' && !preg_match('/<(p|div|br|li|ul|ol)\b/i', $body)) {
			$body = nl2br($body, false);
		}

		if($wysiwyg) {
			$message = $db->safesql($parse->BB_Parse($parse->process($body)));
		} else {
			$parse->allowbbcodes = false;
			$message             = $db->safesql($parse->BB_Parse($parse->process($body), false));
		}

		$subj = $db->safesql($subject);
		$time = time();

		$db->query(
			'INSERT INTO ' . USERPREFIX
			.
			"_conversations (subject, created_at, updated_at, sender_id, recipient_id) VALUES ('{$subj}', '{$time}', '{$time}', '{$senderId}', '{$to['user_id']}')",
		);
		$conversationId = (int) $db->insert_id();
		$db->query(
			'INSERT INTO ' . USERPREFIX
			.
			"_conversation_users (user_id, conversation_id) VALUES ('{$to['user_id']}', '{$conversationId}') ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)",
		);
		$db->query(
			'INSERT INTO ' . USERPREFIX
			.
			"_conversations_messages (conversation_id, sender_id, content, created_at) VALUES ('{$conversationId}', '{$senderId}', '{$message}', '{$time}')",
		);

		$this->recountPm((int) $to['user_id']);

		return true;
	}

	private function recountPm(int $userId): void {
		global $db;

		if($userId <= 0) {
			return;
		}

		$db->query(
			'UPDATE ' . USERPREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1 WHERE user_id='{$userId}'",
		);
	}

	/**
	 * @param   array<string, mixed>  $userRow
	 */
	public function userWantsNotify(array $userRow, string $field, string $event): bool {
		$field = trim($field);

		if($field === '') {
			return true;
		}

		$xfields = (string) ($userRow['xfields'] ?? '');
		$value   = '';

		foreach(explode('||', $xfields) as $part) {
			$xf = explode('|', $part, 2);

			if(($xf[0] ?? '') === $field) {
				$value = mb_strtolower(trim((string) ($xf[1] ?? '')));
				break;
			}
		}

		if($value === '' || $value === 'all') {
			return true;
		}

		if($value === 'none') {
			return false;
		}

		return $value === $event || str_contains($value, $event);
	}

}

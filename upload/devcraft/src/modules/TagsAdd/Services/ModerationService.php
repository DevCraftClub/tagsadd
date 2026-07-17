<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Services;

use DevCraft\Core\Application;
use DevCraft\Core\Support\DataManager;
use DevCraft\Core\Support\ParseTemplateTags;
use DevCraft\Modules\TagsAdd\Models\TagSuggestion;

/**
 * Модерация: approve / reject / delete.
 */
final class ModerationService {

	private ConfigNormalizer $normalizer;
	private TagNormalizer $tags;
	private TagSyncService $sync;
	private MailTemplateService $mail;

	public function __construct(
		?ConfigNormalizer $normalizer = null,
		?TagNormalizer $tags = null,
		?TagSyncService $sync = null,
		?MailTemplateService $mail = null,
	) {
		$this->normalizer = $normalizer ?? new ConfigNormalizer();
		$this->tags       = $tags ?? new TagNormalizer();
		$this->sync       = $sync ?? new TagSyncService($this->tags);
		$this->mail       = $mail ?? new MailTemplateService();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function config(): array {
		return $this->normalizer->normalize(DataManager::getConfig('tags_add', null, 'tagsadd'));
	}

	public function approve(int $id, ?string $tagsOverride = null): void {
		global $db;

		$entity = $this->find($id);

		if($entity === null) {
			throw new \RuntimeException(__('Предложение не найдено'));
		}

		$config   = $this->config();
		$proposed = $this->tags->parse($tagsOverride !== null ? $tagsOverride : $entity->tags);
		$this->sync->apply($entity->news_id, $proposed, $config);
		$this->notifyUser($entity, 'onadd', 'user_mail_approve_title', 'user_mail_approve_body', [
			'%tags%'   => $this->tags->toCsv($proposed),
			'%reason%' => '',
		]);
		$db->query('DELETE FROM ' . PREFIX . "_tags_add WHERE id='{$id}'");
	}

	public function reject(int $id, string $reason = ''): void {
		global $db;

		$entity = $this->find($id);

		if($entity === null) {
			throw new \RuntimeException(__('Предложение не найдено'));
		}

		$this->notifyUser($entity, 'ondel', 'user_mail_reject_title', 'user_mail_reject_body', [
			'%tags%'   => $entity->tags,
			'%reason%' => $reason,
		]);
		$db->query('DELETE FROM ' . PREFIX . "_tags_add WHERE id='{$id}'");
	}

	public function delete(int $id): void {
		global $db;

		$db->query('DELETE FROM ' . PREFIX . "_tags_add WHERE id='{$id}'");
	}

	public function saveTags(int $id, string $tags): void {
		global $db;

		$entity = $this->find($id);

		if($entity === null) {
			throw new \RuntimeException(__('Предложение не найдено'));
		}

		$normalized = $this->tags->toCsv($this->tags->parse($tags));
		$db->query(
			'UPDATE ' . PREFIX . "_tags_add SET tags='" . $db->safesql($normalized) . "' WHERE id='{$id}'",
		);
	}

	/**
	 * @param list<int> $ids
	 */
	public function bulk(string $action, array $ids, string $reason = ''): int {
		$count = 0;

		foreach($ids as $id) {
			$id = (int) $id;

			if($id <= 0) {
				continue;
			}

			try {
				match ($action) {
					'approve' => $this->approve($id),
					'reject'  => $this->reject($id, $reason),
					'delete'  => $this->delete($id),
					default   => null,
				};
				$count++;
			} catch(\Throwable) {
				// пропускаем битые id
			}
		}

		return $count;
	}

	private function find(int $id): ?TagSuggestion {
		/** @var TagSuggestion|null $entity */
		$entity = Application::instance()->database()->repository(TagSuggestion::class)->findOneById($id);

		return $entity;
	}

	/**
	 * @param array<string, string> $extra
	 */
	private function notifyUser(
		TagSuggestion $entity,
		string $event,
		string $titleKey,
		string $bodyKey,
		array $extra,
	): void {
		global $db, $config;

		if($entity->user_id <= 0) {
			return;
		}

		$cfg  = $this->config();
		$user = $db->super_query(
			'SELECT user_id, name, xfields FROM ' . USERPREFIX . "_users WHERE user_id='{$entity->user_id}'",
		);

		if(empty($user['user_id'])) {
			return;
		}

		if(!$this->mail->userWantsNotify($user, (string) $cfg['user_inform_field'], $event)) {
			return;
		}

		$news = $this->loadNewsRow($entity->news_id);
		$vars = array_merge($this->buildVars($news, (string) ($user['name'] ?? ''), $entity), $extra);
		$title = $this->mail->render((string) $cfg[$titleKey], $vars, $news);
		$body  = $this->mail->render((string) $cfg[$bodyKey], $vars, $news);
		$from  = (string) ($cfg['mail_from'] !== '' ? $cfg['mail_from'] : ($config['admin_mail'] ?? 'admin'));

		$this->mail->sendPm((string) $user['name'], $from, $title, $body);
	}

	/**
	 * @param array<string, mixed> $news
	 *
	 * @return array<string, string>
	 */
	public function buildVars(array $news, string $userName, TagSuggestion $entity): array {
		return [
			'%title%'     => ParseTemplateTags::title($news),
			'%user%'      => $userName,
			'%link%'      => ParseTemplateTags::fullLink($news),
			'%tags%'      => $entity->tags,
			'%adminlink%' => DataManager::normalizeUrl('?mod=tags_add', [
				'action' => 'edit',
				'id'     => $entity->id,
			]),
			'%reason%'    => '',
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public function loadNewsRow(int $newsId): array {
		global $db;

		if($newsId <= 0) {
			return [];
		}

		$row = $db->super_query(
			'SELECT p.id, p.title, p.alt_name, p.category, p.date, p.autor, p.short_story, p.full_story, p.xfields,'
			. ' p.comm_num, p.fixed, p.allow_comm, p.approve, p.tags,'
			. ' e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.editdate, e.editor, e.reason, e.view_edit'
			. ' FROM ' . PREFIX . '_post p'
			. ' LEFT JOIN ' . PREFIX . "_post_extras e ON e.news_id=p.id WHERE p.id='{$newsId}'",
		);

		return is_array($row) ? $row : [];
	}

	/** @deprecated Используйте ParseTemplateTags::fullLink() */
	public function newsUrl(array $news): string {
		return ParseTemplateTags::fullLink($news);
	}

}

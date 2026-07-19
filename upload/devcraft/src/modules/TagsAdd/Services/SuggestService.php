<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Services;

use DevCraft\Core\Application;
use DevCraft\Core\Support\DataManager;
use DevCraft\Modules\TagsAdd\Models\TagSuggestion;
use DevCraft\Modules\TagsAdd\Repositories\TagSuggestionRepository;

/**
 * Создание предложения тегов с сайта.
 */
final class SuggestService {

	private ConfigNormalizer $normalizer;
	private TagNormalizer $tags;
	private MailTemplateService $mail;
	private ModerationService $moderation;

	public function __construct(
		?ConfigNormalizer $normalizer = null,
		?TagNormalizer $tags = null,
		?MailTemplateService $mail = null,
		?ModerationService $moderation = null,
	) {
		$this->normalizer = $normalizer ?? new ConfigNormalizer();
		$this->tags       = $tags ?? new TagNormalizer();
		$this->mail       = $mail ?? new MailTemplateService();
		$this->moderation = $moderation ?? new ModerationService($this->normalizer, $this->tags, null, $this->mail);
	}

	/**
	 * @throws \RuntimeException
	 */
	public function suggest(int $newsId, string $rawTags): TagSuggestion {
		global $member_id, $is_logged, $config;

		$cfg = $this->normalizer->normalize(DataManager::getConfig('tags_add', null, 'tagsadd'));

		$logged = !empty($is_logged) && !empty($member_id['user_id']);

		if(!$logged && empty($cfg['allow_guests'])) {
			throw new \RuntimeException(__('Гостям запрещено предлагать теги'));
		}

		$newsId = max(0, $newsId);

		if($newsId === 0) {
			throw new \RuntimeException(__('Новость не указана'));
		}

		$news = $this->moderation->loadNewsRow($newsId);

		if($news === []) {
			throw new \RuntimeException(__('Новость не найдена'));
		}

		$parsed = $this->tags->parse($rawTags);

		if($parsed === []) {
			throw new \RuntimeException(__('Укажите хотя бы один тег'));
		}

		$userId = $logged ? (int) $member_id['user_id'] : 0;
		$csv    = $this->tags->toCsv($parsed);

		$entity          = new TagSuggestion();
		$entity->news_id = $newsId;
		$entity->user_id = $userId;
		$entity->tags    = $csv;
		$entity->date    = new \DateTimeImmutable();
		$this->repo()->saveEntity($entity);

		$userName = $logged ? (string) ($member_id['name'] ?? __('Гость')) : __('Гость');
		$vars     = $this->moderation->buildVars($news, $userName, $entity);
		$from     = (string) ($cfg['mail_from'] !== '' ? $cfg['mail_from'] : ($config['admin_mail'] ?? 'admin'));

		if(!empty($cfg['notify_admin']) && $cfg['admin_name'] !== '') {
			$this->mail->sendPm(
				(string) $cfg['admin_name'],
				$from,
				$this->mail->render((string) $cfg['admin_mail_title'], $vars, $news),
				$this->mail->render((string) $cfg['admin_mail_body'], $vars, $news),
			);
		}

		if($logged && $this->mail->userWantsNotify($member_id, (string) $cfg['user_inform_field'], 'onsend')) {
			$this->mail->sendPm(
				(string) $member_id['name'],
				$from,
				$this->mail->render((string) $cfg['user_mail_send_title'], $vars, $news),
				$this->mail->render((string) $cfg['user_mail_send_body'], $vars, $news),
			);
		}

		return $entity;
	}

	private function repo(): TagSuggestionRepository {
		/** @var TagSuggestionRepository $repo */
		$repo = Application::instance()->database()->repository(TagSuggestion::class);

		return $repo;
	}

}

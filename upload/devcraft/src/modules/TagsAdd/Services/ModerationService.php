<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Services;

use DevCraft\Modules\TagsAdd\TagsAddIdentity;

use DevCraft\Core\Application;
use DevCraft\Builders\QueryBuilder;
use DevCraft\Core\Support\DataManager;
use DevCraft\Core\Support\DleDataService;
use DevCraft\Core\Support\ParseTemplateTags;
use DevCraft\Modules\TagsAdd\Models\TagSuggestion;
use DevCraft\Modules\TagsAdd\Repositories\TagSuggestionRepository;

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
		return $this->normalizer->normalize(DataManager::getConfig(TagsAddIdentity::code(), null, 'tagsadd'));
	}

	public function approve(int $id, ?string $tagsOverride = null): void {
		$entity = $this->find($id);

		if($entity === null) {
			throw new \RuntimeException(__('Предложение не найдено'));
		}

		$config   = $this->config();
		$proposed = $this->tags->parse($tagsOverride !== null ? $tagsOverride : $entity->tags);
		$this->sync->apply($entity->news_id, $proposed, $config);
		$this->notifyUser($entity, 'onadd', 'user_mail_approve_title', 'user_mail_approve_body', [
			'{suggested_tags}' => $this->tags->toCsv($proposed),
			'{decline_reason}' => '',
		]);
		$this->repo()->deleteEntity($entity);
	}

	public function reject(int $id, string $reason = ''): void {
		$entity = $this->find($id);

		if($entity === null) {
			throw new \RuntimeException(__('Предложение не найдено'));
		}

		$cfg    = $this->config();
		$reason = trim($reason);

		if($reason === '') {
			$reason = trim((string) ($cfg['decline_reason_default'] ?? ''));
		}

		$this->notifyUser($entity, 'ondel', 'user_mail_reject_title', 'user_mail_reject_body', [
			'{suggested_tags}' => $entity->tags,
			'{decline_reason}' => $reason,
		]);
		$this->repo()->deleteEntity($entity);
	}

	public function delete(int $id): void {
		$entity = $this->find($id);

		if($entity === null) {
			throw new \RuntimeException(__('Предложение не найдено'));
		}

		$this->repo()->deleteEntity($entity);
	}

	public function saveTags(int $id, string $tags): void {
		$entity = $this->find($id);

		if($entity === null) {
			throw new \RuntimeException(__('Предложение не найдено'));
		}

		$entity->tags = $this->tags->toCsv($this->tags->parse($tags));
		$this->repo()->saveEntity($entity);
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
		return $this->repo()->findOneById($id);
	}

	private function repo(): TagSuggestionRepository {
		/** @var TagSuggestionRepository $repo */
		$repo = Application::instance()->database()->repository(TagSuggestion::class);

		return $repo;
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
		$user = DleDataService::user(id: $entity->user_id);

		if(empty($user['user_id'])) {
			return;
		}

		if(!$this->mail->userWantsNotify($user, (string) $cfg['user_inform_field'], $event)) {
			return;
		}

		$news  = $this->loadNewsRow($entity->news_id);
		$vars  = array_merge($this->buildVars($news, (string) ($user['name'] ?? ''), $entity), $extra);
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
			'{user}'                    => $userName,
			'{suggested_tags}'          => $entity->tags,
			'{moderate_suggested_tags}' => DataManager::normalizeUrl('?mod=tags_add', [
				'action' => 'edit',
				'id'     => $entity->id(),
			]),
			'{decline_reason}'          => '',
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public function loadNewsRow(int $newsId): array {
		if($newsId <= 0) {
			return [];
		}

		$row = QueryBuilder::create('post')
			->withColumns([
				'id', 'title', 'alt_name', 'category', 'date', 'autor',
				'short_story', 'full_story', 'xfields', 'comm_num', 'fixed',
				'allow_comm', 'approve', 'tags',
			])
			->withConditionsItem('id', $newsId)
			->withLimit(1)
			->first();

		if($row === []) {
			return [];
		}

		$extras = QueryBuilder::create('post_extras')
			->withColumns([
				'news_read', 'allow_rate', 'rating', 'vote_num', 'votes',
				'editdate', 'editor', 'reason', 'view_edit',
			])
			->withConditionsItem('news_id', $newsId)
			->withLimit(1)
			->first();

		return $extras !== [] ? array_merge($row, $extras) : $row;
	}

	/** @deprecated Используйте ParseTemplateTags::fullLink() */
	public function newsUrl(array $news): string {
		return ParseTemplateTags::fullLink($news);
	}

}

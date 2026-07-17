<?php

declare(strict_types=1);

namespace DevCraft\Modules\TagsAdd\Services;

/**
 * Нормализация конфига TagsAdd и импорт legacy engine/data/tagsadd.php.
 */
final class ConfigNormalizer {

	/** @var list<string> */
	private const TARGETS = ['tags', 'xfield'];

	/**
	 * @param array<string, mixed> $config
	 *
	 * @return array<string, mixed>
	 */
	public function normalize(array $config): array {
		if($config === [] || $this->looksLegacy($config)) {
			$config = array_merge($this->defaults(), $this->fromLegacy($config));
		} else {
			$config = array_merge($this->defaults(), $config);
		}

		$config['allow_guests']      = (bool) ($config['allow_guests'] ?? false);
		$config['notify_admin']      = (bool) ($config['notify_admin'] ?? true);
		$config['xfield_link']       = (bool) ($config['xfield_link'] ?? false);
		$config['button_label']      = trim((string) ($config['button_label'] ?? 'Предложить теги'));
		$config['admin_name']        = trim((string) ($config['admin_name'] ?? ''));
		$config['mail_from']         = trim((string) ($config['mail_from'] ?? ''));
		$config['user_inform_field'] = trim((string) ($config['user_inform_field'] ?? ''));
		$config['xfield_name']       = trim((string) ($config['xfield_name'] ?? ''));

		$target = (string) ($config['insert_target'] ?? 'tags');
		$config['insert_target'] = in_array($target, self::TARGETS, true) ? $target : 'tags';

		foreach([
			'admin_mail_title', 'admin_mail_body',
			'user_mail_send_title', 'user_mail_send_body',
			'user_mail_approve_title', 'user_mail_approve_body',
			'user_mail_reject_title', 'user_mail_reject_body',
		] as $key) {
			$config[$key] = (string) ($config[$key] ?? '');
		}

		return $config;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function defaults(): array {
		return [
			'allow_guests'            => false,
			'button_label'            => 'Предложить теги',
			'notify_admin'            => true,
			'admin_name'              => '',
			'mail_from'               => '',
			'user_inform_field'       => '',
			'insert_target'           => 'tags',
			'xfield_name'             => '',
			'xfield_link'             => false,
			'admin_mail_title'        => 'Новое предложение тегов: {title}',
			'admin_mail_body'         => "Пользователь %user% предложил теги для новости «{title}»:\n%tags%\n\nСсылка: <a href=\"{full-link}\">{title}</a>\nМодерация: <a href=\"%adminlink%\">открыть</a>",
			'user_mail_send_title'    => 'Ваше предложение тегов отправлено',
			'user_mail_send_body'     => "Вы предложили теги для «{title}»:\n%tags%\n\nСсылка: <a href=\"{full-link}\">{title}</a>",
			'user_mail_approve_title' => 'Теги одобрены: {title}',
			'user_mail_approve_body'  => "Ваши теги для «{title}» одобрены:\n%tags%\n\nСсылка: <a href=\"{full-link}\">{title}</a>",
			'user_mail_reject_title'  => 'Теги отклонены: {title}',
			'user_mail_reject_body'   => "Ваши теги для «{title}» отклонены.\nПричина: %reason%\n\nСсылка: <a href=\"{full-link}\">{title}</a>",
		];
	}

	/**
	 * @param array<string, mixed> $config
	 */
	private function looksLegacy(array $config): bool {
		return isset($config['onof']) || isset($config['listcount']) || isset($config['fast']);
	}

	/**
	 * @param array<string, mixed> $legacy
	 *
	 * @return array<string, mixed>
	 */
	private function fromLegacy(array $legacy): array {
		if($legacy === []) {
			$legacy = $this->loadLegacyFile();
		}

		if($legacy === []) {
			return [];
		}

		$fast = (string) ($legacy['fast'] ?? 'tags');

		return [
			'notify_admin'            => !empty($legacy['send']),
			'user_inform_field'       => (string) ($legacy['userinform'] ?? ''),
			'admin_name'              => (string) ($legacy['admin'] ?? ''),
			'mail_from'               => (string) ($legacy['master'] ?? ''),
			'button_label'            => (string) ($legacy['button'] ?? 'Предложить теги'),
			'insert_target'           => $fast === 'field' || $fast === 'xfield' ? 'xfield' : 'tags',
			'xfield_name'             => (string) ($legacy['field'] ?? ''),
			'xfield_link'             => !empty($legacy['xflink']),
			'admin_mail_title'        => (string) ($legacy['adminmailtitle'] ?? ''),
			'admin_mail_body'         => (string) ($legacy['adminmail'] ?? ''),
			'user_mail_send_title'    => (string) ($legacy['usermailsendtitle'] ?? $legacy['usermailtitle'] ?? ''),
			'user_mail_send_body'     => (string) ($legacy['usermailsend'] ?? $legacy['usermail'] ?? ''),
			'user_mail_approve_title' => (string) ($legacy['usermailapprovetitle'] ?? ''),
			'user_mail_approve_body'  => (string) ($legacy['usermailapprove'] ?? ''),
			'user_mail_reject_title'  => (string) ($legacy['usermailrejecttitle'] ?? ''),
			'user_mail_reject_body'   => (string) ($legacy['usermailreject'] ?? ''),
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function loadLegacyFile(): array {
		$file = ENGINE_DIR . '/data/tagsadd.php';

		if(!is_file($file)) {
			return [];
		}

		$tagsconf = [];
		/** @noinspection PhpIncludeInspection */
		include $file;

		return is_array($tagsconf ?? null) ? $tagsconf : [];
	}

}

(function (window) {
	'use strict';

	function t(phrase) {
		return typeof window.__ === 'function' ? window.__(phrase) : phrase;
	}

	function boot($) {
		// jqueryui/dle_js с defer — готовы только после DOM ready
		if (!$ || typeof $.fn.dialog !== 'function') {
			console.error('[TagsAdd] Нужны jQuery и jQuery UI Dialog (dle_js / jqueryui).');
			return;
		}

		function getModal(newsId) {
			return $('#tags-add-modal-' + newsId);
		}

		function getInput(newsId) {
			return getModal(newsId).find('.tags-add-input[data-news-id="' + newsId + '"]');
		}

		function readTags(newsId) {
			return String(getInput(newsId).val() || '').trim();
		}

		function clearTags(newsId) {
			getInput(newsId).val('');
		}

		function setBusy(newsId, busy) {
			var $dialog = getModal(newsId);
			var $buttons = $();

			if ($dialog.data('ui-dialog')) {
				$buttons = $dialog.dialog('widget').find('.ui-dialog-buttonpane button');
			}

			$buttons.prop('disabled', !!busy);
			getInput(newsId).prop('disabled', !!busy);
		}

		function notifyError(message) {
			if (window.DevCraftPublic && DevCraftPublic.Ajax && typeof DevCraftPublic.Ajax.notify === 'function') {
				DevCraftPublic.Ajax.notify(t('Ошибка'), message, 'error');
				return;
			}

			if (window.DLEPush && typeof DLEPush.error === 'function') {
				DLEPush.error(message, t('Ошибка'));
				return;
			}

			console.error('[TagsAdd]', message);
		}

		function notifySuccess(message) {
			if (window.DevCraftPublic && DevCraftPublic.Ajax && typeof DevCraftPublic.Ajax.notify === 'function') {
				DevCraftPublic.Ajax.notify(t('Готово'), message, 'success');
				return;
			}

			if (window.DLEPush && typeof DLEPush.info === 'function') {
				DLEPush.info(message, t('Готово'));
				return;
			}

			console.info('[TagsAdd]', message);
		}

		function submitTags(newsId) {
			if (!window.DevCraftPublic || !DevCraftPublic.Ajax) {
				notifyError(t('Клиент отправки не загружен'));
				return;
			}

			var tags = readTags(newsId);

			if (!tags) {
				notifyError(t('Укажите хотя бы один тег'));
				return;
			}

			setBusy(newsId, true);

			DevCraftPublic.Ajax.post('tags_add', 'suggest', {
				news_id: newsId,
				tags: tags
			}).then(function (payload) {
				if (payload && payload.success) {
					clearTags(newsId);
					closeModal(newsId);
					notifySuccess(t('Теги отправлены на модерацию'));
					return;
				}

				if (!(payload && payload.notice && payload.notice.message) && !(payload && payload.error && payload.error.message)) {
					notifyError(t('Не удалось отправить теги'));
				}
			}).catch(function (err) {
				notifyError((err && err.message) ? err.message : t('Ошибка сети при отправке тегов'));
			}).finally(function () {
				setBusy(newsId, false);
			});
		}

		function ensureDialog(newsId) {
			var $el = getModal(newsId);

			if (!$el.length) {
				return $el;
			}

			if ($el.data('ui-dialog')) {
				return $el;
			}

			var buttons = {};
			buttons[t('Отправить')] = function () {
				submitTags(newsId);
			};
			buttons[t('Отмена')] = function () {
				$(this).dialog('close');
			};

			$el.dialog({
				autoOpen: false,
				width: 480,
				resizable: false,
				modal: true,
				dialogClass: 'modalfixed tags-add-popup',
				buttons: buttons
			});

			if ($(window).width() > 500) {
				$('.modalfixed.ui-dialog').css({position: 'fixed'});
				$el.dialog('option', 'position', {my: 'center', at: 'center', of: window});
			}

			return $el;
		}

		function openModal(newsId) {
			var $el = ensureDialog(newsId);

			if (!$el.length) {
				return;
			}

			$el.dialog('open');
			getInput(newsId).trigger('focus');
		}

		function closeModal(newsId) {
			var $el = getModal(newsId);

			if ($el.length && $el.data('ui-dialog')) {
				$el.dialog('close');
			}
		}

		$(document).on('click', '.tags-add-open', function (event) {
			event.preventDefault();
			openModal(parseInt($(this).data('news-id'), 10));
		});
	}

	if (typeof window.jQuery === 'function') {
		window.jQuery(boot);
		return;
	}

	document.addEventListener('DOMContentLoaded', function () {
		boot(window.jQuery);
	});
})(window);

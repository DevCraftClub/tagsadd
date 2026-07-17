(function (window) {
	'use strict';

	if (!window.DevCraft) {
		console.error('[TagsAdd] Сначала должен быть загружен DevCraft core.');
		return;
	}

	const Ajax = window.DevCraft.Ajax;
	const Metro = window.DevCraft.Metro;

	function t(key) {
		return window.__ ? window.__(key) : key;
	}

	document.addEventListener('click', function (event) {
		if (!event.target.closest('.js-settings-save') || !window.tinymce) {
			return;
		}

		window.tinymce.triggerSave();
	}, true);

	function post(method, data) {
		return Ajax.post(method, data || {});
	}

	function selectedIds() {
		return Array.from(document.querySelectorAll('.js-tags-row:checked')).map(function (el) {
			return parseInt(el.value, 10);
		}).filter(Boolean);
	}

	function removeRow(id) {
		const row = document.querySelector('tr[data-id="' + id + '"]');
		if (row) {
			row.remove();
		}
	}

	/**
	 * @returns {Promise<string|null>} причина или null при отмене
	 */
	function askReason() {
		return new Promise(function (resolve) {
			var pending;
			var settled = false;

			function done(value) {
				if (settled) {
					return;
				}
				settled = true;
				resolve(value);
			}

			if (!Metro || typeof Metro.dialogCreate !== 'function') {
				var fallback = window.prompt(t('Причина отклонения'), '');
				done(fallback === null ? null : String(fallback).trim());
				return;
			}

			Metro.dialogCreate({
				title: t('Причина отклонения'),
				content: '<textarea id="dc-tags-reject-reason" class="metro-input" rows="3" style="width:100%"></textarea>',
				closeButton: true,
				defaultActions: false,
				onClose: function () {
					done(pending === undefined ? null : pending);
				},
				customButtons: [
					{
						text: t('Отклонить'),
						cls: 'warning js-dialog-close',
						onclick: function () {
							var el = document.getElementById('dc-tags-reject-reason');
							pending = el ? String(el.value).trim() : '';
							done(pending);
						},
					},
					{
						text: t('Отмена'),
						cls: 'js-dialog-close',
						onclick: function () {
							pending = null;
							done(null);
						},
					},
				],
			});
		});
	}

	/**
	 * @returns {Promise<boolean>}
	 */
	function confirmDelete() {
		return new Promise(function (resolve) {
			var pending;
			var settled = false;

			function done(value) {
				if (settled) {
					return;
				}
				settled = true;
				resolve(value);
			}

			if (!Metro || typeof Metro.dialogCreate !== 'function') {
				done(window.confirm(t('Удалить предложение? Это действие необратимо.')));
				return;
			}

			Metro.dialogCreate({
				title: t('Удалить предложение?'),
				content: '<p>' + t('Действие необратимо. Пользователь не получит уведомление.') + '</p>',
				closeButton: true,
				defaultActions: false,
				onClose: function () {
					done(pending === true);
				},
				customButtons: [
					{
						text: t('Удалить'),
						cls: 'alert js-dialog-close',
						onclick: function () {
							pending = true;
							done(true);
						},
					},
					{
						text: t('Отмена'),
						cls: 'js-dialog-close',
						onclick: function () {
							pending = false;
							done(false);
						},
					},
				],
			});
		});
	}

	document.addEventListener('click', function (event) {
		const approve = event.target.closest('.js-tags-approve');
		const reject = event.target.closest('.js-tags-reject');
		const del = event.target.closest('.js-tags-delete');
		const save = event.target.closest('.js-tags-save');
		const bulk = event.target.closest('.js-tags-bulk');
		const checkAll = event.target.closest('.js-tags-check-all');

		if (checkAll) {
			document.querySelectorAll('.js-tags-row').forEach(function (el) {
				el.checked = checkAll.checked;
			});
			return;
		}

		if (approve) {
			const id = parseInt(approve.dataset.id, 10);
			const form = document.querySelector('.js-tags-edit-form');
			const tags = form ? (form.querySelector('[name="tags"]') || {}).value : undefined;
			post('approve', { id: id, tags: tags }).then(function (payload) {
				if (payload && payload.success) {
					removeRow(id);
					if (form) {
						window.location.href = '?mod=tags_add&action=suggestions';
					}
				}
			});
			return;
		}

		if (reject) {
			const id = parseInt(reject.dataset.id, 10);
			askReason().then(function (reason) {
				if (reason === null) {
					return;
				}
				post('reject', { id: id, reason: reason }).then(function (payload) {
					if (payload && payload.success) {
						removeRow(id);
						if (document.querySelector('.js-tags-edit-form')) {
							window.location.href = '?mod=tags_add&action=suggestions';
						}
					}
				});
			});
			return;
		}

		if (del) {
			const id = parseInt(del.dataset.id, 10);
			confirmDelete().then(function (ok) {
				if (!ok) {
					return;
				}
				post('delete', { id: id }).then(function (payload) {
					if (payload && payload.success) {
						removeRow(id);
					}
				});
			});
			return;
		}

		if (save) {
			const form = document.querySelector('.js-tags-edit-form');
			if (!form) {
				return;
			}
			post('save_suggestion', {
				id: parseInt(form.dataset.id, 10),
				tags: (form.querySelector('[name="tags"]') || {}).value || '',
			});
			return;
		}

		if (bulk) {
			const ids = selectedIds();
			if (!ids.length) {
				Ajax.notify(t('Внимание'), t('Не выбраны записи'), 'warning');
				return;
			}
			const action = bulk.dataset.action;
			const data = { action: action, ids: ids };

			const runBulk = function () {
				post('bulk_moderation', data).then(function (payload) {
					if (payload && payload.success) {
						ids.forEach(removeRow);
					}
				});
			};

			if (action === 'reject') {
				askReason().then(function (reason) {
					if (reason === null) {
						return;
					}
					data.reason = reason;
					runBulk();
				});
				return;
			}

			if (action === 'delete') {
				confirmDelete().then(function (ok) {
					if (!ok) {
						return;
					}
					runBulk();
				});
				return;
			}

			runBulk();
		}
	});
})(window);

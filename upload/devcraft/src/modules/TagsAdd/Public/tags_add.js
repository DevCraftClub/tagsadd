(function (window) {
	'use strict';

	if (!window.DevCraft) {
		console.error('[TagsAdd] Сначала должен быть загружен DevCraft core.');
		return;
	}

	const Ajax = window.DevCraft.Ajax;

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

	function askReason() {
		return window.prompt(window.__ ? window.__('Причина отклонения') : 'Причина отклонения', '') || '';
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
			post('approve', {id: id, tags: tags}).then(function (payload) {
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
			post('reject', {id: id, reason: askReason()}).then(function (payload) {
				if (payload && payload.success) {
					removeRow(id);
					if (document.querySelector('.js-tags-edit-form')) {
						window.location.href = '?mod=tags_add&action=suggestions';
					}
				}
			});
			return;
		}

		if (del) {
			const id = parseInt(del.dataset.id, 10);
			post('delete', {id: id}).then(function (payload) {
				if (payload && payload.success) {
					removeRow(id);
				}
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
				tags: (form.querySelector('[name="tags"]') || {}).value || ''
			});
			return;
		}

		if (bulk) {
			const ids = selectedIds();
			if (!ids.length) {
				Ajax.notify(window.__ ? window.__('Внимание') : 'Внимание', window.__ ? window.__('Не выбраны записи') : 'Не выбраны записи', 'warning');
				return;
			}
			const action = bulk.dataset.action;
			const data = {action: action, ids: ids};
			if (action === 'reject') {
				data.reason = askReason();
			}
			post('bulk_moderation', data).then(function (payload) {
				if (payload && payload.success) {
					ids.forEach(removeRow);
				}
			});
		}
	});
})(window);

(function () {
	let modalElement = null;
	let modalInstance = null;
	let titleElement = null;
	let bodyElement = null;
	let confirmButton = null;
	let cancelButton = null;
	let currentOptions = null;

	function ensureModal() {
		if (modalElement) return true;

		modalElement = document.getElementById('appConfirmModal');
		titleElement = document.getElementById('appConfirmModalLabel');
		bodyElement = document.getElementById('appConfirmModalBody');
		confirmButton = document.getElementById('appConfirmModalConfirm');
		cancelButton = document.getElementById('appConfirmModalCancel');

		if (!modalElement || !titleElement || !bodyElement || !confirmButton || !cancelButton || typeof bootstrap === 'undefined') {
			return false;
		}

		modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);

		confirmButton.addEventListener('click', async function () {
			if (!currentOptions) return;

			const onConfirm = currentOptions.onConfirm;
			if (typeof onConfirm !== 'function') {
				modalInstance.hide();
				return;
			}

			confirmButton.disabled = true;
			try {
				const result = await onConfirm();
				if (result !== false) {
					modalInstance.hide();
				}
			} catch (error) {
				console.error('Confirmation action failed:', error);
			} finally {
				confirmButton.disabled = false;
			}
		});

		modalElement.addEventListener('hidden.bs.modal', function () {
			currentOptions = null;
			confirmButton.className = 'btn btn-outline-success ui-button';
			confirmButton.textContent = 'Bestätigen';
			cancelButton.textContent = 'Abbrechen';
			bodyElement.textContent = '';
			titleElement.textContent = 'Bestätigung';
		});

		return true;
	}

	window.showConfirmationModal = function (options) {
		const opts = options || {};
		if (!ensureModal()) {
			console.error('Reusable confirmation modal is not available.');
			return;
		}

		currentOptions = opts;

		titleElement.textContent = opts.title || 'Bestätigung';

		if (opts.bodyHtml) {
			bodyElement.innerHTML = opts.bodyHtml;
		} else {
			bodyElement.textContent = opts.message || '';
		}

		confirmButton.textContent = opts.confirmText || 'Bestätigen';
		cancelButton.textContent = opts.cancelText || 'Abbrechen';
		confirmButton.className = 'btn ui-button ' + (opts.confirmClass || 'btn-outline-success');

		modalInstance.show();

		if (typeof opts.onShown === 'function') {
			setTimeout(function () {
				opts.onShown();
			}, 0);
		}
	};
})();

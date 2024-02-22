function showToast(header_message, sub_header_message, message, style) {
	var newToast = document.createElement('div');

	newToast.classList.add('toast');
	newToast.setAttribute('role', 'alert');
	newToast.setAttribute('aria-live', 'assertive');
	newToast.setAttribute('aria-atomic', 'true');

	var header = document.createElement('div');
	header.classList.add('toast-header');

	var Icon = document.createElement('div');
	Icon.classList.add('toast-icon');
	Icon.classList.add('toast-' + style);
	header.appendChild(Icon);

	var strong = document.createElement('strong');
	strong.classList.add('me-auto');
	strong.innerText = header_message;
	header.appendChild(strong);

	var small = document.createElement('small');
	small.classList.add('text-muted');
	small.innerText = sub_header_message;
	header.appendChild(small);

	var closeButton = document.createElement('button');
	closeButton.type = 'button';
	closeButton.classList.add('btn-close');
	closeButton.setAttribute('data-bs-dismiss', 'toast');
	closeButton.setAttribute('aria-label', 'Close');
	header.appendChild(closeButton);

	newToast.appendChild(header);

	var body = document.createElement('div');
	body.classList.add('toast-body');
	body.innerText = message;
	newToast.appendChild(body);

	document.getElementById('toastContainer').appendChild(newToast);

	var toastInstance = new bootstrap.Toast(newToast);

	toastInstance.show();
}
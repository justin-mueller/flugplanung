$(document).ready(function () {

	$('#registration-form').submit(function (event) {
		$('#register-error').addClass('d-none');
		event.preventDefault();

		var formDataArray = $(this).serializeArray();

		var windenfahrerCheckbox = $('#windenfahrer_register');
		formDataArray.push({
			name: windenfahrerCheckbox.attr('name'),
			value: windenfahrerCheckbox.is(':checked') ? '1' : '0'
		});

		var formData = $.param(formDataArray);

		$.ajax({
			url: 'register.php',
			method: 'POST',
			data: formData,
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					$('.login').addClass('flash-login');
					showToast('Juhu!', 'Das hat geklappt', 'Du bist jetzt registriert und kannst Dich direkt anmelden!', 'success');
				} else {
					$('#register-error').removeClass('d-none');
					$('#register-error').html(response.error);
				}
			},
			error: function (error) {
				$('#register-error').removeClass('d-none');
				$('#register-error').html('Das hat leider nicht geklappt. Versuch es gern nochmal…');
				console.error('Error:', error);
			}
		});
	});

	updatePreview(0);

});

function openLoginForm() {
	$('#login-container').removeClass('d-none');
	$('#registration-form').addClass('d-none');
	$('#forgot-password-container').addClass('d-none');
}

function openRegisterForm() {
	$('#login-container').addClass('d-none');
	$('#registration-form').removeClass('d-none');
	$('#forgot-password-container').addClass('d-none');
}

function openForgotPasswordForm() {
	$('#login-container').addClass('d-none');
	$('#registration-form').addClass('d-none');
	$('#forgot-password-container').removeClass('d-none');
}


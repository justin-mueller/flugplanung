function loadChatbox(scroll) {

	$.ajax({
		url: 'loadChatbox.php',
		type: 'GET',
		dataType: 'json',
		success: function (data) {
			loadChatboxMessages(data, scroll);
		},
		error: function (error) {
			console.error('Chatbox konnte nicht geladen werden!', error);
		}
	});
}

function loadChatboxMessages(data, scroll) {

	$('#chatbox-messages').empty();

	$.each(data, function (index, entry) {

		const myMessage = entry.pilot_id == User_Information.pilot_id;
		let msg;
		let avatar = `a${entry.avatar}.png`;

		if (myMessage) {
			msg = `
			<div class="d-flex flex-row justify-content-end mb-4">
				<div class="p-2 me-2 border chatbox-message-me">
					<p class="small mb-0" style="font-style: italic; font-size: 12px;">${entry.datetime}</p>
					<p class="small mb-1" style="font-weight: 700;">${entry.firstname} ${entry.lastname}</p>
					<p class="small mb-0">${entry.text}</p>
				</div>
				<img src="img/${avatar}" alt="user avatar" class="chatbox-avatar">
			</div>
			`
		} else {
			msg = `
			<div class="d-flex flex-row justify-content-start mb-4">
				<img src="img/${avatar}" alt="user avatar" class="chatbox-avatar">
				<div class="p-2 me-2 border chatbox-message-other-user">
					<p class="small mb-0" style="font-style: italic; font-size: 12px;">${entry.datetime}</p>
					<p class="small mb-1" style="font-weight: 700;">${entry.firstname} ${entry.lastname}</p>
					<p class="small mb-0">${entry.text}</p>
				</div>
			</div>
			`
		}

		$('#chatbox-messages').append(msg);
	});


	if (scroll) $('#chatbox-messages').scrollTop($('#chatbox-messages')[0].scrollHeight);

}

function enterChatboxMessage(scroll) {
	let text = $('#chatboxMessage').val()

	if (text == 'einhorn') { // easter egg :)
		$('#unicorn').addClass('move-image');
	} else if (text != '') {
		$.ajax({
			url: 'enterChatbox.php',
			type: 'POST',
			data: { text: text, pilot_id: User_Information.pilot_id },
			success: function () {
				loadChatbox(scroll);
				$('#chatboxMessage').val('')
			},
			error: function (error) {
				showToast('Oops!', 'Etwas ist schiefgegangen!', 'Deine Nachricht konnte nicht gespeichtert werden!', 'error');
				console.error('Error inserting Chatbox Message:', error.responseText);
			}
		});
	}
} 
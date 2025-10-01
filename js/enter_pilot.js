function enterPilot() {

	toggleSpinner(true);
	let prio_result = getSelectedButtons();
	let kommentar = $('#kommentar').val();
	let zeit = $('#flugtag-zeit').val();

	$.ajax({
		url: 'enter_pilot.php',
		type: 'POST',
		data: { kommentar: kommentar, prio_result: prio_result, pilotid: User_Information.pilot_id, update: (User_Information.record_present ? '1' : '0'), flugtag: flugtag_formatted, zeit: zeit },

		success: function () {
			getFlugtag();
			showToast('Juhu!', 'Das hat geklappt', 'Dein Eintrag wurde gespeichert!', 'success');
		},
		error: function (xhr, status, error) {
			showToast('Oops!', 'Etwas ist schiefgegangen!', 'Dein Eintrag wurde nicht gespeichert!', 'error');
			console.log(xhr.responseText);
		},
		complete: function () {
			toggleSpinner(false);
		}
	});
}
function delete_pilot() {

	var pilotid_delete = parseInt(User_Information.pilot_id);
	console.log(pilotid_delete);
	toggleSpinner(true);

	$.ajax({
		url: 'delete_pilot.php',
		type: 'POST',
		dataType: 'json',
		data: { pilotid_delete: pilotid_delete },

		success: function () {
			getFlugtag(); // reload the data from the database
			showToast('Juhu!', 'Das hat geklappt', 'Dein Eintrag wurde gelöscht! Wie schade, dass du nicht fliegen möchtest!', 'success');
			$('#btn_enter').removeClass('d-none');
			$('#btn_update').addClass('d-none');
			$('#btn_delete').addClass('d-none');
		},
		error: function (xhr, status, error) {
			showToast('Oops!', 'Etwas ist schiefgegangen!', 'Dein Eintrag könnte nicht gelöscht werden!', 'error');
			console.log('Fehler beim Löschen des Eintrags!');
			console.log(xhr.responseText);
		},
		complete: function () {
			toggleSpinner(false);
		}
	});
}
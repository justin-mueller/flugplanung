function betriebAusrufen(Fluggebiet) {

	toggleSpinner(true);

	if (Fluggebiet == 999) {

		FlugbetriebAbgesagt = !FlugbetriebAbgesagt;

		for (i in Flugbetrieb) {
			Flugbetrieb[i] = false;
		}

	} else if (!FlugbetriebAbgesagt) {
		Flugbetrieb[Fluggebiet] = !Flugbetrieb[Fluggebiet]
	}

	const aufbau = $('#aufbau').val();

	let postData = {
		flugtag: flugtag_formatted,
		abgesagt: FlugbetriebAbgesagt ? '1' : '0',
		aufbau: aufbau
	};
	for (let i = 0; i < SiteCount; i++) {
		postData['betrieb[' + i + ']'] = Flugbetrieb[i] ? '1' : '0';
	}

	$.ajax({
		url: 'betriebAusrufen.php',
		type: 'POST',
		data: postData,
		success: function (data) {
			betriebAbfragen();
			showToast('Juhu!', 'Das hat geklappt', 'Die Änderung in dem Betrieb wurde gespeichert!', 'success');
		},
		error: function (xhr, status, error) {
			showToast('Ups!', 'Etwas ist schiefgegangen!', 'Der Betrieb konnte nicht ausgerufen werden!', 'error');
			console.log(xhr);
			console.log(error);
		},

		complete: function () {
			toggleSpinner(false);
		}
	});
}


function betriebAbfragen() {


	toggleSpinner(true);
	$.ajax({
		url: 'betriebAbfragen.php',
		type: 'GET',
		data: { flugtag: flugtag_formatted },
		success: function (data) {

			for (let i = 0; i < SiteCount; i++) {
				Flugbetrieb[i] = data.betrieb[i] == '1';
			}
			FlugbetriebAbgesagt = data.abgesagt == '1';

			$('#abgesagt').addClass('d-none');

			for (let i = 0; i < SiteCount; i++) {
				if (Flugbetrieb[i]) {
					$(`#banner_flugbetrieb_${i}`).removeClass('d-none');
					$(`#btn_betrieb${i}`).removeClass('btn-secondary').addClass('btn-success');
				} else {
					$(`#banner_flugbetrieb_${i}`).addClass('d-none');
					$(`#btn_betrieb${i}`).addClass('btn-secondary').removeClass('btn-success');
				}
			}

				const Flugbetrieb_any = Flugbetrieb.some(value => value === true);

				if (Flugbetrieb_any) {
					$('#banner_aufbau').removeClass('d-none');
					$('#banner_aufbau div').html('Aufbau: ' + data.aufbau.slice(0, 5) + ' Uhr');
					$('#minpilotreached').addClass('d-none');
				} else {
					$('#banner_aufbau').addClass('d-none');
					if (isFlugtag) $('#minpilotreached').removeClass('d-none');
				}		

				if (FlugbetriebAbgesagt) {

					$('#abgesagt').removeClass('d-none');
	
					$('#banner_aufbau').addClass('d-none');
					$('#minpilotreached').addClass('d-none');
	
					$('#btn_betrieb_absagen').removeClass('btn-secondary');
					$('#btn_betrieb_absagen').addClass('btn-danger');
	
				} else {
					$('#btn_betrieb_absagen').addClass('btn-secondary');
					$('#btn_betrieb_absagen').removeClass('btn-danger'); 	
				}


		},
		error: function (xhr, status, error) {
			showToast('Ups!', 'Etwas ist schiefgegangen!', 'Der Flugbetrieb konnte nicht geladen werden!', 'error');			
			console.log(xhr);
			console.log(error);
		},

		complete: function () {
			toggleSpinner(false);
		}
	});
}
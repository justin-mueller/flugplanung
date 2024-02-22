var Flugbetrieb = [false, false, false];

function BetriebAusrufen(Fluggebiet) {

	toggleSpinner(true);
	Flugbetrieb[Fluggebiet] = !Flugbetrieb[Fluggebiet]

	const aufbau = $('#aufbau').val();

	$.ajax({
		url: 'betriebAusrufen.php',
		type: 'POST',
		data: {
			flugtag: flugtag_formatted,
			flugbetrieb_ngl: Flugbetrieb[0] ? '1' : '0',
			flugbetrieb_hrp: Flugbetrieb[1] ? '1' : '0',
			flugbetrieb_amd: Flugbetrieb[2] ? '1' : '0',
			aufbau: aufbau
		},
		success: function (data) {
			BetriebAbfragen();
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

function BetriebAbfragen() {
	toggleSpinner(true);
	$.ajax({
		url: 'betriebAbfragen.php',
		type: 'GET',
		data: { flugtag: flugtag_formatted },
		success: function (data) {

			Flugbetrieb[0] = data.betrieb_ngl == '1' ? true : false;
			Flugbetrieb[1] = data.betrieb_hrp == '1' ? true : false;
			Flugbetrieb[2] = data.betrieb_amd == '1' ? true : false;

			if (Flugbetrieb[0]) {
				$('#banner_flugbetrieb_0').removeClass('d-none');
				$('#btn_betrieb0').removeClass('btn-danger');
				$('#btn_betrieb0').addClass('btn-success');
			} else {
				$('#banner_flugbetrieb_0').addClass('d-none');
				$('#btn_betrieb0').addClass('btn-danger');
				$('#btn_betrieb0').removeClass('btn-success');
			}
			if (Flugbetrieb[1]) {
				$('#banner_flugbetrieb_1').removeClass('d-none');
				$('#btn_betrieb1').removeClass('btn-danger');
				$('#btn_betrieb1').addClass('btn-success');
			} else {
				$('#banner_flugbetrieb_1').addClass('d-none');
				$('#btn_betrieb1').addClass('btn-danger');
				$('#btn_betrieb1').removeClass('btn-success');
			}
			if (Flugbetrieb[2]) {
				$('#banner_flugbetrieb_2').removeClass('d-none');
				$('#btn_betrieb2').removeClass('btn-danger');
				$('#btn_betrieb2').addClass('btn-success');
			} else {
				$('#banner_flugbetrieb_2').addClass('d-none');
				$('#btn_betrieb2').addClass('btn-danger');
				$('#btn_betrieb2').removeClass('btn-success');
			}

			const Flugbetrieb_any = Flugbetrieb.some(value => value === true);

			if (Flugbetrieb_any) {
				$('#banner_aufbau').removeClass('d-none');
				$('#banner_aufbau div').html('Aufbau: ' + data.aufbau.slice(0, 5) + ' Uhr');
			} else {
				$('#banner_aufbau').addClass('d-none');

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
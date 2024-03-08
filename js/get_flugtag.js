function getRowCount(data, rowKey, targetValue, clubFilter = 0) {
	var count = 0;
	$.each(data, function (index, row) {
		if (row[rowKey] === targetValue && (clubFilter === 0 || row.VereinId === clubFilter)) {
			count++;
		}
	});
	return count;
}

function getFlugtag() {


	toggleSpinner(true);

	min_pilot_amount_reached = false;
	
	var now = new Date();
	var oneHourBack = new Date();
	oneHourBack.setHours(oneHourBack.getHours() - 1);
	User_Information.record_present = false;
	$('#tagesplanung tbody').empty();

	// Reset Buttons, Banners
	$('#btn_enter').removeClass('d-none');
	$('#btn_update').addClass('d-none');
	$('#btn_delete').addClass('d-none');

	$("[id=list_fist_choice_1]").removeClass("active");
	$("[id=list_fist_choice_2]").removeClass("active");
	$("[id=list_fist_choice_3]").removeClass("active");
	$("[id=list_alternative_1]").removeClass("active");
	$("[id=list_alternative_2]").removeClass("active");
	$("[id=list_alternative_3]").removeClass("active");
	
	$.ajax({
		url: 'get_flugtag.php',
		type: 'GET',
		data: { flugtag: flugtag_formatted },

		success: function (data) {
			
			if (typeof(data) === 'object') {
				console.log('Planung für den ' + flugtag_formatted + ' erfolgreich geladen:');
				console.log(data);

				// Problem: Failed wenn keine Daten vorhanden.Dann wird auch kein Banner angezeigt
				windenfahrer_official = data.some(value => value.windenfahrer_official == 1) ? (data.filter(o => o.windenfahrer_official == 1))[0].Pilot_ID : null;
				startleiter_official = data.some(value => value.startleiter_official == 1) ? (data.filter(o => o.startleiter_official == 1))[0].Pilot_ID : null;

				var pilot_count_all_prio_1 = [
					getRowCount(data, 'NGL', '0'),
					getRowCount(data, 'HRP', '0'),
					getRowCount(data, 'AMD', '0')
				];
				var pilot_count_all_prio_2 = [
					getRowCount(data, 'NGL', '1'),
					getRowCount(data, 'HRP', '1'),
					getRowCount(data, 'AMD', '1')
				];

				var pilot_count_hdgf_prio_1 = [
					getRowCount(data, 'NGL', '0', localClubId),
					getRowCount(data, 'HRP', '0', localClubId),
					getRowCount(data, 'AMD', '0', localClubId)
				];
				var pilot_count_hdgf_prio_2 = [
					getRowCount(data, 'NGL', '1', localClubId),
					getRowCount(data, 'HRP', '1', localClubId),
					getRowCount(data, 'AMD', '1', localClubId)
				];

				total_pilot_count_all[0] = pilot_count_all_prio_1[0] + pilot_count_all_prio_2[0];
				total_pilot_count_all[1] = pilot_count_all_prio_1[1] + pilot_count_all_prio_2[1];
				total_pilot_count_all[2] = pilot_count_all_prio_1[2] + pilot_count_all_prio_2[2];

				total_pilot_count_hdgf[0] = pilot_count_hdgf_prio_1[0] + pilot_count_hdgf_prio_2[0];
				total_pilot_count_hdgf[1] = pilot_count_hdgf_prio_1[1] + pilot_count_hdgf_prio_2[1];
				total_pilot_count_hdgf[2] = pilot_count_hdgf_prio_1[2] + pilot_count_hdgf_prio_2[2];

				min_pilot_amount_reached = total_pilot_count_hdgf[0] >= 3 || total_pilot_count_hdgf[1] >= 3 || total_pilot_count_hdgf[2] >= 3;

				let possible_areas = '';

				for (c in total_pilot_count_hdgf) {
					if (total_pilot_count_hdgf[c] >= 3) possible_areas = possible_areas + Fluggebiete[c] + ', '
				}

				var possible_areas_sliced = possible_areas.slice(0, possible_areas.length - 2);


				$.each(data, function (index, row) {

					let timestamp = new Date(row.timestamp);
					let time_ago = Math.round((now - timestamp) / 60000);
					let new_record = (timestamp >= oneHourBack && timestamp <= now) ? '<span class="badge bg-info">Neu vor ' + time_ago + ' min</span>' : '';
					let hdgf_member = row.VereinId == localClubId;
					let row_not_hdgf = hdgf_member ? '' : 'class="tr_no_hgdf"';
					let ist_startleiter = row.Pilot_ID == startleiter_official ? '<span class="badge bg-success">SL Offiziell</span>' : '';
					let windenfahrer_official_info = row.Pilot_ID == windenfahrer_official ? '<span class="badge bg-success">WF Offiziell</span>' : '';
					let windenfahrer_info = (row.ist_windenfahrer == 1 && row.Pilot_ID != windenfahrer_official) ? '<span class="badge bg-primary">WF</span>' : '';
					var newRow = $('<tr ' + row_not_hdgf + '>');

					newRow.append('<td>' + row.Pilot + ' ' + windenfahrer_info + ' ' + windenfahrer_official_info + ' ' + ist_startleiter + ' ' + new_record + '</td>');
					newRow.append('<td>' + (hdgf_member ? '<strong>' : '') + row.Verein + (hdgf_member ? '</strong>' : '') + '</td>');
					newRow.append('<td>' + replaceValueWithImage(row.NGL) + '</td>');
					newRow.append('<td>' + replaceValueWithImage(row.HRP) + '</td>');
					newRow.append('<td>' + replaceValueWithImage(row.AMD) + '</td>');
					newRow.append('<td>' + row.Kommentar + '</td>');

					$('#tagesplanung tbody').append(newRow);
				});


				var newRow = $('<tr>');
				newRow.append('<td><strong>Max. Piloten</strong></td>');
				newRow.append('<td></td>');
				newRow.append('<td>' + total_pilot_count_all[0] + '</td>');
				newRow.append('<td>' + total_pilot_count_all[1] + '</td>');
				newRow.append('<td>' + total_pilot_count_all[2] + '</td>');

				newRow.append('<td></td>');
				$('#tagesplanung tbody').append(newRow);

				var legendRow = $('<tr>');
				legendRow.append('<td colspan="6" style="font-size: small"><span class="badge bg-success">SL Offiziell</span> = Offizieller Startleiter für den Tag<br><span class="badge bg-success">WF Offiziell</span> = Offizieller Windenfahrer für den Tag<br><span class="badge bg-primary">WF</span> = Hat einen Windenfahrerschein</td>');
				$('#tagesplanung tbody').append(legendRow);

				User_Information.record_present = data.some(pilot => pilot.Pilot_ID === User_Information.pilot_id && (pilot.startleiter_official == "0" || pilot.startleiter_official == "") && (pilot.windenfahrer_official == "0" || pilot.windenfahrer_official == ""));

				if (User_Information.record_present) {
					$('#btn_enter').addClass('d-none');
					$('#btn_update').removeClass('d-none');
					$('#btn_delete').removeClass('d-none');

					let Active_Pilot_Flugtag_Data = (data.filter(o => o.Pilot_ID == User_Information.pilot_id))[0];

					$("#kommentar").val(Active_Pilot_Flugtag_Data.Kommentar)

					Active_Pilot_Choices[0] = Active_Pilot_Flugtag_Data.NGL;
					Active_Pilot_Choices[1] = Active_Pilot_Flugtag_Data.HRP;
					Active_Pilot_Choices[2] = Active_Pilot_Flugtag_Data.AMD;

					Active_Pilot_First_Choice = null;

					if (Active_Pilot_Choices[0] == 0) {
						$("[id=list_fist_choice_1]").addClass("active");
						if (Active_Pilot_Choices[1] == 1) { $("[id=list_alternative_1]").addClass("active"); }
						if (Active_Pilot_Choices[2] == 1) { $("[id=list_alternative_2]").addClass("active"); }
						Active_Pilot_First_Choice = 0;
					} else if (Active_Pilot_Choices[1] == 0) {
						$("[id=list_fist_choice_2]").addClass("active");
						if (Active_Pilot_Choices[0] == 1) { $("[id=list_alternative_1]").addClass("active"); }
						if (Active_Pilot_Choices[2] == 1) { $("[id=list_alternative_2]").addClass("active"); }
						Active_Pilot_First_Choice = 1;
					} else if (Active_Pilot_Choices[2] == 0) {
						$("[id=list_fist_choice_3]").addClass("active");
						if (Active_Pilot_Choices[0] == 1) { $("[id=list_alternative_1]").addClass("active"); }
						if (Active_Pilot_Choices[1] == 1) { $("[id=list_alternative_2]").addClass("active"); }
						Active_Pilot_First_Choice = 2;
					}

					renameAlternativeButtons(Active_Pilot_First_Choice)
				} else {

					$("[id=list_fist_choice_1]").addClass("active");
				}

				

			} else {
				windenfahrer_official = null;
				startleiter_official = null;
				$("[id=list_fist_choice_1]").addClass("active");
				
			}

			if (min_pilot_amount_reached) {


				$('#minpilotreached').html(
					'<div style="display: flex; align-items: center;">' +
						'<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:">' +
							'<use xlink:href="img/warning.svg#warning-fill"/>' +
						'</svg>' +
						'<div style="margin-left: 5px;">Es ist Flugbetrieb möglich in: ' + possible_areas_sliced + '</div>' +
					'</div>' +
					'<div id="countdown"></div>'
					
				);
			} else {
				$('#minpilotreached').html(
					'<div style="display: flex; align-items: center;">' +
						'<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Warning:">' +
							'<use xlink:href="img/warning.svg#warning-fill"/>' +
						'</svg>' +
						'<div style="margin-left: 5px;">Aktuell wird die Mindestanzahl von 3 Vereinsmitgliedern in keinem Fluggebiet erreicht!</div>' +
					'</div>' +
					'<div id="countdown"></div>'
				);
			}

			if (FlugbetriebAbgesagt) $('#minpilotreached').addClass('d-none');

			updateCountdown();

			
		},

		error: function (xhr, status, error) {
			console.log('Der Flugtag konnte nicht geladen werden');
			console.log(xhr);
			showToast('Oops!', 'Etwas ist schiefgegangen!', 'Der Flugtag konnte nicht geladen werden!', 'error');
		},

		complete: function () {

			toggleSpinner(false);
			let Active_User_Is_Startleiter = User_Information.pilot_id == startleiter_official;
			let Active_User_Is_Windenfahrer = User_Information.pilot_id == windenfahrer_official;

			let Startleiterinfotext = Active_User_Is_Startleiter ? ' <br>Du bist für diesen Tag Startleiter!' : '';
			let Windenfahrerinfotext = Active_User_Is_Windenfahrer ? ' <br>Du bist für diesen Tag Windenfahrer!' : '';

			$('#user_name_header').html('Hallo, ' + User_Information.firstname + ' ' + User_Information.lastname + '! ' + Startleiterinfotext + Windenfahrerinfotext);

			//Banner für Flugbetrieb ausrufen
			if (Active_User_Is_Startleiter) {
				$('#flugbetriebAusrufen').removeClass('d-none');
				$('#eintraege').addClass('d-none');
			} else {
				$('#flugbetriebAusrufen').addClass('d-none');
				$('#eintraege').removeClass('d-none');
			}

			if (Active_User_Is_Windenfahrer) {
				$('#btn_enter').addClass('d-none');
			}
		}
	});
}

function replaceValueWithImage(value) {
	if (value === 0) {
		return '<img src="img/stern_fav.svg" class="table-image">';
	} else if (value === 1) {
		return '<img src="img/stern.svg" class="table-image">';
	} else if (value === 2) {
		return '<img src="img/kreuz.svg" class="table-image">';
	}
	return value;
}

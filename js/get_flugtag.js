function getRowCount(data, siteIndex, targetValue, clubFilter = 0) {
	var count = 0;
	$.each(data, function (index, row) {
		if (row.sites[siteIndex] == targetValue && (clubFilter == 0 || row.VereinId == clubFilter)) {
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
	$('#btn_open_flugtag').addClass('d-none');
	$('#btn_update').addClass('d-none');
	$('#btn_delete').addClass('d-none');

	$("[id^=list_fist_choice_]").removeClass("active");
	$("[id^=list_alternative_]").removeClass("active");

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

				var pilot_count_all_prio_1 = [];
				var pilot_count_all_prio_2 = [];
				var pilot_count_hdgf_prio_1 = [];
				var pilot_count_hdgf_prio_2 = [];

				for (let i = 0; i < SiteCount; i++) {
					pilot_count_all_prio_1.push(getRowCount(data, i, 0));
					pilot_count_all_prio_2.push(getRowCount(data, i, 1));
					pilot_count_hdgf_prio_1.push(getRowCount(data, i, 0, localClubId));
					pilot_count_hdgf_prio_2.push(getRowCount(data, i, 1, localClubId));
				}

				for (let i = 0; i < SiteCount; i++) {
					total_pilot_count_all[i] = pilot_count_all_prio_1[i] + pilot_count_all_prio_2[i];
					total_pilot_count_hdgf[i] = pilot_count_hdgf_prio_1[i] + pilot_count_hdgf_prio_2[i];
				}

				min_pilot_amount_reached = total_pilot_count_hdgf.some(c => c >= 3);

				let possible_areas = '';

				for (c in total_pilot_count_hdgf) {
					if (total_pilot_count_hdgf[c] >= 3) possible_areas = possible_areas + Fluggebiete[c] + ', '
				}

				var possible_areas_sliced = possible_areas.slice(0, possible_areas.length - 2);

				$.each(data, function (index, row) {

					let timestamp = new Date(row.timestamp);
					let time_ago = Math.round((now - timestamp) / 60000);
					let new_record = (timestamp >= oneHourBack && timestamp <= now) ? '<span class="badge bg-info">Neu vor ' + time_ago + ' min</span>' : '';
					let local_club_member = row.VereinId === localClubId;
					let row_not_local_club_member = local_club_member ? '' : 'class="tr_no_hgdf"';
					let ist_startleiter = row.Pilot_ID == startleiter_official ? '<span class="badge bg-warning">SL Offiziell</span>' : '';
					let windenfahrer_official_info = row.Pilot_ID == windenfahrer_official ? '<span class="badge bg-warning">WF Offiziell</span>' : '';
					let windenfahrer_info = (row.ist_windenfahrer == 1 && row.Pilot_ID != windenfahrer_official) ? '<span class="badge bg-primary">WF</span>' : '';
					var newRow = $('<tr ' + row_not_local_club_member + '>');
					let fluggerateIMG = row.fluggeraet.includes('G') ? replaceValueWithImage('G') : ''
					fluggerateIMG += row.fluggeraet.includes('D') ? replaceValueWithImage('D') : ''
					fluggerateIMG += row.fluggeraet.includes('S') ? replaceValueWithImage('S') : '';

					newRow.append('<td>' + row.Pilot + ' ' + windenfahrer_info + ' ' + windenfahrer_official_info + ' ' + ist_startleiter + ' ' + fluggerateIMG + new_record + '</td>');
					newRow.append('<td>' + (local_club_member ? '<strong>' : '') + row.Verein + (local_club_member ? '</strong>' : '') + '</td>');
					for (let i = 0; i < SiteCount; i++) {
						newRow.append('<td>' + replaceValueWithImage(row.sites[i]) + '</td>');
					}
					newRow.append('<td>' + row.Kommentar + '</td>');

					$('#tagesplanung tbody').append(newRow);
				});


				var newRow = $('<tr>');
				newRow.append('<td><strong>Abstimmung</td>');
				newRow.append('<td></td>');
				for (let i = 0; i < SiteCount; i++) {
					newRow.append('<td>' + pilot_count_hdgf_prio_1[i] + '</td>');
				}
				newRow.append('<td></td>');
				$('#tagesplanung tbody').append(newRow);


				var newRow = $('<tr>');
				newRow.append('<td><strong>Piloten maximal</strong></td>');
				newRow.append('<td></td>');
				for (let i = 0; i < SiteCount; i++) {
					newRow.append('<td>' + total_pilot_count_all[i] + '</td>');
				}
				newRow.append('<td></td>');
				$('#tagesplanung tbody').append(newRow);




				var legendRow = $('<tr>');
				legendRow.append('<td colspan="6" style="font-size: small">Abstimmung = <img src="img/stern_fav.svg" class="table-image"></strong> Relevant für die Entscheidung des Haupt-Fluggebiets<br>Piloten maximal = <img src="img/stern_fav.svg" class="table-image"> + <img src="img/stern.svg" class="table-image"> wenn Betrieb nur in diesem Fluggebiet stattfindet<br><span class="badge bg-warning">SL Offiziell</span> = Offizieller Startleiter für den Tag<br><span class="badge bg-warning">WF Offiziell</span> = Offizieller Windenfahrer für den Tag<br><span class="badge bg-primary">WF</span> = Hat einen Windenfahrerschein</td>');
				$('#tagesplanung tbody').append(legendRow);

				User_Information.record_present = data.some(pilot => pilot.Pilot_ID === User_Information.pilot_id && (pilot.startleiter_official == "0" || pilot.startleiter_official == "") && (pilot.windenfahrer_official == "0" || pilot.windenfahrer_official == ""));

				if (User_Information.record_present) {
					$('#btn_enter').addClass('d-none');
					$('#btn_update').removeClass('d-none');
					$('#btn_delete').removeClass('d-none');

					let Active_Pilot_Flugtag_Data = (data.filter(o => o.Pilot_ID == User_Information.pilot_id))[0];

					$("#kommentar").val(Active_Pilot_Flugtag_Data.Kommentar)

					for (let i = 0; i < SiteCount; i++) {
						Active_Pilot_Choices[i] = Active_Pilot_Flugtag_Data.sites[i];
					}

					Active_Pilot_First_Choice = null;
					let firstChoiceIndex = Active_Pilot_Choices.indexOf(0);
					if (firstChoiceIndex >= 0) {
						$(`[id=list_fist_choice_${firstChoiceIndex + 1}]`).addClass("active");
						Active_Pilot_First_Choice = firstChoiceIndex;
						let altBtnIndex = 1;
						for (let i = 0; i < SiteCount; i++) {
							if (i !== firstChoiceIndex && Active_Pilot_Choices[i] == 1) {
								$(`[id=list_alternative_${altBtnIndex}]`).addClass("active");
								altBtnIndex++;
							}
						}
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

			isFlugtag = FlugtageRaw.find((item) => item.datum === flugtag_formatted) ? true : false;

			/*banner_aufbau
			minpilotreached
			no_official_flugtag
			abgesagt
			*/
			const isLocalClubMember = User_Information.vereinId == localClubId;

			if (isFlugtag) {

				$('#no_official_flugtag').addClass('d-none');
				$('#minpilotreached').removeClass('d-none');
				$('#btn_open_flugtag').addClass('d-none');


				if( isLocalClubMember && (User_Information.pilot_id != startleiter_official || User_Information.pilot_id != windenfahrer_official )) {
					$('#take_over_duty').removeClass('d-none');
					$('#take_over_duty_text').html("Ich möchte den Dienst als " +  (User_Information.windenfahrer == 0 ? "Startleiter" : "Windenfahrer") + 	" für diesen Tag übernehmen:");
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

			} else {
				$('#no_official_flugtag').removeClass('d-none');
				$('#minpilotreached').addClass('d-none');
				$('#btn_enter').addClass('d-none');
				$('#btn_update').addClass('d-none');
				$('#btn_delete').addClass('d-none');

				if (isLocalClubMember) {
					$('#btn_open_flugtag').removeClass('d-none');
				}
			}

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

			$('#user_name_header').html('Hallo, ' + User_Information.firstname + '! ' + Startleiterinfotext + Windenfahrerinfotext);

			//Banner für Flugbetrieb ausrufen
			if (Active_User_Is_Startleiter || Active_User_Is_Windenfahrer) {
				$('#flugbetriebAusrufen').removeClass('d-none');
				$('#eintraege').addClass('d-none');
			} else {
				$('#flugbetriebAusrufen').addClass('d-none');
				$('#eintraege').removeClass('d-none');
			}

			if (Active_User_Is_Windenfahrer) {
				$('#btn_enter').addClass('d-none');
			}

			// Load reparaturen counts after flugplanung data is loaded
			loadReparaturenCounts();

		}
	});
}

function openFlugtag() {
	showConfirmationModal({
		title: 'Bestätigung',
		message: 'Bist Du sicher, dass Du diesen Flugtag eröffnen möchtest?',
		confirmText: 'Bestätigen',
		cancelText: 'Abbrechen',
		confirmClass: 'btn-outline-success',
		onConfirm: function () {
			openFlugtagConfirmed();
			return true;
		}
	});
}

function confirmTakeOverDienst() {
	showConfirmationModal({
		title: 'Bestätigung',
		message: 'Ja, ich möchte diesen Dienst übernehmen!',
		confirmText: 'Bestätigen',
		cancelText: 'Abbrechen',
		confirmClass: 'btn-outline-success',
		onConfirm: function () {
			enterDienst();
			return true;
		}
	});
}

function openFlugtagConfirmed() {
	if (!flugtag_formatted) {
		showToast('Oops!', 'Etwas ist schiefgegangen!', 'Es wurde kein gültiges Datum ausgewählt!', 'error');
		return;
	}

	if (User_Information.vereinId != localClubId) {
		showToast('Oops!', 'Keine Berechtigung', 'Nur Vereinsmitglieder können einen Flugtag eröffnen!', 'error');
		return;
	}

	$.ajax({
		url: 'add_flugtag.php',
		type: 'POST',
		data: { datum: flugtag_formatted },
		success: function (response) {
			if (!response || response.success !== true) {
				showToast('Oops!', 'Etwas ist schiefgegangen!', 'Der Flugtag konnte nicht eröffnet werden!', 'error');
				return;
			}

			if (!FlugtageRaw.some(item => item.datum === flugtag_formatted)) {
				FlugtageRaw.push({ datum: flugtag_formatted });
			}

			sendAutomaticFlugtagChatMessage();

			showToast('Juhu!', 'Das hat geklappt', 'Der Flugtag wurde eröffnet!', 'success');
			loadFlugtage(false);
			getFlugtag();
			betriebAbfragen();
		},
		error: function (xhr) {
			let errorMessage = 'Der Flugtag konnte nicht eröffnet werden!';

			if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
				errorMessage = xhr.responseJSON.error;
			}

			showToast('Oops!', 'Etwas ist schiefgegangen!', errorMessage, 'error');
		}
	});
}

function sendAutomaticFlugtagChatMessage() {
	if (!flugtag_formatted) return;

	const dateParts = flugtag_formatted.split('-');
	if (dateParts.length !== 3) return;

	const formattedDate = dateParts[2] + '.' + dateParts[1] + '.' + dateParts[0];
	const text = '<i>[Automatische Nachricht]</i> Der ' + formattedDate + ' wurde von mir als Flugtag erstellt.';

	$.ajax({
		url: 'enterChatbox.php',
		type: 'POST',
		data: { text: text, pilot_id: User_Information.pilot_id },
		success: function () {
			loadChatbox(true);
		},
		error: function (error) {
			console.error('Error inserting automatic Chatbox Message:', error.responseText);
		}
	});
}

function replaceValueWithImage(value) {
	if (value == 0) {
		return '<img src="img/stern_fav.svg" class="table-image vote">';
	} else if (value == 1) {
		return '<img src="img/stern.svg" class="table-image vote">';
	} else if (value == 2) {
		return '<img src="img/kreuz.svg" class="table-image vote">';
	}

	return '<img src="img/' + value + '.png" class="table-image aircraft">';
}

// Reparaturen functionality
let reparaturenData = {};

function loadReparaturenCounts() {
	$.ajax({
		url: 'getReparaturenCounts.php',
		type: 'GET',
		success: function (data) {
			reparaturenData = data;
			updateReparaturenBadges();
			// Update modal warnings if modal is open
			if (document.getElementById('enterModal').classList.contains('show')) {
				updateEnterModalReparaturenWarnings();
			}
		},
		error: function (xhr) {
			console.log('Reparaturen counts could not be loaded');
			console.log(xhr);
		}
	});
}

function updateReparaturenBadges() {
	for (let i = 0; i < SiteCount; i++) {
		const fluggebiet = FluggebieteShort[i].toLowerCase();
		const elementId = 'reparaturen-' + fluggebiet;
		const element = document.getElementById(elementId);

		if (element && reparaturenData[i]) {
			const level0Count = reparaturenData[i].level0.count;
			const level1Count = reparaturenData[i].level1.count;

			let badgesHtml = '';
			const fluggebietUpper = FluggebieteShort[i];

			if (level0Count > 0) {
				badgesHtml += `<span class="badge bg-warning reparaturen-badge"
					data-fluggebiet="${fluggebietUpper}"
					data-level="0"
					style="cursor: pointer; margin-right: 2px;">${level0Count}</span>`;
			}

			if (level1Count > 0) {
				badgesHtml += `<span class="badge bg-danger reparaturen-badge"
					data-fluggebiet="${fluggebietUpper}"
					data-level="1"
					style="cursor: pointer;">${level1Count}</span>`;
			}

			element.innerHTML = badgesHtml;
		}
	}
}

function showReparaturenModal(fluggebiet) {
	const siteIndex = FluggebieteShort.indexOf(fluggebiet);
	if (siteIndex < 0 || !reparaturenData[siteIndex]) return;

	const level0Data = reparaturenData[siteIndex].level0;
	const level1Data = reparaturenData[siteIndex].level1;

	let modalContent = '';

	// Level 0 (Geringfügig)
	if (level0Data.count > 0) {
		modalContent += '<div class="mb-3">';
		modalContent += '<h6><span class="badge bg-warning">Geringfügig</span> (' + level0Data.count + ' Problem' + (level0Data.count > 1 ? 'e' : '') + ')</h6>';
		modalContent += '<ul class="list-group list-group-flush">';
		level0Data.texts.forEach(function(text) {
			modalContent += '<li class="list-group-item">' + text + '</li>';
		});
		modalContent += '</ul>';
		modalContent += '</div>';
	}

	// Level 1 (Flugbetrieb nicht möglich)
	if (level1Data.count > 0) {
		modalContent += '<div class="mb-3">';
		modalContent += '<h6><span class="badge bg-danger">Flugbetrieb nicht möglich</span> (' + level1Data.count + ' Problem' + (level1Data.count > 1 ? 'e' : '') + ')</h6>';
		modalContent += '<ul class="list-group list-group-flush">';
		level1Data.texts.forEach(function(text) {
			modalContent += '<li class="list-group-item">' + text + '</li>';
		});
		modalContent += '</ul>';
		modalContent += '</div>';
	}

	if (modalContent === '') {
		modalContent = '<p class="text-muted">Keine offenen Reparaturen vorhanden.</p>';
	}

	document.getElementById('modal-fluggebiet').textContent = fluggebiet;
	document.getElementById('reparaturen-modal-content').innerHTML = modalContent;

	const modal = new bootstrap.Modal(document.getElementById('reparaturenModal'));
	modal.show();
}

// Function to update reparaturen warnings in the enter modal
function updateEnterModalReparaturenWarnings() {
	const container = document.getElementById('reparaturen-warnings-container');
	if (!container) return;

	let warningsHtml = '';

	// Check each Fluggebiet for open repairs
	for (let i = 0; i < SiteCount; i++) {
		if (reparaturenData[i]) {
			const level0Count = reparaturenData[i].level0.count;
			const level1Count = reparaturenData[i].level1.count;
			const level0Texts = reparaturenData[i].level0.texts;
			const level1Texts = reparaturenData[i].level1.texts;
			const siteName = Fluggebiete[i];

			// Show critical (level 1) repairs in red
			if (level1Count > 0) {
				warningsHtml += `<div class="alert alert-danger d-flex align-items-start mb-2" role="alert">
					<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:">
						<use xlink:href="img/warning.svg#warning-fill"/>
					</svg>
					<div class="w-100">
						<strong>${siteName}: Flugbetrieb nicht möglich!</strong>
						<ul class="mb-0 mt-1 small">`;
				level1Texts.forEach(function(text) {
					warningsHtml += `<li>${text}</li>`;
				});
				warningsHtml += `</ul>
					</div>
				</div>`;
			}

			// Show minor (level 0) repairs in yellow
			if (level0Count > 0) {
				warningsHtml += `<div class="alert alert-warning d-flex align-items-start mb-2" role="alert">
					<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Warning:">
						<use xlink:href="img/warning.svg#warning-fill"/>
					</svg>
					<div class="w-100">
						<strong>${siteName}: Geringfügige Probleme</strong>
						<ul class="mb-0 mt-1 small">`;
				level0Texts.forEach(function(text) {
					warningsHtml += `<li>${text}</li>`;
				});
				warningsHtml += `</ul>
					</div>
				</div>`;
			}
		}
	}

	container.innerHTML = warningsHtml;
}

// Add event listeners for reparaturen badges
document.addEventListener('DOMContentLoaded', function() {
	// Load reparaturen counts when page loads
	loadReparaturenCounts();

	// Add click event listeners for reparaturen badges
	document.addEventListener('click', function(e) {
		if (e.target.classList.contains('reparaturen-badge')) {
			const fluggebiet = e.target.getAttribute('data-fluggebiet');
			showReparaturenModal(fluggebiet);
		}
	});

	// Update reparaturen warnings when enter modal is shown
	const enterModal = document.getElementById('enterModal');
	if (enterModal) {
		enterModal.addEventListener('show.bs.modal', function() {
			updateEnterModalReparaturenWarnings();
		});
	}
});

var zeitBarChartInstance = null;

function getRowCount(data, rowKey, targetValue, clubFilter = 0) {
	var count = 0;
	$.each(data, function (index, row) {
		if (row[rowKey] == targetValue && (clubFilter == 0 || row.VereinId == clubFilter)) {
			count++;
		}
	});
	return count;
}

// Store chart data globally for filter updates
var zeitChartData = null;
var zeitChartLabels = [];

function updateZeitBarChart(data) {
	const canvas = document.getElementById('zeitBarChart');
	if (!canvas) return;

	// Generate fixed time labels from 08:00 to 18:00
	zeitChartLabels = [];
	for (let h = 8; h <= 18; h++) {
		zeitChartLabels.push(h.toString().padStart(2, '0') + ':00');
	}

	// Initialize data structure for each Fluggebiet
	const fluggebiete = ['NGL', 'HRP', 'AMD'];
	zeitChartData = {};
	
	fluggebiete.forEach(fg => {
		zeitChartData[fg] = {
			counts: {},
			names: {}
		};
		zeitChartLabels.forEach(label => {
			zeitChartData[fg].counts[label] = 0;
			zeitChartData[fg].names[label] = [];
		});
	});

	// Process data and assign to Fluggebiet based on first choice (value 0)
	data.forEach(row => {
		if (row.zeit) {
			const hour = parseInt(row.zeit.substring(0, 2));
			const timeFormatted = hour.toString().padStart(2, '0') + ':00';
			const pilotName = row.Pilot ? row.Pilot.split(' ')[0] : '';

			// Check which Fluggebiet is the first choice (value 0)
			let firstChoice = null;
			if (row.NGL == 0) firstChoice = 'NGL';
			else if (row.HRP == 0) firstChoice = 'HRP';
			else if (row.AMD == 0) firstChoice = 'AMD';

			if (firstChoice && zeitChartData[firstChoice].counts.hasOwnProperty(timeFormatted)) {
				zeitChartData[firstChoice].counts[timeFormatted]++;
				if (pilotName) {
					zeitChartData[firstChoice].names[timeFormatted].push(pilotName);
				}
			}
		}
	});

	// Render chart with current filter state
	renderZeitBarChart();
}

function updateZeitBarChartFilter() {
	renderZeitBarChart();
}

function renderZeitBarChart() {
	const canvas = document.getElementById('zeitBarChart');
	if (!canvas || !zeitChartData) return;
	
	const ctx = canvas.getContext('2d');

	// Check which filters are active
	const showNGL = document.getElementById('chartFilterNGL')?.checked ?? true;
	const showHRP = document.getElementById('chartFilterHRP')?.checked ?? true;
	const showAMD = document.getElementById('chartFilterAMD')?.checked ?? true;

	// Define colors for each Fluggebiet
	const colors = {
		NGL: { bg: 'rgba(54, 162, 235, 0.6)', border: 'rgba(54, 162, 235, 1)' },
		HRP: { bg: 'rgba(255, 159, 64, 0.6)', border: 'rgba(255, 159, 64, 1)' },
		AMD: { bg: 'rgba(75, 192, 192, 0.6)', border: 'rgba(75, 192, 192, 1)' }
	};

	const labels = {
		NGL: 'Neustadt-Glewe',
		HRP: 'Hörpel',
		AMD: 'Altenmedingen'
	};

	// Build datasets based on filter
	const datasets = [];
	const fluggebieteConfig = [
		{ key: 'NGL', show: showNGL },
		{ key: 'HRP', show: showHRP },
		{ key: 'AMD', show: showAMD }
	];

	fluggebieteConfig.forEach(config => {
		if (config.show) {
			const fg = config.key;
			const chartData = zeitChartLabels.map(label => zeitChartData[fg].counts[label]);
			const namesPerBar = zeitChartLabels.map(label => zeitChartData[fg].names[label]);

			datasets.push({
				label: labels[fg],
				data: chartData,
				backgroundColor: colors[fg].bg,
				borderColor: colors[fg].border,
				borderWidth: 1,
				namesPerBar: namesPerBar
			});
		}
	});

	// Destroy existing chart if it exists
	if (zeitBarChartInstance) {
		zeitBarChartInstance.destroy();
	}

	// Render the chart
	zeitBarChartInstance = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: zeitChartLabels,
			datasets: datasets
		},
		options: {
			responsive: true,
			layout: {
				padding: {
					top: 30
				}
			},
			scales: {
				x: {
					title: {
						display: true,
						text: 'Uhrzeit'
					}
				},
				y: {
					beginAtZero: true,
					ticks: {
						stepSize: 1
					},
					title: {
						display: true,
						text: 'Anzahl Piloten'
					}
				}
			},
			plugins: {
				legend: {
					position: 'bottom'
				},
				datalabels: {
					anchor: 'end',
					align: 'top',
					color: '#333',
					font: {
						size: 9
					},
					formatter: function(value, context) {
						const dataset = context.dataset;
						if (dataset.namesPerBar) {
							const names = dataset.namesPerBar[context.dataIndex];
							return names && names.length > 0 ? names.join(', ') : '';
						}
						return '';
					}
				}
			}
		},
		plugins: [ChartDataLabels]
	});
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

			flugtagData = data;

			// Always update the Zeit bar chart (with empty array if no data)
			updateZeitBarChart(Array.isArray(data) ? data : []);

			if (typeof(data) === 'object') {
				console.log('Planung für den ' + flugtag_formatted + ' erfolgreich geladen:');
				console.log(data);

				// Problem: Failed wenn keine Daten vorhanden.Dann wird auch kein Banner angezeigt
				windenfahrer_official = data.some(value => value.windenfahrer_official == 1) ? (data.filter(o => o.windenfahrer_official == 1))[0].Pilot_ID : null;
				startleiter_official = data.some(value => value.startleiter_official == 1) ? (data.filter(o => o.startleiter_official == 1))[0].Pilot_ID : null;

				var pilot_count_all_prio_1 = [
					getRowCount(data, 'NGL', 0),
					getRowCount(data, 'HRP', 0),
					getRowCount(data, 'AMD', 0)
				];
				var pilot_count_all_prio_2 = [
					getRowCount(data, 'NGL', 1),
					getRowCount(data, 'HRP', 1),
					getRowCount(data, 'AMD', 1)
				];

				var pilot_count_hdgf_prio_1 = [
					getRowCount(data, 'NGL', 0, localClubId),
					getRowCount(data, 'HRP', 0, localClubId),
					getRowCount(data, 'AMD', 0, localClubId)
				];
				var pilot_count_hdgf_prio_2 = [
					getRowCount(data, 'NGL', 1, localClubId),
					getRowCount(data, 'HRP', 1, localClubId),
					getRowCount(data, 'AMD', 1, localClubId)
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
					let local_club_member = row.VereinId === localClubId;
					let row_not_local_club_member = local_club_member ? '' : 'class="tr_no_hgdf"';
					let ist_startleiter = row.Pilot_ID == startleiter_official ? '<span class="badge bg-success">SL Offiziell</span>' : '';
					let windenfahrer_official_info = row.Pilot_ID == windenfahrer_official ? '<span class="badge bg-success">WF Offiziell</span>' : '';
					let windenfahrer_info = (row.ist_windenfahrer == 1 && row.Pilot_ID != windenfahrer_official) ? '<span class="badge bg-primary">WF</span>' : '';
					var newRow = $('<tr ' + row_not_local_club_member + '>');
					let fluggerateIMG = row.fluggeraet.includes('G') ? replaceValueWithImage('G') : ''
					fluggerateIMG += row.fluggeraet.includes('D') ? replaceValueWithImage('D') : ''
					fluggerateIMG += row.fluggeraet.includes('S') ? replaceValueWithImage('S') : '';

					newRow.append('<td>' + row.Pilot + ' ' + windenfahrer_info + ' ' + windenfahrer_official_info + ' ' + ist_startleiter + ' ' + fluggerateIMG + new_record + '</td>');
					newRow.append('<td>' + (local_club_member ? '<strong>' : '') + row.Verein + (local_club_member ? '</strong>' : '') + '</td>');
					newRow.append('<td>' + replaceValueWithImage(row.NGL) + '</td>');
					newRow.append('<td>' + replaceValueWithImage(row.HRP) + '</td>');
					newRow.append('<td>' + replaceValueWithImage(row.AMD) + '</td>');
					newRow.append('<td>' + row.Kommentar + '</td>');

					$('#tagesplanung tbody').append(newRow);

				});


				var newRow = $('<tr>');
				newRow.append('<td><strong>Abstimmung</td>');
				newRow.append('<td></td>');
				newRow.append('<td>' + pilot_count_hdgf_prio_1[0] + '</td>');
				newRow.append('<td>' + pilot_count_hdgf_prio_1[1] + '</td>');
				newRow.append('<td>' + pilot_count_hdgf_prio_1[2] + '</td>');

				newRow.append('<td></td>');
				$('#tagesplanung tbody').append(newRow);


				var newRow = $('<tr>');
				newRow.append('<td><strong>Piloten maximal</strong></td>');
				newRow.append('<td></td>');
				newRow.append('<td>' + total_pilot_count_all[0] + '</td>');
				newRow.append('<td>' + total_pilot_count_all[1] + '</td>');
				newRow.append('<td>' + total_pilot_count_all[2] + '</td>');

				newRow.append('<td></td>');
				$('#tagesplanung tbody').append(newRow);




				var legendRow = $('<tr>');
				legendRow.append('<td colspan="6" style="font-size: small">Abstimmung = <img src="img/stern_fav.svg" class="table-image"></strong> Relevant für die Entscheidung des Haupt-Fluggebiets<br>Piloten maximal = <img src="img/stern_fav.svg" class="table-image"> + <img src="img/stern.svg" class="table-image"> wenn Betrieb nur in diesem Fluggebiet stattfindet<br><span class="badge bg-success">SL Offiziell</span> = Offizieller Startleiter für den Tag<br><span class="badge bg-success">WF Offiziell</span> = Offizieller Windenfahrer für den Tag<br><span class="badge bg-primary">WF</span> = Hat einen Windenfahrerschein</td>');
				$('#tagesplanung tbody').append(legendRow);

				User_Information.record_present = data.some(pilot => pilot.Pilot_ID === User_Information.pilot_id && (pilot.startleiter_official == "0" || pilot.startleiter_official == "") && (pilot.windenfahrer_official == "0" || pilot.windenfahrer_official == ""));

				if (User_Information.record_present) {
					$('#btn_enter').addClass('d-none');
					$('#btn_update').removeClass('d-none');
					$('#btn_delete').removeClass('d-none');

					let Active_Pilot_Flugtag_Data = (data.filter(o => o.Pilot_ID == User_Information.pilot_id))[0];

					$("#kommentar").val(Active_Pilot_Flugtag_Data.Kommentar)
					$("#flugtag-zeit").val(Active_Pilot_Flugtag_Data.zeit ? Active_Pilot_Flugtag_Data.zeit.substring(0,5) : "");
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
					$("#flugtag-zeit").val("");
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
			if (isFlugtag) {

				$('#no_official_flugtag').addClass('d-none');
				$('#minpilotreached').removeClass('d-none');
				

				if( User_Information.vereinId == localClubId && (User_Information.pilot_id != startleiter_official || User_Information.pilot_id != windenfahrer_official )) {
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

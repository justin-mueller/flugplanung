function getWeekendDays() {
	const weekendDays = [];

	for (let currentDay = new Date(calcSeasonStart({ earliestCalenderDate: false })); currentDay <= saisonEndDate; currentDay.setDate(currentDay.getDate() + 1)) {

		const dayOfWeek = currentDay.getDay();

		if (dayOfWeek === 6 || dayOfWeek === 0) {
			weekendDays.push(new Date(currentDay));
		}
	}

	var dates = weekendDays.map(formatDateString);

	insertFlugtag(dates)

}


function loadFlugtage(init) {

	let startDate = formatDateString(saisonStartDate);
	let endDate = formatDateString(saisonEndDate);

	$.ajax({
		url: 'fetch_flugtage.php',
		type: 'GET',
		data: { startDate: startDate, endDate: endDate },
		dataType: 'json',
		success: function (data) {

			Flugtage = data;
			populateFlugtageTable(Flugtage);

			if (init) {
				let startFlugtag = new Date();

				$.each(Flugtage, function (index, entry) {
					let Flugtag_Converted = parseDateStringWithGermanMonth(entry.datum);
					if (Flugtag_Converted >= startFlugtag) {
						startFlugtag = Flugtag_Converted;
						return false;
					}
				});

				flugtag_formatted = dateToSQLFormat(startFlugtag);
				flugtag_unformatted = startFlugtag;
				calc_deadline(flugtag_unformatted);

				$('#flugtag').val(getFormattedGermanDate(startFlugtag));

				getFlugtag();
				BetriebAbfragen();
			}
		},
		error: function (error) {
			console.error('Flugtage konnten nicht geladen werdn!', error);
		}
	});
}

function populateFlugtageTable(data) {

	const tableBody = $("#flugtageTableBody");
	tableBody.empty();
	data.reverse()
	$.each(data, function (index, entry) {
		data[index].datum = getFormattedGermanDate(data[index].datum)
	});

	if (data.length === 0) {
		tableBody.append('<tr><td colspan="2">Keine Einträge gefunden!</td></tr>');
	} else {
		$.each(data, function (index, entry) {
			const row = `<tr>
									  <td>${entry.datum}</td>
									  <td><button class="btn btn-danger ui-button" onclick="deleteFlugtage('${entry.datum}')">Löschen</button></td>
								 </tr>`;
			tableBody.append(row);
		});
	}
}

function deleteFlugtage(datum) {

	datum = parseDateStringWithGermanMonth(datum);
	datum = dateToSQLFormat(datum);

	$.ajax({
		url: 'delete_flugtage.php',
		type: 'POST',
		data: { datum: datum },
		success: function (response) {
			showToast('Juhu!', 'Das hat geklappt', 'Der Flugtag wurde gelöscht!', 'success');
			loadFlugtage();
		},
		error: function (error) {
			showToast('Oops!', 'Etwas ist schiefgegangen!', 'Der Flugtag konnte nicht gelöscht werden!', 'error');
			console.error('Error deleting entry:', error.responseText);
		}
	});
}

function enterSingleFlugtag() {
	let date = $('#additional_flugtag').val()
	date = calc_flugtag_date(date)
	insertFlugtag([date])
}

function insertFlugtag(dates) {

	var counter = 0;

	function handleSuccess() {
		counter++;
		if (counter === dates.length) {
			let e = (dates.length == 1) ? '' : 'e';
			let n = (dates.length == 1) ? '' : 'n';
			showToast('Juhu!', 'Das hat geklappt', dates.length + ' Flugtag' + e + ' wurde' + n + ' eingetragen!', 'success');
			loadFlugtage();
		}
	}

	dates.forEach(function (date) {
		$.ajax({
			url: 'add_flugtag.php',
			type: 'POST',
			data: { datum: date },
			success: handleSuccess,
			error: function (error) {
				showToast('Oops!', 'Etwas ist schiefgegangen!', 'Der Flugtag konnte nicht gelöscht werden!', 'error');
				console.error('Error inserting entry:', error.responseText);
			}
		});
	});
}


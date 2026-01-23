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


function loadFlugtage(init, fullYear = false) {

	let startDate = fullYear ? saisonJahr + '-01-01' : formatDateString(saisonStartDate);
	let endDate = fullYear ? saisonJahr + '-12-31' : formatDateString(saisonEndDate);

	$.ajax({	
		url: 'fetch_flugtage.php',
		type: 'GET',
		data: { startDate: startDate, endDate: endDate },
		dataType: 'json',
		success: function (data) {


			Flugtage = JSON.parse(JSON.stringify(data));
			FlugtageRaw = JSON.parse(JSON.stringify(data));


			//Fill Datepicker
			let highlightedDates = new Set(FlugtageRaw.map(item => new Date(item.datum).toDateString()));

			$('.date').datepicker({
				language: 'de',
				weekStart: 1,
				todayHighlight: true,
				format: {
					toDisplay: function (date, format, language) {
						return getFormattedGermanDate(date);
					},
					toValue: function (date, format, language) {
						return date;
					}
				},
				beforeShowDay: function (date) {
					if (highlightedDates.has(date.toDateString())) {

						return { classes: 'highlight-day', tooltip: 'Highlighted date' };
					
					} else {
						return { classes: 'non-highlight-day', tooltip: 'Regular date' };
					}
				}
			})


			$(".date").datepicker({
				language: "de",
				weekStart: 1,
				daysOfWeekHighlighted: [0, 6],
				todayHighlight: true,
				format: {
				  toDisplay: function (date, format, language) {
					return getFormattedGermanDate(date);
				  },
				  toValue: function (date, format, language) {
					return date;
				  },
				},
			  });
			  
			renderFlugtageList(Flugtage);

			if (init) {

				let startFlugtag = new Date();
				const checkTime = new Date();
				checkTime.setHours(14, 0, 0); 
				
				$.each(Flugtage, function (index, entry) {
					let Flugtag_Converted = new Date(entry.datum);

					let flugtagDate = Flugtag_Converted.getFullYear().toString() + Flugtag_Converted.getMonth().toString() + Flugtag_Converted.getDate().toString();
					let startFlugtagDate = startFlugtag.getFullYear().toString() + startFlugtag.getMonth().toString() + startFlugtag.getDate().toString();

					if (Flugtag_Converted > startFlugtag || (flugtagDate == startFlugtagDate && checkTime > startFlugtag )) {
						startFlugtag = Flugtag_Converted;
						return false;
					}
				});

				flugtag_formatted = dateToSQLFormat(startFlugtag);
				flugtag_unformatted = startFlugtag;
				calc_deadline(flugtag_unformatted);

				$('#flugtag').val(getFormattedGermanDate(startFlugtag));

				getFlugtag();
				betriebAbfragen();
			}
		},
		error: function (error) {
			console.error('Flugtage konnten nicht geladen werdn!', error);
		}
	});
}

function renderFlugtageList(data) {

	const container = $("#flugtage-container");
	container.empty();
	let displayData = [...data];
	displayData.reverse();

	if (displayData.length === 0) {
		container.append('<div class="alert alert-info">Keine Einträge gefunden!</div>');
	} else {
		let currentWeek = null;

		$.each(displayData, function (index, entry) {
			
			let dateObj = new Date(entry.datum);
			let week = getCalendarWeek(dateObj);
			let year = dateObj.getFullYear();
			// Handle year crossing roughly for grouping key
			let weekKey = year + '-' + week;

			if (currentWeek !== weekKey) {
				currentWeek = weekKey;
				container.append(`<div class="week-separator">KW ${week}</div>`);
			}

			let day = dateObj.getDate();
			let monthShort = dateObj.toLocaleDateString('de-DE', { month: 'short' }).toUpperCase().replace('.', '');
			// Ensure we strip possible dot "Okt."
			
			let fullDate = getFormattedGermanDate(entry.datum);

			const card = `
			<div class="card-row">
				<div class="card-row-header">
					<div class="card-date-badge">
						<div class="card-date-day">${day}</div>
						<div class="card-date-month">${monthShort}</div>
					</div>
					<div class="card-date-full">
						${fullDate}
					</div>
					<div class="ms-auto">
						<button class="btn btn-outline-danger btn-sm" onclick="deleteFlugtage('${entry.datum}')">
							<i class="fa-solid fa-trash"></i>
						</button>
					</div>
				</div>
			</div>`;
			container.append(card);
		});
	}
}

function deleteFlugtage(datum) {

	if (!/^\d{4}-\d{2}-\d{2}$/.test(datum)) {
		datum = parseDateStringWithGermanMonth(datum);
		datum = dateToSQLFormat(datum);
	}

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


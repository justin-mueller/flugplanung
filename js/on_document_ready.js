$(document).ready(function () {

	$('.date').datepicker({
		language: 'de',
		weekStart: 1,
		daysOfWeekHighlighted: [0,6],
		todayHighlight: true,
		format: {
			toDisplay: function (date, format, language) {
				return getFormattedGermanDate(date);
			},
			toValue: function (date, format, language) {
				return date;
			}
		}
	})

	loadFlugtage(true);

	$("#flugtag").on("change", function () {
		let value_from_datepicker = $('#flugtag').val();
		flugtag_formatted = dateToSQLFormat(parseDateStringWithGermanMonth(value_from_datepicker));
		flugtag_unformatted = parseDateStringWithGermanMonth(value_from_datepicker);
		calc_deadline(flugtag_unformatted);
		getFlugtag();
		betriebAbfragen();
	});

	if (typeof getUserWuensche === 'function') {
		getUserWuensche();
	}
	getDashboardData();

	$("[id^=list_fist_choice]").on('click', function (e) {
		if (!$(this).hasClass("active")) $("[id^=list_alternative]").removeClass("active");
		$("[id^=list_fist_choice]").removeClass("active");
		$(this).addClass('active');
		let firstChoice = ($(this)[0].id.substring($(this)[0].id.length - 1) * 1) - 1;
		renameAlternativeButtons(firstChoice);
	})

	$("[id^=list_alternative]").click(function () {
		$(this).toggleClass("active");
	});

	$('#FluggebietForm').on('keydown', 'input[type="text"]', function (e) {
		if (e.key === 'Enter') {
			e.preventDefault();
		}
	});

	$('#table-body-dashboard').on('click', '.pilot-div', function () {

		const clickedDiv = $(this);
		const sourceColumn = clickedDiv.parent().attr('id');
		const dienst = sourceColumn.includes('windenfahrer') ? 'windenfahrer' : 'startleiter';
		const pilot_id = clickedDiv.attr('data-pilot-id');

		const date = sourceColumn.slice(-10);
		var name = clickedDiv[0].innerHTML

		name = (name.endsWith('+') || name.endsWith('-')) ? name.slice(0, -1) : name;

		let destinationColumn;

		if (sourceColumn.includes('Optionen')) {
			destinationColumn = sourceColumn.replace('Optionen_', '');

		} else {
			destinationColumn = 'Optionen_' + sourceColumn;
		}
		const destinationCell = $(`#${destinationColumn}`);

		if ((!destinationCell.text().trim() && !destinationCell.attr('id').includes('Optionen')) || destinationCell.attr('id').includes('Optionen')) {
			destinationCell.append(clickedDiv);
			clickedDiv.data('column', destinationColumn);
			if (sourceColumn.includes('Optionen')) {
				enteredDienste.push({ pilot_id: pilot_id, name: name, date: date, dienst: dienst });
			} else {
				enteredDienste = enteredDienste.filter(item => !(item.name === name && item.date === date));
			}
		}
		populatePilotTable();

	});

	$(".year-dropdown").each(function () {
		for (let year = thisYear - 2; year <= thisYear + 1; year++) {
			$(this).append(`<option value="${year}">${year}</option>`);
			$(this).val(thisYear);
		}
	});

	$('.year-dropdown').on('change', function () {
		saisonJahr = $(this).val();
		saisonStartDate = calcSeasonStart({ earliestCalenderDate: true });
		saisonEndDate = calcSeasonEnd({ latestCalenderDate: true });
		$('.year-dropdown').val(saisonJahr);
		if (typeof getUserWuensche === 'function') {
			getUserWuensche();
		}
		getDashboardData();
		loadFlugtage();
	});

	loadChatbox(true);

    var avatarDiv = document.getElementById("avatar-header");
    var imageUrl = "img/a" + User_Information.avatar + ".png";
    avatarDiv.innerHTML = "<img src='" + imageUrl + "' alt='Avatar Preview'>";

	$("#avatar-header").on('click', function () {
		showToast('Info!', 'Nutzer Einstellungen', 'Hier kannst du demnächst Einstellungen zu Deinem Nutzerprofil machen. Noch ist dies nicht implementiert!', 'success');
	})

	setInterval(() => loadChatbox(false), 15000);
});

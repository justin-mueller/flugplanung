$(document).ready(function () {
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

  loadFlugtage(true);

  $("#flugtag").on("change", function () {
    let value_from_datepicker = $("#flugtag").val();
    flugtag_formatted = dateToSQLFormat(
      parseDateStringWithGermanMonth(value_from_datepicker)
    );
    flugtag_unformatted = parseDateStringWithGermanMonth(value_from_datepicker);
    calc_deadline(flugtag_unformatted);
    getFlugtag();
    betriebAbfragen();
  });

  if (typeof getUserWuensche === "function") {
    getUserWuensche();
  }
  getDashboardData();

  $("[id^=list_fist_choice]").on("click", function (e) {
    if (!$(this).hasClass("active"))
      $("[id^=list_alternative]").removeClass("active");
    $("[id^=list_fist_choice]").removeClass("active");
    $(this).addClass("active");
    let firstChoice = $(this)[0].id.substring($(this)[0].id.length - 1) * 1 - 1;
    renameAlternativeButtons(firstChoice);
  });

  $("[id^=list_alternative]").click(function () {
    $(this).toggleClass("active");
  });

  $("#FluggebietForm").on("keydown", 'input[type="text"]', function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
    }
  });
  $("#table-body-dashboard").on("click", ".pilot-div", function () {
    const clickedDiv = $(this);
    const sourceColumn = clickedDiv.parent().attr("id");
    const dienst = sourceColumn.includes("windenfahrer")
      ? "windenfahrer"
      : "startleiter";
    const pilot_id = parseInt(clickedDiv.attr("data-pilot-id")); // Ensure pilot_id is an integer

    const date = sourceColumn.slice(-10); // Extract the date
    let name = clickedDiv[0].innerHTML;

    // Remove any trailing "+" or "-" from the name
    name = name.endsWith("+") || name.endsWith("-") ? name.slice(0, -1) : name;

    let destinationColumn;

    if (sourceColumn.includes("Optionen")) {
      destinationColumn = sourceColumn.replace("Optionen_", "");

      // Find the pilot in dashboardDataHistory and subtract 1 from duties_count_thisyear
      const pilotData = dashboardDataHistory.find(
        (item) => item.pilot_id === pilot_id
      );
      if (pilotData) {
        pilotData.duties_count_thisyear += 1;
      }
    } else {
      destinationColumn = "Optionen_" + sourceColumn;

      // Find the pilot in dashboardDataHistory and add 1 to duties_count_thisyear
      const pilotData = dashboardDataHistory.find(
        (item) => item.pilot_id === pilot_id
      );
      if (pilotData) {
        pilotData.duties_count_thisyear = Math.max(
          0,
          pilotData.duties_count_thisyear - 1
        );
      }
    }

    const destinationCell = $(`#${destinationColumn}`);

    if (
      (!destinationCell.text().trim() &&
        !destinationCell.attr("id").includes("Optionen")) ||
      destinationCell.attr("id").includes("Optionen")
    ) {
      destinationCell.append(clickedDiv);
      clickedDiv.data("column", destinationColumn);

      if (sourceColumn.includes("Optionen")) {
        // Add the entry to enteredDienste when moving into "active" columns
        enteredDienste.push({
          pilot_id: pilot_id,
          name: name,
          date: date,
          dienst: dienst,
        });
      } else {
        // Remove the entry from enteredDienste when moving into "Optionen" columns
        enteredDienste = enteredDienste.filter(
          (item) => !(item.name === name && item.date === date)
        );
      }
    }

    populateDashboardHistory();
  });

  $(".year-dropdown").each(function () {
    for (let year = thisYear - 2; year <= thisYear + 1; year++) {
      $(this).append(`<option value="${year}">${year}</option>`);
      $(this).val(thisYear);
    }
  });

  $(".year-dropdown").on("change", function () {
    saisonJahr = $(this).val();
    saisonStartDate = calcSeasonStart({ earliestCalenderDate: true });
    saisonEndDate = calcSeasonEnd({ latestCalenderDate: true });
    $(".year-dropdown").val(saisonJahr);
    if (typeof getUserWuensche === "function") {
      getUserWuensche();
    }
    getDashboardData();
    loadFlugtage();
  });

  loadChatbox(true);

  var avatarDiv = document.getElementById("avatar-header");
  var imageUrl = "img/a" + User_Information.avatar + ".png";
  avatarDiv.innerHTML = "<img src='" + imageUrl + "' alt='Avatar Preview'>";


  setInterval(() => loadChatbox(false), 15000);
});

document.addEventListener("DOMContentLoaded", function () {
  //Countdown
  const observer = new MutationObserver((mutationsList, observer) => {
    const countdownElement = document.getElementById("countdown");

    if (countdownElement) {
      intervalId = setInterval(updateCountdown, 1000);
      observer.disconnect();
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });



  //Fill User Update Form
	const observer2 = new MutationObserver((mutationsList, observer) => {
		const countdownElement2 = document.getElementById("update-user-info-form");

		if (countdownElement2) {
			$("#nachname").val(User_Information.lastname);
			$("#vorname").val(User_Information.firstname);

			$("#avatar-update").val(User_Information.avatar).change();

      $("#verein").val(User_Information.vereinId).change();

			$("#fluggeraet_gleitschirm").prop(
			  "checked",
			  User_Information.fluggeraet.includes("G")
			);

			$("#fluggeraet_drachen").prop(
			  "checked",
			  User_Information.fluggeraet.includes("D")
			);
			$("#fluggeraet_sonstiges").prop(
			  "checked",
			  User_Information.fluggeraet.includes("S")
			);

			updatePreview(User_Information.avatar-1);


			$("#windenfahrer").prop(
				"checked",
				User_Information.windenfahrer == 1
			  );

		  observer2.disconnect();
		}
	  });

	  observer2.observe(document.body, { childList: true, subtree: true });


});

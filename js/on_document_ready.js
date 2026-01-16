$(document).ready(function () {


	loadFlugtage(true, true);


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

  if (typeof initHistoryRange === "function") {
    initHistoryRange();
  }

  if (typeof getUserWuensche === "function") {
    getUserWuensche();
  }
  getDashboardData();
  getDienste();

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

    // Extract the flugtag date from the column ID (timestamp format)
    const columnParts = sourceColumn.split("_");
    const flugtag = columnParts[columnParts.length - 1]; // Get the timestamp at the end
    
    let name = clickedDiv[0].innerHTML;
    // Remove any trailing "+" or "-" from the name
    name = name.endsWith("+") || name.endsWith("-") ? name.slice(0, -1) : name;

    let destinationColumn;
    const isAssigning = sourceColumn.includes("Optionen"); // Moving from Optionen to active = assigning

    if (isAssigning) {
      destinationColumn = sourceColumn.replace("Optionen_", "");
    } else {
      destinationColumn = "Optionen_" + sourceColumn;
    }

    const destinationCell = $(`#${destinationColumn}`);

    // Check if move is valid: either destination is empty (for active columns) or it's an Optionen column
    const canMove =
      (!destinationCell.text().trim() &&
        !destinationCell.attr("id").includes("Optionen")) ||
      destinationCell.attr("id").includes("Optionen");

    if (canMove) {
      // Prepare the AJAX call based on whether we're assigning or unassigning
      let ajaxConfig;
      
      if (isAssigning) {
        // Assigning: INSERT into backend
        ajaxConfig = {
          url: 'saveDienste.php',
          method: 'POST',
          data: {
            flugtag: flugtag,
            pilot_id: pilot_id,
            windenfahrer: dienst === 'windenfahrer' ? 1 : 0,
            startleiter: dienst === 'startleiter' ? 1 : 0
          }
        };
      } else {
        // Unassigning: DELETE from backend
        ajaxConfig = {
          url: 'deleteSingleDienst.php',
          method: 'POST',
          data: {
            flugtag: flugtag,
            pilot_id: pilot_id
          }
        };
      }

      // Make the backend call
      $.ajax(ajaxConfig)
        .done(function(response) {
          // Backend succeeded - now update the frontend
          destinationCell.append(clickedDiv);
          clickedDiv.data("column", destinationColumn);

          // Update the pilot's duty count in dashboardDataHistory
          const pilotData = dashboardDataHistory.find(
            (item) => item.pilot_id === pilot_id
          );
          if (pilotData) {
            if (isAssigning) {
              pilotData.duties_count_thisyear += 1;
            } else {
              pilotData.duties_count_thisyear = Math.max(
                0,
                pilotData.duties_count_thisyear - 1
              );
            }
          }

          if (isAssigning) {
            // Add the entry to enteredDienste
            enteredDienste.push({
              pilot_id: pilot_id,
              name: name,
              date: flugtag,
              dienst: dienst,
            });
          } else {
            // Remove the entry from enteredDienste
            enteredDienste = enteredDienste.filter(
              (item) => !(item.pilot_id === pilot_id && item.date === flugtag)
            );
          }

          // Live update the history table
          populateDashboardHistory();
        })
        .fail(function(xhr, status, error) {
          console.error('Error saving/deleting dienst:', error);
          showToast('Oops!', 'Fehler', 'Die Änderung konnte nicht gespeichert werden.', 'error');
        });
    }
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

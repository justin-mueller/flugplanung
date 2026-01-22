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
  if (typeof initMaxDiensteField === "function") {
    initMaxDiensteField();
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
    
    // Ignore clicks on disabled cards
    if (clickedDiv.hasClass('pilot-div-disabled')) {
      return;
    }
    
    const sourceColumn = clickedDiv.parent().attr("id");
    const dienst = sourceColumn.includes("windenfahrer")
      ? "windenfahrer"
      : "startleiter";
    const pilot_id = parseInt(clickedDiv.attr("data-pilot-id")); // Ensure pilot_id is an integer
    const maxDiensteAttr = clickedDiv.attr("data-max-dienste");
    const max_dienste_halbjahr = maxDiensteAttr !== '' && maxDiensteAttr !== undefined ? parseInt(maxDiensteAttr) : null;

    // Extract the flugtag date from the column ID (timestamp format)
    const columnParts = sourceColumn.split("_");
    const flugtag = columnParts[columnParts.length - 1]; // Get the timestamp at the end
    
    let name = clickedDiv[0].innerHTML;
    // Remove the {N} max dienste indicator if present
    name = name.replace(/\s*\{\d+\}$/, '');
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
      // Check if pilot would exceed their max dienste limit when assigning
      if (isAssigning && max_dienste_halbjahr !== null) {
        // Count current duties for this pilot
        const currentDutyCount = enteredDienste.filter(
          (item) => item.pilot_id === pilot_id || item.pilot_id === String(pilot_id)
        ).length;
        
        if (currentDutyCount >= max_dienste_halbjahr) {
          showToast(
            'Achtung!', 
            'Maximale Dienste erreicht', 
            `${name} hat bereits ${currentDutyCount} Dienste und möchte max. ${max_dienste_halbjahr} Dienste in diesem Halbjahr!`, 
            'warning'
          );
        }
      }
      
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

          if (isAssigning) {
            // Add visual effects for assignment
            clickedDiv.addClass('pilot-div-entered assigning');
            spawnConfetti(clickedDiv[0]);
            
            // Remove animation class after it completes
            setTimeout(() => {
              clickedDiv.removeClass('assigning');
            }, 500);
            
            // Add the entry to enteredDienste only if it doesn't already exist
            const alreadyExists = enteredDienste.some(
              (item) => item.pilot_id === pilot_id && item.date === flugtag && item.dienst === dienst
            );
            if (!alreadyExists) {
              enteredDienste.push({
                pilot_id: pilot_id,
                name: name,
                date: flugtag,
                dienst: dienst,
                max_dienste_halbjahr: max_dienste_halbjahr
              });
            }
          } else {
            // Add visual effects for unassignment
            clickedDiv.addClass('unassigning');
            clickedDiv.removeClass('pilot-div-entered');
            
            // Remove animation class after it completes
            setTimeout(() => {
              clickedDiv.removeClass('unassigning');
            }, 300);
            
            // Remove the entry from enteredDienste (matching pilot_id, date, AND dienst)
            enteredDienste = enteredDienste.filter(
              (item) => !(item.pilot_id === pilot_id && item.date === flugtag && item.dienst === dienst)
            );
          }

          // Update disabled states for option cards
          updateDisabledStates();
          
          // Refresh history table from backend to ensure accurate counts
          refreshDashboardHistory();
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
    if (typeof initMaxDiensteField === "function") {
      initMaxDiensteField();
    }
    getDashboardData();
    loadFlugtage();
    getDienste();
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

			$("#newsletter").prop(
			  "checked",
			  User_Information.newsletter == 1
			);

			$("#duty_reminder").prop(
			  "checked",
			  User_Information.duty_reminder == 1
			);

			$("#duty_reminder_days").val(
			  User_Information.duty_reminder_days || 7
			);

			$("#wuensche_reminder").prop(
			  "checked",
			  User_Information.wuensche_reminder == 1
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

// Fun confetti effect when assigning a pilot to duty
function spawnConfetti(element) {
  const colors = ['#ff6b6b', '#4ecdc4', '#ffe66d', '#95e1d3', '#f38181', '#aa96da', '#fcbad3', '#a8d8ea'];
  const rect = element.getBoundingClientRect();
  const centerX = rect.left + rect.width / 2;
  const centerY = rect.top + rect.height / 2;
  
  // Create confetti container
  const container = document.createElement('div');
  container.className = 'confetti-container';
  container.style.position = 'fixed';
  container.style.left = centerX + 'px';
  container.style.top = centerY + 'px';
  document.body.appendChild(container);
  
  // Spawn confetti particles
  const particleCount = 12;
  for (let i = 0; i < particleCount; i++) {
    const particle = document.createElement('div');
    particle.className = 'confetti';
    
    // Random color
    particle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
    
    // Random direction (full 360 degrees)
    const angle = (Math.PI * 2 * i) / particleCount + (Math.random() - 0.5) * 0.5;
    const distance = 30 + Math.random() * 40;
    const tx = Math.cos(angle) * distance;
    const ty = Math.sin(angle) * distance;
    const rotation = Math.random() * 720 - 360;
    
    particle.style.setProperty('--tx', tx + 'px');
    particle.style.setProperty('--ty', ty + 'px');
    particle.style.setProperty('--rot', rotation + 'deg');
    
    // Random size
    const size = 5 + Math.random() * 6;
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';
    
    // Random shape (some squares, some circles)
    if (Math.random() > 0.5) {
      particle.style.borderRadius = '50%';
    }
    
    // Stagger animation slightly
    particle.style.animationDelay = (Math.random() * 0.1) + 's';
    
    container.appendChild(particle);
  }
  
  // Add sparkles
  for (let i = 0; i < 6; i++) {
    const sparkle = document.createElement('div');
    sparkle.className = 'sparkle';
    
    const angle = (Math.PI * 2 * i) / 6;
    const distance = 20 + Math.random() * 25;
    const tx = Math.cos(angle) * distance;
    const ty = Math.sin(angle) * distance;
    
    sparkle.style.setProperty('--tx', tx + 'px');
    sparkle.style.setProperty('--ty', ty + 'px');
    sparkle.style.animationDelay = (Math.random() * 0.15) + 's';
    
    container.appendChild(sparkle);
  }
  
  // Clean up after animation
  setTimeout(() => {
    container.remove();
  }, 1000);
}

// Update disabled state for pilot option cards based on whether role is already filled
function updateDisabledStates() {
  // Find all date tables in the dashboard
  $('#table-body-dashboard .date-table').each(function() {
    const dateTable = $(this);
    
    // Find cells by looking at IDs
    dateTable.find('td[id^="startleiter_"], td[id^="windenfahrer_"]').each(function() {
      const cell = $(this);
      const cellId = cell.attr('id');
      
      // Skip if this is an Optionen cell
      if (cellId.startsWith('Optionen_')) return;
      
      // Determine the role and get the corresponding Optionen cell
      const optionenCellId = 'Optionen_' + cellId;
      const optionenCell = $(`#${optionenCellId}`);
      
      if (optionenCell.length) {
        // Check if the main cell has any assigned pilots
        const hasAssignedPilot = cell.find('.pilot-div').length > 0;
        
        // Enable or disable all cards in the Optionen cell
        optionenCell.find('.pilot-div').each(function() {
          if (hasAssignedPilot) {
            $(this).addClass('pilot-div-disabled');
          } else {
            $(this).removeClass('pilot-div-disabled');
          }
        });
      }
    });
  });
}

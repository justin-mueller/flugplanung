function initMaxDiensteField() {
    // Only show for windenfahrer
    if (User_Information.windenfahrer == 1) {
        $('#maxDiensteContainer').removeClass('d-none');
        
        // Set current value if available
        if (User_Information.max_dienste_halbjahr !== null && User_Information.max_dienste_halbjahr !== undefined) {
            $('#maxDiensteHalbjahr').val(User_Information.max_dienste_halbjahr);
        }
    }
}

function saveMaxDienste() {
    if (User_Information.windenfahrer != 1) {
        return Promise.resolve();
    }

    const maxDienste = $('#maxDiensteHalbjahr').val();
    
    return $.ajax({
        url: 'saveMaxDienste.php',
        type: 'POST',
        data: { 
            pilot_id: User_Information.pilot_id, 
            max_dienste_halbjahr: maxDienste === '' ? null : parseInt(maxDienste)
        }
    }).then(function(response) {
        // Update the User_Information object with the new value
        User_Information.max_dienste_halbjahr = maxDienste === '' ? null : parseInt(maxDienste);
    });
}

function createWunschCard(date, defaultValue) {
    // Parse date for display
    const dateObj = new Date(date);
    const day = dateObj.getDate();
    const monthNames = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
    const weekdayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
    const month = monthNames[dateObj.getMonth()];
    const weekday = weekdayNames[dateObj.getDay()];
    const fullDate = getFormattedGermanDate(date);
    
    const card = document.createElement('div');
    card.className = 'card-row';
    card.setAttribute('data-date', date);
    
    const options = [
        { value: 'Ja', label: 'Ja', class: 'wunsch-ja' },
        { value: 'Nein', label: 'Nein', class: 'wunsch-nein' },
        { value: 'Egal', label: 'Egal', class: 'wunsch-egal' }
    ];
    
    let optionsHtml = options.map(opt => `
        <label class="wunsch-option ${opt.class}">
            <input type="radio" name="radio_${date}" value="${opt.value}" ${opt.value === defaultValue ? 'checked' : ''}>
            <span class="wunsch-option-label">${opt.label}</span>
        </label>
    `).join('');
    
    card.innerHTML = `
        <div class="card-row-header">
            <div class="card-date-badge">
                <div class="card-date-day">${day}</div>
                <div class="card-date-month">${month}</div>
            </div>
            <div class="card-date-full">${weekday}, ${fullDate}</div>
            <div class="wunsch-options">
                ${optionsHtml}
            </div>
        </div>
    `;
    
    return card;
}


function populateWuenscheValues(wuenscheValues) {
    const container = document.getElementById('wunschliste-container');
    if (!container) return;

    container.innerHTML = '';

    // Show empty state if no flugtage
    if (!wuenscheValues || wuenscheValues.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="fa-solid fa-circle-info me-2"></i>
                Hier wurden noch keine Flugtage eingetragen!
            </div>
        `;
        return;
    }

    wuenscheValues.forEach((entry) => {
        const card = createWunschCard(entry.date, entry.wunsch);
        container.appendChild(card);
    });
}


function getUserWuensche() {

    let startDate = formatDateString(saisonStartDate);
    let endDate = formatDateString(saisonEndDate);

    $.ajax({
        url: 'getWuensche.php',
        type: 'GET',
        data: { pilot_id: User_Information.pilot_id, startDate: startDate, endDate: endDate },
        success: function (data) {
            if (Array.isArray(data)) {
                populateWuenscheValues(data);
            } else {
                console.log('data should be returned as array already!');
                populateWuenscheValues(JSON.parse(data));
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr);
            console.log(error);
        }
    });

}

function saveWuenscheALT() {
    const tableRows = document.querySelectorAll('#table-body tr');
    tableRows.forEach(row => {
        const dateCell = row.querySelector('td:first-child');
        const germanDateString = dateCell.textContent;
        const dateObject = parseDateStringWithGermanMonth(germanDateString);
        formattedDate = dateToSQLFormat(dateObject);
        const radioButtons = row.querySelectorAll('input[type="radio"]');

        radioButtons.forEach(radio => {
            let wunsch = radio.checked ? radio.value : null;
            if (radio.checked) {
                wunsch = radio.value;
            }
        });

        const xhrSave = new XMLHttpRequest();
        xhrSave.open("POST", "saveWuensche.php", true);
        xhrSave.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhrSave.send(`pilot_id=${User_Information.pilot_id}&datum=${formattedDate}&wunsch=${wunsch}`);
    });

    showToast('Juhu!', 'Das hat geklappt', 'Deine Wünsche wurden gespeichert!', 'success');

};

function saveWuensche() {
    const cards = document.querySelectorAll('#wunschliste-container .card-row');
    var enteredCount = 0;
    var failedCount = 0;
    var totalExpected = cards.length;
    
    // First save the max dienste if user is windenfahrer
    if (User_Information.windenfahrer == 1) {
        totalExpected++; // Add one more for max dienste save
        saveMaxDienste()
            .done(function() {
                enteredCount++;
                if (enteredCount == totalExpected) {
                    showToast('Juhu!', 'Das hat geklappt', 'Deine Wünsche wurden gespeichert!', 'success');
                }
            })
            .fail(function(xhr, status, error) {
                failedCount++;
                console.log(xhr.responseText);
            });
    }
    
    cards.forEach(card => {
        const formattedDate = card.getAttribute('data-date');
        const radioButtons = card.querySelectorAll('input[type="radio"]');
        var wunsch = 'Egal';
        radioButtons.forEach(radio => {
            if (radio.checked) {
                wunsch = radio.value;
            }
        });

        $.ajax({
            url: 'saveWuensche.php',
            type: 'POST',
            data: { pilot_id: User_Information.pilot_id, datum: formattedDate, wunsch: wunsch },

            success: function () {
                enteredCount++;
                if (enteredCount == totalExpected) {
                    showToast('Juhu!', 'Das hat geklappt', 'Deine Wünsche wurden gespeichert!', 'success');
                }
            },
            error: function (xhr, status, error) {
                failedCount++;
                console.log(xhr.responseText);
            },
        });
    });

    if (failedCount > 0) {
        showToast('Oops!', 'Etwas ist schiefgegangen!', 'Mindestens ein Wunsch konnte nicht gespeichert werden!', 'error');
    }
};




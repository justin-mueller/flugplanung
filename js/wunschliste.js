function createTableRow(date, defaultValue) {
    const row = document.createElement('tr');
    const dateCell = document.createElement('td');
    dateCell.textContent = getFormattedGermanDate(date);
    row.appendChild(dateCell);

    const options = ['Ja', 'Nein', 'Egal'];

    options.forEach(option => {
        const cell = document.createElement('td');
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = `radio_${date}`;
        radio.value = option;
        radio.checked = option === defaultValue;
        cell.appendChild(radio);
        row.appendChild(cell);
    });

    return row;
}


function populateWuenscheValues(wuenscheValues) {
    const tableBody = document.getElementById('table-body');

    while (tableBody.firstChild) {
        tableBody.removeChild(tableBody.firstChild);
    }

    wuenscheValues.forEach((entry) => {
        const row = createTableRow(entry.date, entry.wunsch);
        tableBody.appendChild(row);
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
    const tableRows = document.querySelectorAll('#table-body tr');
    var enteredCount = 0;
    var failedCount = 0;
    tableRows.forEach(row => {
        const dateCell = row.querySelector('td:first-child');
        const germanDateString = dateCell.textContent;
        const dateObject = parseDateStringWithGermanMonth(germanDateString);
        formattedDate = dateToSQLFormat(dateObject);
        const radioButtons = row.querySelectorAll('input[type="radio"]');
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
                if (enteredCount == tableRows.length) {
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




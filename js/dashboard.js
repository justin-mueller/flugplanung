function getDashboardData() {

    let startDate = formatDateString(saisonStartDate);
    let endDate = formatDateString(saisonEndDate);

    enteredDienste = [];
    $.ajax({
        url: 'getDashboardData.php',
        type: 'GET',
        dataType: 'json',
        data: { startDate: startDate, endDate: endDate },
        success: function (data) {

            // Clean data, weil SQL code nicht richtig funktioniert!

            data.forEach(entry => {
                const startleiterWithPlus = new Set(entry.startleiterOptionen.map(pilot => ({ name: pilot.name, id: extractNumericId(pilot.id) })));
                const windenfahrerWithPlus = new Set(entry.windenfahrerOptionen.map(pilot => ({ name: pilot.name, id: extractNumericId(pilot.id) })));

                const deleteId = (pilotSet, pilotWithoutPlus) => {
                    const indexToDelete = [...pilotSet].findIndex(pilot => pilot.name === pilotWithoutPlus);
                    if (indexToDelete !== -1) {
                        pilotSet.delete([...pilotSet][indexToDelete]);
                    }
                };

                entry.startleiterOptionen.forEach(pilot => {
                    if (pilot.name.endsWith('+') || pilot.name.endsWith('-')) {
                        const pilotWithoutPlus = pilot.name.slice(0, -1); // Remove '+' or '-'
                        deleteId(startleiterWithPlus, pilotWithoutPlus);
                    }
                });

                entry.windenfahrerOptionen.forEach(pilot => {
                    if (pilot.name.endsWith('+') || pilot.name.endsWith('-')) {
                        const pilotWithoutPlus = pilot.name.slice(0, -1); // Remove '+' or '-'
                        deleteId(windenfahrerWithPlus, pilotWithoutPlus);
                    }
                });

                // Convert the Sets back to arrays of objects
                entry.startleiterOptionen = Array.from(startleiterWithPlus);
                entry.windenfahrerOptionen = Array.from(windenfahrerWithPlus);
            });

            dashboardData = data;
            populateDashboardTable();
            populatePilotTable();
        },
        error: function (xhr, status, error) {
            console.error('Dashboard Daten konnten nicht geladen werden:', status, error);
        }
    });
}

function extractNumericId(name) {
    const matches = name.match(/\d+/);
    return matches ? matches[0] : null;
}

function populateDashboardTable() {
    const tableBody = document.getElementById('table-body-dashboard');

    if (tableBody) {
        while (tableBody.firstChild) {
            tableBody.removeChild(tableBody.firstChild);
        }

        dashboardData.forEach(entry => {
            if (entry.date) {
                const date = entry.date.valueOf();
                const row = document.createElement('tr');

                // Date column
                const dateCell = document.createElement('td');
                dateCell.textContent = getFormattedGermanDate(date);
                row.appendChild(dateCell);

                // Startleiter Optionen column
                const startleiterOptionenCell = document.createElement('td');
                startleiterOptionenCell.setAttribute('id', 'Optionen_startleiter_' + date);
                row.appendChild(startleiterOptionenCell);

                // Windenfahrer Optionen column
                const windenfahrerOptionenCell = document.createElement('td');
                windenfahrerOptionenCell.setAttribute('id', 'Optionen_windenfahrer_' + date);
                row.appendChild(windenfahrerOptionenCell);

                // Startleiter column
                const startleiterCell = document.createElement('td');
                startleiterCell.setAttribute('id', 'startleiter_' + date);
                row.appendChild(startleiterCell);

                // Windenfahrer column
                const windenfahrerCell = document.createElement('td');
                windenfahrerCell.setAttribute('id', 'windenfahrer_' + date);
                row.appendChild(windenfahrerCell);

                populatePilotOptions(startleiterOptionenCell, startleiterCell, entry.startleiterOptionen, date, 'startleiter', entry.startleiter);
                populatePilotOptions(windenfahrerOptionenCell, windenfahrerCell, entry.windenfahrerOptionen, date, 'windenfahrer', entry.windenfahrer);

                tableBody.appendChild(row);
            }
        });
    }
}

function populatePilotOptions(cell_option, cell_dienst, pilotOptions, date, dienst, entered) {
    pilotOptions.forEach(pilotId => {
        var pilotEntered = entered.includes(pilotId.id);

        var destination_cell = cell_option;

        if (pilotEntered) {
            destination_cell = cell_dienst;
            enteredDienste.push({ pilot_id: pilotId.id, name: pilotId.name.replace("+", "").replace("-", ""), date: date, dienst: dienst });
        }

        const pilotDiv = document.createElement('div');
        pilotDiv.textContent = `${pilotId.name}`;
        pilotDiv.setAttribute('data-pilot-id', `${pilotId.id}`);
        pilotDiv.classList.add('pilot-div');

        if (pilotEntered) {
            pilotDiv.classList.add('pilot-div-entered');
        }

        if (pilotId.name.includes('-')) {
            pilotDiv.classList.add('pilot-div-not-available');
        } else if (pilotId.name.includes('+')) {
            pilotDiv.classList.add('pilot-div-wish');
        }

        destination_cell.appendChild(pilotDiv);
    });
}



function populatePilotOptions2(cell_option, cell_dienst, pilotOptions, date, dienst, entered) {

    pilotOptions.forEach(pilotId => {

        var pilotEntered = [];

        if (typeof (diensteJahr) === 'object') {
            pilotEntered = diensteJahr.filter(function (pilot2) {
                return pilot2.pilot_id == pilotId.id && pilot2.flugtag == date;
            });
        }

        var destination_cell = cell_option;

        if (pilotEntered.length > 0) {
            destination_cell = cell_dienst;
            enteredDienste.push({ pilot_id: pilotId.id, name: pilotId.name.replace("+", "").replace("-", ""), date: date, dienst: dienst });
        }


        const pilotDiv = document.createElement('div');
        pilotDiv.textContent = `${pilotId.name}`;
        pilotDiv.setAttribute('data-pilot-id', `${pilotId.id}`);
        pilotDiv.classList.add('pilot-div');
        if (pilotId.name.includes('-')) {
            pilotDiv.classList.add('pilot-div-not-available');
        } else if (pilotId.name.includes('+')) {
            pilotDiv.classList.add('pilot-div-wish');
        }
        destination_cell.appendChild(pilotDiv);

    });

}


function populatePilotTable() {

    const table = document.getElementById('pilotTable');
    if (table) {
        table.innerHTML = '';

        const pilotCount = {};


        // Add Counts
        enteredDienste.forEach(entry => {
            const dienst_short = entry.dienst == 'windenfahrer' ? ' (WF)' : ' (SL)'
            const pilotName = entry.name + dienst_short;
            pilotCount[pilotName] = (pilotCount[pilotName] || 0) + 1;
        });

        // Add 0 Values
        if (dashboardData.startleiterOptionen) {
            dashboardData.startleiterOptionen.forEach(entry => {
                const field = entry.name.replace("+", "").replace("-", "") + ' (SL)'
                pilotCount[field] = (pilotCount[field] || 0);
            });
        }
        if (dashboardData.windenfahrerOptionen) {
            dashboardData.windenfahrerOptionen.forEach(entry => {
                const field = entry.name.replace("+", "").replace("-", "") + ' (WF)'
                pilotCount[field] = (pilotCount[field] || 0);
            });
        }

        const headerRow = table.createTHead().insertRow();
        const headerCellName = headerRow.insertCell(0);
        const headerCellOccurrences = headerRow.insertCell(1);

        headerCellName.textContent = 'Pilot';
        headerCellOccurrences.textContent = '#Dienste';


        // Populate the table
        for (const pilotName in pilotCount) {
            const count = pilotCount[pilotName];

            // Create a new row
            const row = table.insertRow(-1);

            // Create two cells (columns) in the row
            const cell1 = row.insertCell(0);
            const cell2 = row.insertCell(1);

            // Set the content of the cells
            cell1.textContent = pilotName;
            cell2.textContent = count;
        }
    }
}

//Error Handling ist hier etwas schlecht
function saveDienste() {

    $.ajax({
        url: `deleteDienste.php?year=${saisonJahr}`,
        method: 'GET',
        dataType: 'json',
    })
        .done(deleteData => {
            if (deleteData.success) {
                console.log('Deleted old entries for the year:', saisonJahr);

                const ajaxPromises = [];

                enteredDienste.forEach(entry => {
                    const formData = new FormData();
                    formData.append('flugtag', entry.date);
                    formData.append('pilot_id', entry.pilot_id);

                    if (entry.dienst === 'windenfahrer') {
                        formData.append('windenfahrer', 1);
                        formData.append('startleiter', 0);
                    } else {
                        formData.append('windenfahrer', 0);
                        formData.append('startleiter', 1);
                    }

                    const dataObject = {
                        flugtag: entry.date,
                        pilot_id: entry.pilot_id,
                        windenfahrer: entry.dienst === 'windenfahrer' ? 1 : 0,
                        startleiter: entry.dienst === 'windenfahrer' ? 0 : 1
                    };

                    ajaxPromises.push(
                        $.ajax({
                            url: 'saveDienste.php',
                            method: 'POST',
                            data: dataObject
                        })
                            .done(data => {

                            })
                            .fail((xhr, status, error) => {
                                // Handle errors
                                showToast('Oops!', 'Etwas ist schiefgegangen!', 'Die Dienste konnten nicht gespeichert werden!', 'error');
                                console.error('Error saving Dienste:', error);
                            })
                    );
                });

                $.when.apply($, ajaxPromises)
                    .done(() => {
                        showToast('Juhu!', 'Das hat geklappt', 'Die Dienste wurden gespeichert!', 'success');
                    })
                    .fail(error => {
                        console.error('Error during Dienste processing:', error);
                        showToast('Oops!', 'Etwas ist schiefgegangen!', 'Die Dienste konnten nicht gespeichert werden!', 'error');
                    });
            } else {
                console.error('Eintrag konnte nicht gelöscht werden:', deleteData.error);
            }
        })
        .fail(error => {
            console.error('Fehler beim Löschen alter Einträge:', error);
        });
}






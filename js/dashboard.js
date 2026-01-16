function getDefaultHistoryRange() {
    const previousYear = new Date().getFullYear() - 1;
    return {
        startDate: new Date(previousYear, 0, 1, 12, 0, 0),
        endDate: new Date(previousYear, 11, 31, 12, 0, 0),
    };
}

function formatShortGermanDate(dateString) {
    const date = new Date(`${dateString}T00:00:00`);
    return date.toLocaleDateString('de-DE');
}

function updateHistoryRangeLabels(startDateString, endDateString) {
    const beforeLabel = `vor ${formatShortGermanDate(startDateString)}`;
    const rangeLabel = `${formatShortGermanDate(startDateString)} - ${formatShortGermanDate(endDateString)}`;

    document.querySelectorAll('.history-range-before-label').forEach(label => {
        label.textContent = beforeLabel;
    });
    document.querySelectorAll('.history-range-label').forEach(label => {
        label.textContent = rangeLabel;
    });
}

function getHistoryRangeValues() {
    const defaults = getDefaultHistoryRange();
    const startInput = document.getElementById('historyStartDate');
    const endInput = document.getElementById('historyEndDate');

    let startDate = defaults.startDate;
    let endDate = defaults.endDate;

    if (startInput && startInput.value) {
        startDate = new Date(`${startInput.value}T12:00:00`);
    }
    if (endInput && endInput.value) {
        endDate = new Date(`${endInput.value}T12:00:00`);
    }

    if (endDate < startDate) {
        endDate = new Date(startDate.getTime());
    }

    if (startInput && !startInput.value) {
        startInput.value = formatDateString(startDate);
    }
    if (endInput && !endInput.value) {
        endInput.value = formatDateString(endDate);
    }

    return {
        startDate: formatDateString(startDate),
        endDate: formatDateString(endDate),
    };
}

function initHistoryRange() {
    const startInput = document.getElementById('historyStartDate');
    const endInput = document.getElementById('historyEndDate');
    const applyButton = document.getElementById('historyRangeApply');

    if (!startInput || !endInput) {
        return;
    }

    const { startDate, endDate } = getHistoryRangeValues();
    updateHistoryRangeLabels(startDate, endDate);

    const applyRange = () => {
        const range = getHistoryRangeValues();
        updateHistoryRangeLabels(range.startDate, range.endDate);
        getDashboardData();
    };

    if (applyButton) {
        applyButton.addEventListener('click', applyRange);
    }

    startInput.addEventListener('change', () => updateHistoryRangeLabels(getHistoryRangeValues().startDate, getHistoryRangeValues().endDate));
    endInput.addEventListener('change', () => updateHistoryRangeLabels(getHistoryRangeValues().startDate, getHistoryRangeValues().endDate));
}

function getDashboardData() {

    let startDate = formatDateString(saisonStartDate);
    let endDate = formatDateString(saisonEndDate);
    const historyRange = getHistoryRangeValues();
    updateHistoryRangeLabels(historyRange.startDate, historyRange.endDate);

    enteredDienste = [];

    const promises = [
        $.ajax({
            url: 'getDashboardData.php',
            type: 'GET',
            dataType: 'json',
            data: { startDate: startDate, endDate: endDate }
        }),
        $.ajax({
            url: 'getDashboardDataHistory.php',
            type: 'GET',
            dataType: 'json',
            data: { startDate: historyRange.startDate, endDate: historyRange.endDate }
        }),
    ];

    $.when(...promises).then(function (dataResponse, historyResponse) {

        const data = dataResponse[0];
        const history = historyResponse[0];


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
        dashboardDataHistory = history;
        populateDashboardTable();
        //populatePilotTable();
        populateDashboardHistory();
    }).fail(function (xhr, status, error) {
        console.error('Dashboard Daten konnten nicht geladen werden:', status, error);
    });
}
function populateDashboardHistory() {
    console.log("history");
    console.log(dashboardDataHistory);

    // Add the sum to each row and calculate points
    dashboardDataHistory.forEach(row => {
        row.sum = row.duties_count_history + row.active_duties_count_history + row.duties_count_thisyear + row.active_duties_count_thisyear - row.active_flying_days_history * 0.2;
    });

    // Sort the data by the sum (ascending)
    dashboardDataHistory.sort((a, b) => a.sum - b.sum);

    const tbody = document.querySelector('#diensteHistory tbody');
    tbody.innerHTML = ''; // Clear the table body

    // Populate table rows
    dashboardDataHistory.forEach(row => {
        const tr = document.createElement('tr');

        // Create cells
        const fullName = `${row.firstname} ${row.lastname}`.trim();
        const nameCell = document.createElement('td');
        nameCell.classList.add('dienste-name');
        nameCell.textContent = fullName;
        nameCell.title = fullName;
        
        // Conditionally format the last two columns
        const noDutiesCell_hist = `<td style="background-color: ${row.duties_count_history === 0 ? 'orange' : 'inherit'}">${row.duties_count_history}</td>`;
        const activeDutiesCell_hist = `<td style="background-color: ${row.active_duties_count_history === 0 ? 'orange' : 'inherit'}">${row.active_duties_count_history}</td>`;
        const noDutiesCell_thisSeason = `<td style="background-color: ${row.duties_count_thisyear === 0 ? 'orange' : 'inherit'}">${row.duties_count_thisyear}</td>`;
        const activeDutiesCell_thisSeason = `<td style="background-color: ${row.active_duties_count_thisyear === 0 ? 'orange' : 'inherit'}">${row.active_duties_count_thisyear}</td>`;
        const activeFlyingDaysHistory = `<td style="background-color: ${row.active_flying_days_history === 0 ? 'green' : 'orange'}">${row.active_flying_days_history}</td>`;

        // Calculate points using the new sum, rounded to 1 decimal place
        const roundedSum = parseFloat(row.sum.toFixed(1));
        const points = `<td style="background-color: ${row.sum <= 0 ? 'orange' : 'inherit'}">${roundedSum}</td>`;

        // Add cells to the row
        tr.appendChild(nameCell);
        tr.insertAdjacentHTML(
            'beforeend',
            noDutiesCell_hist + activeDutiesCell_hist + noDutiesCell_thisSeason + activeDutiesCell_thisSeason + activeFlyingDaysHistory + points
        );
        tbody.appendChild(tr);
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

                // Create a new table for each date
                const dateTable = document.createElement('table');
                dateTable.classList.add('date-table');

                // Create table header
                const headerRow = document.createElement('tr');

                const dateHeaderCell = document.createElement('th');
                dateHeaderCell.textContent = getFormattedGermanDate(date);
                dateHeaderCell.colSpan = 2; // Span for expandability
                //dateHeaderCell.style.width = '60%'; 
                headerRow.appendChild(dateHeaderCell);

                const startleiterHeaderCell = document.createElement('th');
                startleiterHeaderCell.textContent = 'Startleiter';
                //startleiterHeaderCell.style.width = '20%'; 
                headerRow.appendChild(startleiterHeaderCell);

                const windenfahrerHeaderCell = document.createElement('th');
                windenfahrerHeaderCell.textContent = 'Windenfahrer';
                //windenfahrerHeaderCell.style.width = '20%'; 
                headerRow.appendChild(windenfahrerHeaderCell);

                dateTable.appendChild(headerRow);

                // Add main row
                const mainRow = document.createElement('tr');
                mainRow.classList.add('main-row');

                // Expandable section
                const expandableCell = document.createElement('td');
                expandableCell.colSpan = 2; // For the first two columns
                const expandButton = document.createElement('button');
                expandButton.textContent = '+';
                expandButton.classList.add('expand-button');
                expandButton.addEventListener('click', function () {
                    const detailRow = document.getElementById(`detail-row-${date}`);
                    if (detailRow.style.display === 'none') {
                        detailRow.style.display = '';
                        expandButton.textContent = '-';
                    } else {
                        detailRow.style.display = 'none';
                        expandButton.textContent = '+';
                    }
                });
                expandableCell.appendChild(expandButton);
                mainRow.appendChild(expandableCell);

                // Startleiter cell
                const startleiterCell = document.createElement('td');
                startleiterCell.setAttribute('id', 'startleiter_' + date);
                mainRow.appendChild(startleiterCell);

                // Windenfahrer cell
                const windenfahrerCell = document.createElement('td');
                windenfahrerCell.setAttribute('id', 'windenfahrer_' + date);
                mainRow.appendChild(windenfahrerCell);

                dateTable.appendChild(mainRow);

                // Add detail row (initially hidden)
                const detailRow = document.createElement('tr');
                detailRow.setAttribute('id', `detail-row-${date}`);
                detailRow.style.display = 'none';

                const startleiterOptionenCell = document.createElement('td');
                startleiterOptionenCell.setAttribute('id', 'Optionen_startleiter_' + date);
                startleiterOptionenCell.style.width = '30%'; 
                detailRow.appendChild(startleiterOptionenCell);

                const windenfahrerOptionenCell = document.createElement('td');
                windenfahrerOptionenCell.setAttribute('id', 'Optionen_windenfahrer_' + date);
                windenfahrerOptionenCell.style.width = '30%'; 
                detailRow.appendChild(windenfahrerOptionenCell);

                // Empty cells for alignment with the main row
                detailRow.appendChild(document.createElement('td'));
                detailRow.appendChild(document.createElement('td'));

                dateTable.appendChild(detailRow);

                // Populate the cells
                populatePilotOptions(startleiterOptionenCell, startleiterCell, entry.startleiterOptionen, date, 'startleiter', entry.startleiter);
                populatePilotOptions(windenfahrerOptionenCell, windenfahrerCell, entry.windenfahrerOptionen, date, 'windenfahrer', entry.windenfahrer);

                tableBody.appendChild(dateTable);
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
            // Only add if not already in enteredDienste (prevent duplicates)
            const alreadyExists = enteredDienste.some(
                (item) => item.pilot_id === pilotId.id && item.date === date && item.dienst === dienst
            );
            if (!alreadyExists) {
                enteredDienste.push({ pilot_id: pilotId.id, name: pilotId.name.replace("+", "").replace("-", ""), date: date, dienst: dienst });
            }
        }

        const pilotDiv = document.createElement('div');
        pilotDiv.textContent = `${pilotId.name}`;
        pilotDiv.setAttribute('data-pilot-id', `${pilotId.id}`);
        pilotDiv.classList.add('pilot-div');

        if (pilotEntered) {
            pilotDiv.classList.add('pilot-div-entered');
        }

        if (pilotId.name.endsWith('-')) {
            pilotDiv.classList.add('pilot-div-not-available');
        } else if (pilotId.name.endsWith('+')) {
            pilotDiv.classList.add('pilot-div-wish');
        }

        destination_cell.appendChild(pilotDiv);
    });
}


function populatePilotTable() {}

/*




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
}*/

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
                                console.log('Dienste saved:', data);

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






function getDefaultHistoryStartDate() {
    return new Date(2024, 0, 1, 12, 0, 0);
}

function getHistoryStartDateValue() {
    const startInput = document.getElementById('historyStartDate');
    let startDate = getDefaultHistoryStartDate();

    if (startInput && startInput.value) {
        startDate = new Date(`${startInput.value}T12:00:00`);
    }

    if (startInput && !startInput.value) {
        startInput.value = formatDateString(startDate);
    }

    return formatDateString(startDate);
}

function initHistoryRange() {
    const startInput = document.getElementById('historyStartDate');
    const applyButton = document.getElementById('historyRangeApply');

    if (!startInput) {
        return;
    }

    // Set default value
    getHistoryStartDateValue();

    if (applyButton) {
        applyButton.addEventListener('click', () => {
            getDashboardData();
        });
    }
}

function getDashboardData() {

    let startDate = formatDateString(saisonStartDate);
    let endDate = formatDateString(saisonEndDate);
    const historyStartDate = getHistoryStartDateValue();

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
            data: { startDate: historyStartDate }
        }),
    ];

    $.when(...promises).then(function (dataResponse, historyResponse) {

        const data = dataResponse[0];
        const history = historyResponse[0];


        // Clean data, weil SQL code nicht richtig funktioniert!

        data.forEach(entry => {
            const startleiterWithPlus = new Set(entry.startleiterOptionen.map(pilot => ({ name: pilot.name, id: extractNumericId(pilot.id), max_dienste_halbjahr: pilot.max_dienste_halbjahr })));
            const windenfahrerWithPlus = new Set(entry.windenfahrerOptionen.map(pilot => ({ name: pilot.name, id: extractNumericId(pilot.id), max_dienste_halbjahr: pilot.max_dienste_halbjahr })));

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
        
        // Sort pilot cards according to history table order
        sortPilotCards();
        
        // Update disabled states after dashboard is populated
        if (typeof updateDisabledStates === 'function') {
            updateDisabledStates();
        }
    }).fail(function (xhr, status, error) {
        console.error('Dashboard Daten konnten nicht geladen werden:', status, error);
    });
}
function refreshDashboardHistory() {
    const historyStartDate = getHistoryStartDateValue();
    
    $.ajax({
        url: 'getDashboardDataHistory.php',
        type: 'GET',
        dataType: 'json',
        data: { startDate: historyStartDate }
    })
    .done(function(history) {
        dashboardDataHistory = history;
        populateDashboardHistory();
        sortPilotCards();
    })
    .fail(function(xhr, status, error) {
        console.error('Failed to refresh history data:', status, error);
    });
}

// Sort pilot cards in option cells according to history table order
function sortPilotCards() {
    // Get the sorted pilot IDs from dashboardDataHistory
    const sortedPilotIds = dashboardDataHistory.map(row => String(row.pilot_id));
    
    // Find all option cells (both startleiter and windenfahrer)
    $('[id^="Optionen_startleiter_"], [id^="Optionen_windenfahrer_"]').each(function() {
        const cell = $(this);
        const cards = cell.find('.pilot-div').toArray();
        
        // Sort cards based on their position in the history table
        cards.sort((a, b) => {
            const idA = $(a).attr('data-pilot-id');
            const idB = $(b).attr('data-pilot-id');
            const indexA = sortedPilotIds.indexOf(idA);
            const indexB = sortedPilotIds.indexOf(idB);
            
            // If pilot not found in history, put at end
            const posA = indexA === -1 ? 9999 : indexA;
            const posB = indexB === -1 ? 9999 : indexB;
            
            return posA - posB;
        });
        
        // Re-append cards in sorted order
        cards.forEach(card => cell.append(card));
    });
    
    // Also sort cards in the assigned cells (startleiter_*, windenfahrer_*)
    $('[id^="startleiter_"], [id^="windenfahrer_"]').each(function() {
        const cellId = $(this).attr('id');
        // Skip if this is an Optionen cell
        if (cellId.startsWith('Optionen_')) return;
        
        const cell = $(this);
        const cards = cell.find('.pilot-div').toArray();
        
        if (cards.length > 1) {
            cards.sort((a, b) => {
                const idA = $(a).attr('data-pilot-id');
                const idB = $(b).attr('data-pilot-id');
                const indexA = sortedPilotIds.indexOf(idA);
                const indexB = sortedPilotIds.indexOf(idB);
                
                const posA = indexA === -1 ? 9999 : indexA;
                const posB = indexB === -1 ? 9999 : indexB;
                
                return posA - posB;
            });
            
            cards.forEach(card => cell.append(card));
        }
    });
}

function populateDashboardHistory() {
    console.log("history");
    console.log(dashboardDataHistory);

    // Add the sum to each row and calculate points
    dashboardDataHistory.forEach(row => {
        row.sum = row.duties_count + row.active_duties_count - row.active_flying_days * 0.2;
    });

    const isWindenfahrer = (row) => row.windenfahrer === true || row.windenfahrer === 1 || row.windenfahrer === '1';

    const startleiterRows = dashboardDataHistory
        .filter(row => !isWindenfahrer(row))
        .sort((a, b) => a.sum - b.sum);

    const windenfahrerRows = dashboardDataHistory
        .filter(row => isWindenfahrer(row))
        .sort((a, b) => a.sum - b.sum);

    const startleiterTbody = document.querySelector('#diensteHistoryStartleiter tbody');
    const windenfahrerTbody = document.querySelector('#diensteHistoryWindenfahrer tbody');
    const legacyTbody = document.querySelector('#diensteHistory tbody');

    const renderRows = (rows, tbody) => {
        if (!tbody) {
            return;
        }

        tbody.innerHTML = '';

        rows.forEach(row => {
            const tr = document.createElement('tr');

            const fullName = `${row.firstname} ${row.lastname}`.trim();
            const nameCell = document.createElement('td');
            nameCell.classList.add('dienste-name');
            nameCell.textContent = fullName;
            nameCell.title = fullName;

            const dutiesCell = `<td style="background-color: ${row.duties_count === 0 ? '#ffd699' : 'inherit'}">${row.duties_count}</td>`;
            const activeDutiesCell = `<td style="background-color: ${row.active_duties_count === 0 ? '#ffd699' : 'inherit'}">${row.active_duties_count}</td>`;
            const activeFlyingDays = `<td style="background-color: ${row.active_flying_days === 0 ? '#8bffb1' : '#ffd699'}">${row.active_flying_days}</td>`;

            const roundedSum = parseFloat(row.sum.toFixed(1));
            const points = `<td style="background-color: ${row.sum <= 0 ? '#ffd699' : 'inherit'}">${roundedSum}</td>`;

            tr.appendChild(nameCell);
            tr.insertAdjacentHTML('beforeend', dutiesCell + activeDutiesCell + activeFlyingDays + points);
            tbody.appendChild(tr);
        });
    };

    renderRows(startleiterRows, startleiterTbody);
    renderRows(windenfahrerRows, windenfahrerTbody);

    // Backward compatibility if old single table markup is still present
    if (legacyTbody) {
        renderRows([...startleiterRows, ...windenfahrerRows], legacyTbody);
    }

    // Keep card sorting in sync with new table order (Startleiter first, then Windenfahrer)
    dashboardDataHistory = [...startleiterRows, ...windenfahrerRows];
}


function extractNumericId(name) {
    const matches = name.match(/\d+/);
    return matches ? matches[0] : null;
}

function buildCurrentDutyCounts(entries) {
    const dutyCounts = {};

    entries.forEach(entry => {
        (entry.startleiter || []).forEach(pilotId => {
            dutyCounts[pilotId] = (dutyCounts[pilotId] || 0) + 1;
        });

        (entry.windenfahrer || []).forEach(pilotId => {
            dutyCounts[pilotId] = (dutyCounts[pilotId] || 0) + 1;
        });
    });

    return dutyCounts;
}

function updatePilotDutyIndicators() {
    const currentDutyCounts = {};

    enteredDienste.forEach(entry => {
        const pilotId = String(entry.pilot_id);
        currentDutyCounts[pilotId] = (currentDutyCounts[pilotId] || 0) + 1;
    });

    $('.pilot-div').each(function() {
        const card = $(this);
        const pilotId = String(card.attr('data-pilot-id'));
        const baseName = card.attr('data-pilot-name') || card.text().replace(/\s*\{\d+\}\s*von\s*\{[^}]+\}\s*$/, '');
        const maxDiensteAttr = card.attr('data-max-dienste');
        const maxDuties = (maxDiensteAttr !== '' && maxDiensteAttr !== undefined) ? maxDiensteAttr : 'offen';
        const currentDuties = currentDutyCounts[pilotId] || 0;

        card.text(`${baseName} {${currentDuties}} von {${maxDuties}}`);
    });
}

function populateDashboardTable() {
    const tableBody = document.getElementById('table-body-dashboard');
    const currentDutyCounts = buildCurrentDutyCounts(dashboardData);

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
                populatePilotOptions(startleiterOptionenCell, startleiterCell, entry.startleiterOptionen, date, 'startleiter', entry.startleiter, currentDutyCounts);
                populatePilotOptions(windenfahrerOptionenCell, windenfahrerCell, entry.windenfahrerOptionen, date, 'windenfahrer', entry.windenfahrer, currentDutyCounts);

                tableBody.appendChild(dateTable);
            }
        });
    }
}

function populatePilotOptions(cell_option, cell_dienst, pilotOptions, date, dienst, entered, currentDutyCounts) {
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
                enteredDienste.push({ 
                    pilot_id: pilotId.id, 
                    name: pilotId.name.replace("+", "").replace("-", ""), 
                    date: date, 
                    dienst: dienst,
                    max_dienste_halbjahr: pilotId.max_dienste_halbjahr
                });
            }
        }

        const pilotDiv = document.createElement('div');
        
        // Build display name with current duties and max duties indicator
        let displayName = pilotId.name;
        const currentDuties = currentDutyCounts && currentDutyCounts[pilotId.id] ? currentDutyCounts[pilotId.id] : 0;
        const maxDuties = (pilotId.max_dienste_halbjahr !== null && pilotId.max_dienste_halbjahr !== undefined)
            ? pilotId.max_dienste_halbjahr
            : 'offen';

        displayName += ` {${currentDuties}} von {${maxDuties}}`;
        pilotDiv.textContent = displayName;
        
        pilotDiv.setAttribute('data-pilot-id', `${pilotId.id}`);
        pilotDiv.setAttribute('data-pilot-name', `${pilotId.name}`);
        pilotDiv.setAttribute('data-max-dienste', pilotId.max_dienste_halbjahr !== null ? pilotId.max_dienste_halbjahr : '');
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

// Check if any pilot exceeds their max dienste limit and show warning
function checkMaxDiensteExceeded() {
    const pilotCounts = {};
    const pilotMaxLimits = {};
    const pilotNames = {};
    
    // Count duties per pilot and collect max limits
    enteredDienste.forEach(entry => {
        const pilotId = entry.pilot_id;
        pilotCounts[pilotId] = (pilotCounts[pilotId] || 0) + 1;
        
        // Store the max limit and name (only needs to be set once per pilot)
        if (entry.max_dienste_halbjahr !== null && entry.max_dienste_halbjahr !== undefined) {
            pilotMaxLimits[pilotId] = entry.max_dienste_halbjahr;
        }
        if (entry.name) {
            pilotNames[pilotId] = entry.name;
        }
    });
    
    // Check for exceeded limits
    const exceededPilots = [];
    for (const pilotId in pilotCounts) {
        const count = pilotCounts[pilotId];
        const maxLimit = pilotMaxLimits[pilotId];
        
        if (maxLimit !== undefined && count > maxLimit) {
            exceededPilots.push({
                name: pilotNames[pilotId] || `Pilot ${pilotId}`,
                count: count,
                max: maxLimit
            });
        }
    }
    
    return exceededPilots;
}

//Error Handling ist hier etwas schlecht
function saveDienste() {
    
    // Check for pilots exceeding their max dienste limit
    const exceededPilots = checkMaxDiensteExceeded();
    if (exceededPilots.length > 0) {
        const warningMessages = exceededPilots.map(p => 
            `${p.name}: ${p.count} Dienste (max. ${p.max})`
        ).join(', ');
        
        showToast(
            'Achtung!', 
            'Maximale Dienste überschritten', 
            `Folgende Piloten haben mehr Dienste als gewünscht: ${warningMessages}`, 
            'warning'
        );
    }

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






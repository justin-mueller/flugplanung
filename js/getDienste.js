function getDienste() {

    let startDate = formatDateString(saisonStartDate);
    let endDate = formatDateString(saisonEndDate);

    let Dienste = [];

    $.ajax({
        url: 'getDienste.php',
        type: 'GET',
        dataType: 'json',
        data: { startDate: startDate, endDate: endDate, year: saisonJahr }
    })
        .then(function (data) {

            console.log("dienste loaded: ");
            console.log(data);

            const tableBody = document.querySelector('#diensteUebersicht tbody');
            tableBody.innerHTML = ''; // Clear existing rows

            data.forEach(entry => {

                // Convert comma-separated ID strings to arrays for proper matching
                const windenfahrerIdArray = entry.Windenfahrer_ids ? entry.Windenfahrer_ids.split(',').map(id => id.trim()) : [];
                const startleiterIdArray = entry.Startleiter_ids ? entry.Startleiter_ids.split(',').map(id => id.trim()) : [];
                const userPilotId = String(User_Information.pilot_id);
                
                const isMatch = windenfahrerIdArray.includes(userPilotId) || startleiterIdArray.includes(userPilotId);

                // Only create a row if 'flugtag' is not null
                if (entry.flugtag !== null) {
                    const row = tableBody.insertRow();

                    // Highlight each cell in the row if there's a match
                    const cell1 = row.insertCell(0); // Flugtag
                    const cell2 = row.insertCell(1); // Windenfahrer
                    const cell3 = row.insertCell(2); // Startleiter

                    if (isMatch) {
                        cell1.classList.add('bg-warning'); // Bootstrap class for orange highlight
                        cell2.classList.add('bg-warning');
                        cell3.classList.add('bg-warning');
                    }

                    // Assign values, use '/' as default if null
                    cell1.textContent = entry.flugtag;
                    cell2.textContent = entry.Windenfahrer || '/';  // Use '/' if Windenfahrer is null
                    cell3.textContent = entry.Startleiter || '/';  // Use '/' if Startleiter is null
                }
            });

        }).fail(function (xhr, status, error) {
            console.error('Dienste Daten konnten nicht geladen werden:', status, error);
        });
}

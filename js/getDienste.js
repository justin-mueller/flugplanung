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

            const container = document.querySelector('#diensteUebersicht');
            if (!container) return;
            
            container.innerHTML = ''; // Clear existing cards

            // Filter out null flugtage
            const validEntries = data.filter(entry => entry.flugtag !== null);
            
            // Show empty state if no flugtage
            if (validEntries.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        Hier wurden noch keine Flugtage eingetragen!
                    </div>
                `;
                return;
            }

            validEntries.forEach(entry => {
                // Convert comma-separated ID strings to arrays for proper matching
                const windenfahrerIdArray = entry.Windenfahrer_ids ? entry.Windenfahrer_ids.split(',').map(id => id.trim()) : [];
                const startleiterIdArray = entry.Startleiter_ids ? entry.Startleiter_ids.split(',').map(id => id.trim()) : [];
                const userPilotId = String(User_Information.pilot_id);
                
                const isMyDuty = windenfahrerIdArray.includes(userPilotId) || startleiterIdArray.includes(userPilotId);
                
                // Parse date for display
                const dateObj = new Date(entry.flugtag);
                const day = dateObj.getDate();
                const monthNames = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
                const weekdayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
                const month = monthNames[dateObj.getMonth()];
                const weekday = weekdayNames[dateObj.getDay()];
                const fullDate = getFormattedGermanDate(entry.flugtag);
                
                // Determine if roles are filled
                const hasWindenfahrer = entry.Windenfahrer && entry.Windenfahrer !== '/';
                const hasStartleiter = entry.Startleiter && entry.Startleiter !== '/';
                
                // Build the card HTML
                const card = document.createElement('div');
                card.className = `card-row${isMyDuty ? ' card-highlight' : ''}`;
                
                card.innerHTML = `
                    <div class="card-row-header">
                        <div class="card-date-badge">
                            <div class="card-date-day">${day}</div>
                            <div class="card-date-month">${month}</div>
                        </div>
                        <div class="card-date-full">${weekday}, ${fullDate}</div>
                        ${isMyDuty ? '<div class="card-badge"><i class="fa-solid fa-star"></i> Mein Dienst</div>' : ''}
                    </div>
                    <div class="card-row-body">
                        <div class="card-block">
                            <div class="card-block-header">
                                <div class="card-block-icon">
                                    <i class="fa-solid fa-gear"></i>
                                </div>
                                Windenfahrer
                            </div>
                            <div class="card-block-content ${hasWindenfahrer ? 'card-block-filled' : 'card-block-empty'}">
                                ${hasWindenfahrer ? entry.Windenfahrer : 'Nicht besetzt'}
                            </div>
                        </div>
                        <div class="card-block">
                            <div class="card-block-header">
                                <div class="card-block-icon">
                                    <i class="fa-solid fa-flag"></i>
                                </div>
                                Startleiter
                            </div>
                            <div class="card-block-content ${hasStartleiter ? 'card-block-filled' : 'card-block-empty'}">
                                ${hasStartleiter ? entry.Startleiter : 'Nicht besetzt'}
                            </div>
                        </div>
                    </div>
                `;
                
                container.appendChild(card);
            });

        }).fail(function (xhr, status, error) {
            console.error('Dienste Daten konnten nicht geladen werden:', status, error);
        });
}

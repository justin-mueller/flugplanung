function calcSeasonStart(options) {
    if (options.earliestCalenderDate && Saison === 1) {
        return new Date(saisonJahr, 0, 1, 12, 0, 0) // 1.Januar
    } else if (!options.earliestCalenderDate && Saison === 1) {
        return new Date(saisonJahr, 2, 1, 12, 0, 0)  // 1.März
    } else if (Saison === 2)
        return new Date(saisonJahr, 6, 1, 12, 0, 0)  // 1.Juli
}

function calcSeasonEnd(options) {
    if (options.latestCalenderDate && Saison === 2) {
        return new Date(saisonJahr, 12, 0, 12, 0, 0) // 31.Dezember
    } else if (!options.latestCalenderDate && Saison === 2) {
        return new Date(saisonJahr, 10, 0, 12, 0, 0)  // 31.Oktober
    } else if (Saison === 1)
        return new Date(saisonJahr, 6, 0, 12, 0, 0)  // 30.Juni
}

function getFormattedGermanDate(dateString) {
    const options = { weekday: 'short', year: 'numeric', month: 'long', day: 'numeric' };
    const germanDate = new Date(dateString).toLocaleDateString('de-DE', options);
    return germanDate;
}


function parseDateStringWithGermanMonth(dateString) {
    const germanMonths = {
        Januar: '01', Februar: '02', März: '03', April: '04',
        Mai: '05', Juni: '06', Juli: '07', August: '08',
        September: '09', Oktober: '10', November: '11', Dezember: '12'
    };

    // Try to match format with weekday prefix first (e.g., "Freitag, 15. März 2024")
    let match = dateString.match(/\d+\. \D+ \d+/);
    if (match) {
        // Extract just the date part without weekday
        const datePart = match[0];
        const innerMatch = datePart.match(/(\d+)\. (\D+) (\d+)/);

        if (innerMatch) {
            const [, day, month, year] = innerMatch;
            const numericMonth = germanMonths[month];
            if (numericMonth) {
                const numericDateString = `${year}-${numericMonth}-${day.padStart(2, '0')}`;
                return new Date(numericDateString);
            }
        }
    }

    // Fallback to original regex for formats without weekday
    match = dateString.match(/(\d+)\. (\D+) (\d+)/);

    if (!match) {
        throw new Error('Invalid date string format: ' + dateString);
    }

    const [, day, month, year] = match;

    const numericMonth = germanMonths[month];
    if (!numericMonth) {
        throw new Error('Unknown month: ' + month);
    }

    const numericDateString = `${year}-${numericMonth}-${day.padStart(2, '0')}`;
    return new Date(numericDateString);
}


function dateToSQLFormat(date) {
    // Use local date components to avoid timezone conversion issues
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateString(date) {
    // Use local date components to avoid timezone conversion issues
    // toISOString() converts to UTC which can shift dates by ±1 day
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateStringA(date) {
    const formattedDate = new Date(date);
    formattedDate.setHours(0, 0, 0, 0);
    // Use local date components to avoid timezone conversion issues
    const year = formattedDate.getFullYear();
    const month = String(formattedDate.getMonth() + 1).padStart(2, '0');
    const day = String(formattedDate.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}


function calc_flugtag_date(datum) {

    let dateParts = datum.split('.');

    date_formatted = dateParts[2] + '-' + parseInt(dateParts[1]).toLocaleString('de-DE', {
        minimumIntegerDigits: 2
    }) + '-' + parseInt(dateParts[0]).toLocaleString('de-DE', {
        minimumIntegerDigits: 2
    });

    return date_formatted;

}

function calc_deadline(datum) {
    flugtag_deadline = new Date(datum);
    flugtag_deadline.setTime(flugtag_deadline.getTime() - 86400000);
    flugtag_deadline.setHours(20);
    flugtag_deadline.setMinutes(0);
    flugtag_deadline.setSeconds(0);
}



function updateCountdown() {
    // Get the current date and time
    const now = new Date();

    // Calculate the time difference between now and the target date
    const timeDifference = flugtag_deadline - now;

    // Check if the countdown has finished
    if (timeDifference < 0) {
        clearInterval(intervalId); // Clear the interval using the interval ID
        document.getElementById('countdown').innerHTML = "Warten auf Entscheidung vom Startleiter...";
    } else {
        // Calculate remaining time components
        const days = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

        // Display the remaining time
		document.getElementById('countdown').innerHTML = `Zeit bis Entscheidung (20 Uhr Vortrag): <strong>${days} Tage, ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}</strong>`;
    }
}
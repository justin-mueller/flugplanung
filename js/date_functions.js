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
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const germanDate = new Date(dateString).toLocaleDateString('de-DE', options);
    return germanDate;
}


function parseDateStringWithGermanMonth(dateString) {
    const germanMonths = {
        Januar: '01', Februar: '02', März: '03', April: '04',
        Mai: '05', Juni: '06', Juli: '07', August: '08',
        September: '09', Oktober: '10', November: '11', Dezember: '12'
    };

    const match = dateString.match(/(\d+)\. (\D+) (\d+)/);

    if (!match) {
        throw new Error('Invalid date string format');
    }

    const [, day, month, year] = match;

    const numericMonth = germanMonths[month];
    const numericDateString = `${year}-${numericMonth}-${day.padStart(2, '0')}`;
    return new Date(numericDateString);
}


function dateToSQLFormat(date) {
    return date.toISOString().split('T')[0];
}

function formatDateString(date) {
    return date.toISOString().slice(0, 10);
}

function formatDateStringA(date) {
    const formattedDate = new Date(date);
    formattedDate.setHours(0, 0, 0, 0);
    return formattedDate.toISOString().slice(0, 10);
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

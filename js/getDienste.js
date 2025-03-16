function getDienste() {

    let startDate = formatDateString(saisonStartDate);
    let endDate = formatDateString(saisonEndDate);

    Dienste = [];

    $.ajax({
        url: 'getDienste.php',
        type: 'GET',
        dataType: 'json',
        data: { startDate: startDate, endDate: endDate }
    })
        .then(function (data) {

            console.log("dienste loaded: ")
            console.log(data);

            data.forEach(entry => {

            });

        }).fail(function (xhr, status, error) {
            console.error('Dienste Daten konnten nicht geladen werden:', status, error);
        });
}





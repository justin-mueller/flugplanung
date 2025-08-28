function enterDienst(flugtag = null, pilot_id = null) {

    if (flugtag === null) flugtag = flugtag_formatted;
    if (pilot_id === null) pilot_id = User_Information.pilot_id;

    const dataObject = {
        flugtag: flugtag,
        pilot_id: pilot_id,
        windenfahrer: User_Information.windenfahrer === 1 ? 1 : 0,
        startleiter: User_Information.windenfahrer === 0 ? 1 : 0
    };

    $.ajax({
        url: 'saveDienste.php',
        method: 'POST',
        data: dataObject
    })
        .success(() => {
            $('#enterModal').modal('hide');
            showToast('Juhu!', 'Das hat geklappt', 'Dein Dienst wurde gespeichert!', 'success');
            getFlugtag();
        })
        .fail((xhr, status, error) => {
            showToast('Oops!', 'Etwas ist schiefgegangen!', 'Die Dienste konnten nicht gespeichert werden!', 'error');
            console.error('Error saving Dienste:', error);
        })
}

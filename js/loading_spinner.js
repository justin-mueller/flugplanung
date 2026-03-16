function toggleSpinner(show, selector = '#spinner') {
	if (show) {
		$(selector).removeClass('d-none');
	} else {
		$(selector).addClass('d-none');
	}
}

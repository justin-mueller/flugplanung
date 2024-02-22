function SelectSeason(clickedButton, Season_Chosen) {
	const seasonClassSelected = (Season_Chosen === 1) ? 'season1-button' : 'season2-button';
	const seasonClassNotSelected = (Season_Chosen === 1) ? 'season2-button' : 'season1-button';

	$(`.${seasonClassSelected}`).removeClass('btn-outline-secondary').addClass('btn-secondary');
	$(`.${seasonClassNotSelected}`).addClass('btn-outline-secondary').removeClass('btn-secondary');

	Saison = Season_Chosen;

	saisonStartDate = calcSeasonStart({ earliestCalenderDate: true });
	saisonEndDate = calcSeasonEnd({ latestCalenderDate: true });

	getUserWuensche();
	getDashboardData();
	loadFlugtage();

}

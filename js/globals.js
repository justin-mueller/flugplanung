var windenfahrer_official = null;
var startleiter_official = null;
var flugtag_formatted = null;
var flugtag_unformatted = null;
var flugtag_deadline = null;
var intervalId = setInterval(updateCountdown, 1000);
var min_pilot_amount_reached = false;
var total_pilot_count_all = [0, 0, 0];
var total_pilot_count_hdgf = [0, 0, 0];
var Active_Pilot_Choices = [null, null, null]
const Fluggebiete = ['Neustadt-Glewe', 'Hörpel', 'Altenmedingen'];
const localClubId = 198;
var enteredDienste = [];
var dashboardData = [];
const thisYear = new Date().getFullYear();
var Saison = 1;
var saisonJahr = thisYear;
var saisonStartDate = calcSeasonStart({ earliestCalenderDate: true });
var saisonEndDate = calcSeasonEnd({ latestCalenderDate: true });
var Flugtage = [];

var Flugbetrieb = [false, false, false]
var FlugbetriebAbgesagt = false;

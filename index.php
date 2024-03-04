<?php

require 'check_login.php';

// Access additional user data stored in the session, if available
$mitgliederData = isset($_SESSION['mitgliederData']) ? $_SESSION['mitgliederData'] : [];

// Convert the PHP array to a JSON string for JavaScript
$mitgliederJson = json_encode($mitgliederData);

?>

<script> var User_Information = <?php echo $mitgliederJson; ?>;</script>
	
<!DOCTYPE html>
<html lang="de">

<head>
	<title>Tagesplanung</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
	<link rel="stylesheet" href="css/bootstrap-datepicker.min.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<link rel="stylesheet" type="text/css" href="css/chatbox.css">
  
	<script type="text/javascript" src="js_modules/jquery.js"></script>
	<script type="text/javascript" src="js_modules/bootstrap.js"></script>
	<script type="text/javascript" src="js_modules/bootstrap-datepicker.min.js"></script>
	<script src="js/date_functions.js"></script>
	<script type="text/javascript" src="js/globals.js"></script>
	<script type="text/javascript" src="js/on_document_ready.js"></script>
	<script type="text/javascript" src="js/form_choose.js"></script>
	<script type="text/javascript" src="js/prio_calculation.js"></script>
	<script type="text/javascript" src="js/delete_pilot.js"></script>
	<script type="text/javascript" src="js/toasts.js"></script>
	<script type="text/javascript" src="js/get_flugtag.js"></script>
	<script type="text/javascript" src="js/betrieb.js"></script>
	<script type="text/javascript" src="js/enter_pilot.js"></script>
	<script type="text/javascript" src="js/loading_spinner.js"></script>
	<script type="text/javascript" src="js/manage_user_rights.js"></script>
	<script type="text/javascript" src="js/chatbox.js"></script>
	<script src="js/saison.js"></script>
	<script src="js/wunschliste.js"></script>
	<script src="js/dashboard.js"></script>
	<script type="text/javascript" src="js/flugtage.js"></script>
	</script>

</head>

<body>
	<div id="toastContainer"></div>
	<div id="unicorn"></div>
	<nav class="navbar navbar-dark bg-dark">
		<div class="avatar" id="avatar-header"></div>	
		<span class="navbar-brand mb-0 h1" style="margin-left: 1rem;" id="user_name_header"></span>
		<span class="navbar-brand mb-0 h1" style="margin-left: 1rem;"><a href="logout.php">-> Abmelden</a></span>
	</nav>

	<div class="container-fluid mt-3">

		<nav>
			<div class="nav nav-tabs" id="nav-tab" role="tablist">
				<button class="nav-link active ui-button" id="nav-flugplanung-tab" data-bs-toggle="tab"
					data-bs-target="#nav-flugplanung" type="button" role="tab" aria-controls="nav-flugplanung"
					aria-selected="true">Flugplanung</button>
				<button class="nav-link ui-button" style="display: none" id="nav-wunschliste-tab" data-bs-toggle="tab"
					data-bs-target="#nav-wunschliste" type="button" role="tab" aria-controls="nav-wunschliste"
					aria-selected="false">Wunschliste</button>
				<button class="nav-link ui-button" style="display: none" id="nav-dienste-tab" data-bs-toggle="tab"
					data-bs-target="#nav-dienste" type="button" role="tab" aria-controls="nav-dienste"
					aria-selected="false">Dienste</button>
				<button class="nav-link ui-button" style="display: none" id="nav-flugtage-tab" data-bs-toggle="tab"
					data-bs-target="#nav-flugtage" type="button" role="tab" aria-controls="nav-flugtage"
					aria-selected="false">Flugtage</button>
			</div>
		</nav>

		<div class="tab-content" id="nav-tabContent">

			<!-- TAB 1 -->
			<div class="tab-pane fade show active" id="nav-flugplanung" role="tabpanel"
				aria-labelledby="nav-flugplanung-tab">
				<?php require 'Tab_Flugplanung.php'; ?>
			</div>

			<!-- TAB 2 -->
			<div class="tab-pane fade" id="nav-wunschliste" role="tabpanel" aria-labelledby="nav-wunschliste-tab">
				<?php require 'Tab_Wunschliste.php'; ?>
			</div>

			<!-- TAB 3 -->
			<div class="tab-pane fade" id="nav-dienste" role="tabpanel" aria-labelledby="nav-dienste-tab">
				<?php require 'Tab_Dienste.php'; ?>
			</div>

			<!-- TAB 4 -->
			<div class="tab-pane fade" id="nav-flugtage" role="tabpanel" aria-labelledby="nav-flugtage-tab">
				<?php require 'Tab_Flugtage.php'; ?>
			</div>
		</div>
	</div>
</body>

</html>

<?php
require 'check_login.php';
require 'saison.php';
?>

<div id="Hinweis_Wunschliste" class="alert alert-warning d-flex align-items-center mt-3" role="alert">
	<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Warning:">
		<use xlink:href="#exclamation-triangle-fill" />
	</svg>
	<div>
		Diese Funktion wird derzeit noch nicht genutzt. Bitte Ankündigung abwarten! Später ersetzt dies die Excel
		Wunschliste.
	</div>
</div>

<table class="table">
	<thead>
		<tr>
			<th>Tag</th>
			<th>Ja</th>
			<th>Nein</th>
			<th>Egal</th>
		</tr>
	</thead>
	<tbody id="table-body"></tbody>
</table>

<button class="btn btn-primary ui-button" id="saveButton" onclick="saveWuensche()">Speichern</button>

<div class="row mt-3 mb-3">
	<div class="accordion" id="accordionExample">
		<div class="accordion-item">
			<h2 class="accordion-header" id="headingOne">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
					aria-expanded="false" aria-controls="collapseOne">
					Hilfe
				</button>
			</h2>
			<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
				data-bs-parent="#accordionExample">
				<div class="accordion-body">
					<strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse
					plugin adds the appropriate classes that we use to style each element. These classes control the
					overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of
					this with custom CSS or overriding our default variables. It's also worth noting that just about any
					HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
				</div>
			</div>
		</div>
	</div>
</div>
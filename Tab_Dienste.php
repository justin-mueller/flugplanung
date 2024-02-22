<?php require 'saison.php'; ?>

<div class="row">
	<div class="col-md-4">
		<div class="accordion position-sticky" id="accordionExample" style="top: 0;">
			<table id="pilotTable" class="table table-striped">
				<thead></thead>
				<tbody></tbody>
			</table>
		</div>
	</div>

	<div class="col-md-8">
		<div class="row mt-3 mb-3">
			<table class="table">
				<thead>
					<tr>
						<th>Date</th>
						<th class="Optionen_startleiter">Startleiter Optionen</th>
						<th class="Optionen_windenfahrer">Windenfahrer Optionen</th>
						<th class="startleiter">Startleiter</th>
						<th class="Windenfahrer">Windenfahrer</th>
					</tr>
				</thead>
				<tbody id="table-body-dashboard"></tbody>
			</table>
			<button class="btn btn-primary ui-button" id="saveButton" onclick="saveDienste()">Speichern</button>
		</div>
	</div>
</div>
<!-- Radio button for "first half / second half" -->
<?php
require 'check_login.php';

require 'saison.php';
?>
<div class="row mb-3">
	<div class="col-md-6">
		<div class="input-group date">
			<input type="text" class="form-control date-picker" data-date-format="dd-mm-yyyy" id="additional_flugtag"
				style="text-align: center" />
			<span class="input-group-append">
				<span class="input-group-text bg-light d-block">
					<i class="fa fa-calendar"></i>
				</span>
			</span>
		</div>

	</div>
</div>

<div class="row mb-3">

	<div class="col-md-6">
		<button class="btn btn-primary ui-button" onclick="enterSingleFlugtag()">Einzeltag eintragen</button>
	</div>
</div>


<div class="row mb-3">
	<div class="col-md-12">
		<button class="btn btn-warning ui-button" onclick="getWeekendDays()">Alle Wochenenden eintragen</button>
	</div>
</div>


<script type="text/javascript">
	$(function () {
		$('#additional_flugtag').datepicker({
			format: 'dd.mm.yyyy'
		});
		$('#additional_flugtag').val(new Date().toLocaleDateString("de-DE"));
	});
</script>


<!-- Flugtage Table -->
<table class="table">
	<thead>
		<tr>
			<th>Datum</th>
			<th>Aktion</th>
		</tr>
	</thead>
	<tbody id="flugtageTableBody"></tbody>
</table>
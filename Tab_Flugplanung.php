<div class="row mt-3">
	<div class="col-12 mb-3">
		<form>

			<div class="input-group date" id="datepicker">
				<input type="text" class="form-control date-picker date" id="flugtag" style="text-align: center" />
				<span class="input-group-append">
					<span class="input-group-text bg-light d-block">
						<i class="fa fa-calendar"></i>
					</span>
				</span>
			</div>
		</form>
	</div>

</div>


<div class="row">
	<div class="col-12 mb-3">
		<div class="table-responsive">
			<table class="table table-hover table-bordered" id="tagesplanung">
				<thead>
					<tr>
						<th>Pilot</th>
						<th>Verein</th>
						<th>NGL</th>
						<th>HRP</th>
						<th>AMD</th>
						<th>Kommentar</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<div class="d-flex justify-content-center d-none" id="spinner">
			<div class="spinner-border" role="status"> </div>
		</div>
	</div>
</div>


<div class="row" id="eintraege">
	<div class="col-6 mb-3">
		<button type="button" class="btn btn-primary ui-button d-none" data-bs-toggle="modal"
			data-bs-target="#exampleModal" id="btn_enter">
			Eintragen
		</button>

		<button type="button" class="btn btn-warning mb-3 ui-button d-none" data-bs-toggle="modal"
			data-bs-target="#exampleModal" id="btn_update">
			Eintrag Ändern
		</button>

		<button type="button" class="btn btn-danger mb-3 ui-button d-none" id="btn_delete" onclick="delete_pilot()">
			Austragen
		</button>
	</div>
</div>

<div class="row  d-none" id="flugbetriebAusrufen">
	<div class="col-6 mb-3">
		<p>Betrieb ausrufen:</p>
		<button type="button" class="btn btn-danger ui-button mb-2" id="btn_betrieb0" onclick="BetriebAusrufen(0)">
			NGL
		</button>
		<button type="button" class="btn btn-danger ui-button mb-2" id="btn_betrieb1" onclick="BetriebAusrufen(1)">
			HRP
		</button>
		<button type="button" class="btn btn-danger ui-button mb-2" id="btn_betrieb2" onclick="BetriebAusrufen(2)">
			AMD
		</button>
	</div>

	<div class="col-6 mb-3">
		<p>Aufbau:</p>
		<input type="time" id="aufbau" name="appt" min="09:00" max="18:00" value="10:00" style="height:40px"
			onchange="BetriebAusrufen('timechange')" />

	</div>

</div>

<div class="row">
	<div class="col-12 mb-3">

		<div id="banner_flugbetrieb_0" class="alert alert-success d-flex align-items-center mt-3 mb-3 d-none"
			role="alert">
			<img src="img/flugbetrieb.svg" width="24" height="24" style="margin-right: 0.5rem" alt="Custom SVG">
			<div>
				Flugbetrieb in Neustadt-Glewe
			</div>
		</div>

		<div id="banner_flugbetrieb_1" class="alert alert-success d-flex align-items-center mt-3 mb-3 d-none"
			role="alert">
			<img src="img/flugbetrieb.svg" width="24" height="24" style="margin-right: 0.5rem" alt="Custom SVG">
			<div>
				Flugbetrieb in Hörpel
			</div>
		</div>

		<div id="banner_flugbetrieb_2" class="alert alert-success d-flex align-items-center mt-3 mb-3 d-none"
			role="alert">
			<img src="img/flugbetrieb.svg" width="24" height="24" style="margin-right: 0.5rem" alt="Custom SVG">
			<div>
				Flugbetrieb in Altenmedingen
			</div>
		</div>

		<div id="banner_aufbau" class="alert alert-warning d-flex align-items-center mt-3 mb-3" role="alert">
			<img src="img/clock.svg" width="24" height="24" style="margin-right: 0.5rem" alt="Custom SVG">
			<div>
				Aufbauzeit
			</div>
		</div>

		<div id="minpilotwarning" class="alert alert-warning d-flex align-items-center mt-3" role="alert">
			<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Warning:">
				<use xlink:href="img/warning.svg#warning-fill" />
				<div>
					Aktuell wird die Mindestanzahl von 3 HDGF Piloten in keinem Fluggebiet erreicht!
				</div>
		</div>

		<div id="minpilotreached" class="alert alert-primary d-flex align-items-center mt-3 d-none" role="alert">
			<svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:">
		</div>

		<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="display: block">
			<div class="toast-header">
				<img src="img/chat.svg" width="50px" alt="chat symbol">
				<strong class="me-auto">Chatbox</strong>
				<small>...noch etwas Geduld!</small>

			</div>


			<div class="toast-body">
				Eine Chatbox ist in Arbeit. Für Ad-Hoc Nachrichten für den Flugtag nutzt bitte unsere Signal
				Chatgruppe.<br>Hier ist der <a
					href="https://signal.group/#">Link</a>
				zur Gruppe. Alternativ kannst Du den QR-Code scannen:<br><br>
				<img src="img/qr_code_signal_chat.png" width="150px" alt="chat symbol">
			</div>
		</div>


		<div class="row mt-3 mb-3">
			<div class="accordion" id="accordionExample">
				<div class="accordion-item">
					<h2 class="accordion-header" id="headingOne">
						<button class="accordion-button" type="button" data-bs-toggle="collapse"
							data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
							Hilfe
						</button>
					</h2>
					<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
						data-bs-parent="#accordionExample">
						<div class="accordion-body">
							<strong>Willkommen zur neuen Flugplanung!</strong><br>Du hast ggf. noch einige Fragen. Im
							Prinzip ist alles wie vorher.
							Ein paar Neuerungen gibt es dennoch. Wenn Du das hier siehst, hast Du Dich ja bereits
							erfolgreich registriert - das ist schon mal gut!<br>
							Das heißt, jetzt kannst nur noch Du selbst Dich ein- und austragen. Demnächst wird auch die
							Wunschliste hier verfügbar sein (zweites Halbjahr 2024)<br>
							Was neu ist: ab jetzt wählst Du das Fluggebiet aus, welches du bevorzugst. Da wir aber ja 3
							an der Zahl haben, kannst du die anderen beiden übrigen als Alternative auswählen. Dann
							sieht jeder, ob du nur zu dem einen, oder wenn der Betrieb doch woanders stattfindet, auch
							dort hin kommen würdest. Toll, nicht wahr?
							<br>
							Wenn du Fragen zur neuen Planung hast, wende Dich bitte an Justin.
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="exampleModalLabel">Eintragen für Flugtag</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">


				<form action="insert.php" method="post" id="FluggebietForm">

					<div class="row">

						<div class="col border-end mb-4">
							<h4 class="mb-4 pb-3 border-bottom">Erste Wahl</h4>
							<div class="btn-group-vertical btn-block w-100">
								<button type="button" class="btn btn-outline-primary btn-block ui-button"
									id="list_fist_choice_1">Neustadt-Glewe</button>
								<button type="button" class="btn btn-outline-primary btn-block ui-button"
									id="list_fist_choice_2">Hörpel</button>
								<button type="button" class="btn btn-outline-primary btn-block ui-button"
									id="list_fist_choice_3">Altenmedingen</button>
							</div>

						</div>

						<div class="col border-end mb-4">
							<h4 class="mb-4 pb-3 border-bottom">Alternative</h4>

							<div class="btn-group-vertical w-100">
								<button type="button" class="btn btn-outline-primary ui-button"
									id="list_alternative_1">Hörpel</button>
								<button type="button" class="btn btn-outline-primary ui-button"
									id="list_alternative_2">Altenmedingen</button>
							</div>

						</div>

					</div>

					<div class="row mb-4">
						<div class="form-group">
							<label for="kommentar">Kommentar</label>
							<input autocomplete="off" type="text" name="kommentar" class="form-control" id="kommentar"
								aria-describedby="emailHelp" maxlength="100">

						</div>
					</div>

				</form>


				<div class="modal-footer">
					<button type="button" class="btn btn-outline-warning ui-button"
						data-bs-dismiss="modal">Abbrechen</button>
					<button type="button" class="btn btn-outline-success ui-button" onclick="enterPilot()"
						data-bs-dismiss="modal">Absenden</button>

				</div>
			</div>
		</div>
	</div>
</div>

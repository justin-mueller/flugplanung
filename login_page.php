<?php require 'login.php'; ?>

<!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login</title>
</head>

<body>

	<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<script type="text/javascript" src="js_modules/jquery.js"></script>
	<script type="text/javascript" src="js_modules/bootstrap.js"></script>
	<script type="text/javascript" src="js/register.js"></script>
	<script type="text/javascript" src="js/toasts.js"></script>

	<div id="toastContainer"></div>

	<section class="login">

		<div class="container py-5">
			<div class="row d-flex justify-content-center align-items-center">
				<div class="col-12 col-md-8 col-lg-6 col-xl-5">
					<div class="card shadow-2-strong" style="border-radius: 1rem;">
						<div class="card-body p-5 text-center">

							<h2 class="mb-2">Anmeldung</h2>
							<h4 class="mb-2">HDGF Flugplanung</h4>
							<p class="mb-4" style="color: #8e8e8e;font-style: italic;">Hier bist du richtig bei unserer
								neuen Flugplanung!<br>Ab jetzt müssen sich alle Flieger registrieren!<br><br>
								Die <a  style="color: red; font-style: italic; text-decoration: underline" href="https://hdgf.de/flugplanung_milan/milanplanung.php">Flugplanung für den Milaner e.V.</a> ist derzeit noch im alten System verfügbar.</p>


							<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

								<div class="form-outline mb-4">
									<input type="email" name="email" id="typeEmailX-2" value="<?php echo htmlspecialchars($email ?? ''); ?>"
										class="form-control form-control-lg" required />
									<label class="form-label" for="typeEmailX-2">E-mail</label>
								</div>

								<div class="form-outline mb-4">
									<input type="password" name="password" id="typePasswordX-2"	class="form-control form-control-lg" required />
									<label class="form-label" for="typePasswordX-2">Passwort</label>
								</div>

								<button class="btn btn-primary btn-lg btn-block mb-4 ui-button"	type="submit">Login</button>

								<?php if (isset($error)) {
									echo "<div class='alert alert-danger' role='alert'>E-Mail oder Passwort falsch!</div>";
								} ?>

							</form>

							<hr class="my-4">

							<p class="small mb-5 pb-lg-2"><a class="text-muted" href="#!"
									onclick="forgotPassword()">Passwort vergessen?</a></p>

							<p>Noch keinen Account?</p>
							<button type="button" class="btn btn-danger mb-5 btn-lg ui-button" onclick="openRegisterForm()">
								Hier registrieren!
							</button>

							<form method="post" id="registration-form" class="d-none">

							<div class="card mb-4">
								<div class="card-header">
									<h5 class="card-title">Allgemeine Daten</h5>
								</div>
								<div class="card-body">

									<div class="form-outline mb-4">
										<input type="email" name="email_register" id="email_register"
											class="form-control form-control-lg" required />
										<label class="form-label" for="email_register">E-mail</label>
									</div>
									<div class="form-outline mb-4">
										<input type="password" name="password_register" id="password_register"
											class="form-control form-control-lg" required />
										<label class="form-label" for="password_register">Passwort</label>
									</div>
									<div class="form-outline mb-4">
										<input type="text" name="vorname_register" id="vorname_register"
											class="form-control form-control-lg" required />
										<label class="form-label" for="vorname_register">Vorname</label>
									</div>
									<div class="form-outline mb-4">
										<input type="text" name="nachname_register" id="nachname_register"
											class="form-control form-control-lg" required />
										<label class="form-label" for="vorname_register">Nachname</label>
									</div>
									
									<div class="form-outline mb-4">

									<select class="form-select" name="verein_register" id="verein_register">
										<?php include 'options_vereine.php'; ?>
									</select>
										<label class="form-label" for="verein_register">Verein</label>
									</div>

									<div class="form-check d-flex justify-content-start mb-4">
										<input class="form-check-input" name="windenfahrer_register" type="checkbox"
											value="" id="windenfahrer_register" />
										<label class="form-check-label" for="windenfahrer_register">Ich habe einen
											Windenfahrerschein!</label>
									</div>
								</div>
							</div>


								<div class="card mb-4">
									<div class="card-header">
										<h5 class="card-title">Fluggerät</h5>
									</div>
									<div class="card-body">
										<div class="form-check d-flex justify-content-start mb-4">
											<input class="form-check-input" name="fluggeraet_gleitschirm" type="checkbox" value="" id="fluggeraet_gleitschirm" />
											<label class="form-check-label" for="fluggeraet_gleitschirm">Gleitschirm</label>
										</div>

										<div class="form-check d-flex justify-content-start mb-4">
											<input class="form-check-input" name="fluggeraet_drachen" type="checkbox" value="" id="fluggeraet_drachen" />
											<label class="form-check-label" for="fluggeraet_drachen">Drachen/Starrflügler</label>
										</div>

										<div class="form-check d-flex justify-content-start mb-4">
											<input class="form-check-input" name="fluggeraet_sonstiges" type="checkbox" value="" id="fluggeraet_sonstiges" />
											<label class="form-check-label" for="fluggeraet_sonstiges">Sonstiges</label>
										</div>
									</div>
								</div>


	
								<div class="card mb-4">
									<div class="card-header">
										<h5 class="card-title">Aussehen</h5>
									</div>
									<div class="card-body">

										<div class="form-outline mb-4">
											<div class="custom-select">
												<div class="avatar" id="avatar-preview">
												</div>	
												<select id="avatar-register" name="avatar_register" class="form-select" onchange="updatePreview()">
													<option value="1">Avatar 1</option>
													<option value="2">Avatar 2</option>
													<option value="3">Avatar 3</option>
													<option value="4">Avatar 4</option>
													<option value="5">Avatar 5</option>
													<option value="6">Avatar 6</option>
													<option value="7">Avatar 7</option>
													<option value="8">Avatar 8</option>
													<option value="9">Avatar 9</option>
													<option value="10">Avatar 10</option>
													<option value="11">Avatar 11</option>
													<option value="12">Avatar 12</option>
													<option value="13">Avatar 13</option>
													<option value="14">Avatar 14</option>
													<option value="15">Avatar 15</option>
													<option value="16">Avatar 16</option>
													<option value="17">Avatar 17</option>
													<option value="18">Avatar 18</option>
													<option value="19">Avatar 19</option>
												</select>
											</div>
											<label class="form-label" for="avatar-register">Avatar</label>
										</div>
									</div>
								</div>

								<button class="btn btn-primary btn-lg btn-block mb-4 ui-button" type="submit">Registrieren</button>
								<div id='register-error' class="alert alert-danger  d-none" role="alert"></div>
							</form>


						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</body>



</html>

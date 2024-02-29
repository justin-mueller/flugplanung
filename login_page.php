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
	<div id="unicorn"></div>

	<section class="login">

		<div class="container py-5">
			<div class="row d-flex justify-content-center align-items-center">
				<div class="col-12 col-md-8 col-lg-6 col-xl-5">
					<div class="card shadow-2-strong" style="border-radius: 1rem;">
						<div class="card-body p-5 text-center">

							<h2 class="mb-2">Anmeldung</h2>
							<h4 class="mb-2">HDGF Flugplanung</h4>
							<p class="mb-4" style="color: #8e8e8e;font-style: italic;">Hier bist du richtig bei unserer
								neuen Flugplanung!<br>Ab jetzt müssen sich alle FliegerInnen registrieren!</p>
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
									<div class="custom-select">
										<select id="verein_register" name="verein_register"
											class="form-control form-control-lg">
											<?php include 'options_vereine.php'; ?>
										</select>
									</div>
									<label class="form-label" for="verein_register">Verein</label>
								</div>

								<div class="form-check d-flex justify-content-start mb-4">
									<input class="form-check-input" name="windenfahrer_register" type="checkbox"
										value="" id="windenfahrer_register" />
									<label class="form-check-label" for="windenfahrer_register">Ich habe einen
										Windenfahrerschein!</label>
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

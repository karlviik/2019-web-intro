<?php

include('includes/auth.php');
include('includes/db.php');
include('includes/utils.php');

$message = "<br>";

if(user_logged_in()) {
	header('Location: home.php');
}

if(isset($_POST['register'])) {
	// handle registration and redirect as needed
	$prepared_query = $db->prepare("SELECT username FROM 185043iaibv1users WHERE username = ?");
	$prepared_query->bind_param('s', $_POST['username']);
  $prepared_query->execute();
  if ($prepared_query->get_result()->num_rows) {
    $message = "This username is already taken.";
  } else {
  	// insert user into db and redirect to login
	  $registration_query = $db->prepare("INSERT INTO 185043iaibv1users(username, name, password, email, bio, image) SELECT ?, ?, SHA1(?), ?, ?, ?");
	  $registration_query->bind_param('ssssss', $_POST['username'], $_POST['username'], $spw = $_POST['password'] . $_POST['username'], $_POST['email'], $aa = 'Hello.', $bb = 'default.png');
	  $registration_query->execute();
    header('Location: login.php');
  }
}
?>
<!doctype html>

<html lang="en">
	<head>
	    <meta charset="utf-8">
	    <title>Registration</title>
	    <link rel="stylesheet" href="css/styles.css">
	</head>
	<body>
		<h1 class="register">Register</h1>
		<p class="error">
			<?php if($message) echo $message?>
		</p>
		<div class="container"><div class="box">
			<form method="post">
			<input type="hidden" name="register">
			<label>Username
				<input type="text" placeholder="Username" name="username" required>
			</label>
			<label>Email
				<input type="email" placeholder="Email" name="email" required>
			</label>
			<label>Password
				<input id="password" type="password" placeholder="Password" name="password" required>
			</label>
			<label>Confirm password
				<input id="confirm_password" type="password" placeholder="Confirm password" name="password" required>
			</label>
			<input type="submit" value="Register">
		</form>
		<form>
			<input type="button" onclick="window.location.href = 'login.php'" value="Back"/>
		</form>
			</div></div>

		<!--  <script src="js/scripts.js"></script>-->
		<script>
			// src: https://codepen.io/diegoleme/pen/surIK
			let password = document.getElementById("password")
					, confirm_password = document.getElementById("confirm_password");

			function validatePassword(){
				if(password.value !== confirm_password.value) {
					confirm_password.setCustomValidity("Passwords Don't Match");
				} else {
					confirm_password.setCustomValidity('');
				}
			}

			password.onchange = validatePassword;
			confirm_password.onkeyup = validatePassword;
		</script>
	</body>
</html>
<?php

include ('includes/auth.php');
include ('includes/db.php');
include ('includes/utils.php');

$message = "<br>";

if(user_logged_in()) {
  header('Location: home.php');
}

if(isset($_POST['login'])) {
  // handle login
  $prepared_query = $db->prepare("SELECT id FROM 185043iaibv1users WHERE username = ? AND password = SHA1(?)");
  $spw = $_POST['password'] . $_POST['username'];
  $prepared_query->bind_param('ss', $_POST['username'], $spw);
  $prepared_query->execute();
  $result = $prepared_query->get_result();
  if (!$result->num_rows) {
    $message = "Wrong username and/or password.";
  } else {
	  $_SESSION['user_id'] = mysqli_fetch_assoc($result)['id'];
    header('Location: home.php');
  }
}

?>
<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">

    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


</head>

<body>
	<h1 class="login">Login</h1>
	<p class="error">
		<?php echo $message?>
	</p>
	<div class="container"><div class="box">
	<form method="post">
		<input type="hidden" name="login">
		<label>Username
			<input type="text" placeholder="Username" name="username" required>
		</label>
		<label>Password
			<input type="password" placeholder="Password" name="password" required>
		</label>
		<input type="submit" value="Login">
	</form>
	<form>
		<input type="button" onclick="window.location.href = 'register.php'" value="Register"/>
	</form>
		</div></div>
<!--  <script src="js/scripts.js"></script>-->
</body>
</html>
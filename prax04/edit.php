<?php

include('includes/auth.php');
include('includes/db.php');
include('includes/utils.php');

if (!user_logged_in()) {
  header('Location: login.php');
}
function upload_handler($db) {
	// src: https://www.w3schools.com/php/php_file_upload.asp
	// src: https://stackoverflow.com/questions/3509333/how-to-upload-save-files-with-desired-name
	if ($_FILES['image']['size'] == 0) {
		return "No file was selected";
	}
	$info = pathinfo($_FILES['image']['name']);
	$ext = strtolower($info['extension']);
	$img_name = $_SESSION['user_id'] . "." . $ext;
	$target = 'images/' . $img_name;

	// Check if image file is a actual image or fake image
	$check = getimagesize($_FILES["image"]["tmp_name"]);
	if ($check === false) {
		return "File is not an image.";
	}

	// Check file size
	if ($_FILES["image"]["size"] > 15000000) {
		return "Image is too large, limit is 15 MB.";
	}

	// Allow certain file formats
	if ($ext != "jpg" && $ext != "png" && $ext != "jpeg" && $ext != "gif") {
		return "Only JPG, JPEG, PNG & GIF files are allowed.";
	}

	if (move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
		// add the image filename to database too here

		$query = $db->prepare("UPDATE 185043iaibv1users SET image = ? WHERE id = ?");
		$query->bind_param('sd', $img_name, $_SESSION['user_id']);
		$query->execute();
		return "Image updated.";
	} else {
		return "There was an error uploading the image.";
	}
}

function info_update_handler($db) {
	$query = $db->prepare("UPDATE 185043iaibv1users SET name = ?, bio = ?, email = ? WHERE id = ?");
	$query->bind_param('sssd', $_POST['name'], $_POST['bio'], $_POST['email'], $_SESSION['user_id']);
	$query->execute();
	return "Info updated successfully.";
}

function password_change_handler($db, $username) {
	$prepared_query = $db->prepare("SELECT id FROM 185043iaibv1users WHERE username = ? AND password = SHA1(?)");
	$spw = $_POST['cur_pwd'] . $username;
	$prepared_query->bind_param('ss', $username, $spw);
	$prepared_query->execute();
	$result = $prepared_query->get_result();
	if (!$result->num_rows) {
		return "Wrong current password.";
	}
	$pwd_update = $db->prepare("UPDATE 185043iaibv1users SET password = SHA1(?) WHERE id = ?");
	$spw = $_POST['new_pwd'] . $username;
	$pwd_update->bind_param('sd', $spw, $_SESSION['user_id']);
	$pwd_update->execute();
	return "Password updated successfully.";
}

$img_message = "";
$gen_message = "";
$pwd_message = "";

if (isset($_POST["image_upload"])) {
	$img_message = upload_handler($db);
}

if (isset($_POST["info_update"])) {
	$gen_message = info_update_handler($db);
}

if (isset($_POST["password_change"])) {
	$pwd_message = password_change_handler($db, $username);
}


$prepared_query = $db->prepare("SELECT username, name, email, bio, image FROM 185043iaibv1users WHERE id = ?");
$prepared_query->bind_param('d', $_SESSION['user_id']);
$prepared_query->execute();
$result = mysqli_fetch_assoc($prepared_query->get_result());
$username = sanit($result['username']);
$name = sanit($result['name']);
$email = sanit($result['email']);
$bio = sanit($result['bio']);
$image = sanit($result['image']);



?>
<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Edit</title>
	<link rel="stylesheet" href="css/styles.css">
</head>

<div class="nav-bar">
	<a href="home.php">Home</a><a
					href="profile.php">Profile</a><form class="search"
					action="search.php" method="get"><input
						type="text" class="search-bar" placeholder="Search for users" name="s"></form><a
					class="active" href="edit.php">Edit</a><a
					href="logout.php">Logout</a>
</div>
<div class="container">
<div class="box">
<h1>Change image</h1>
<img class="profile" src="images/<?php echo $image ?>" alt="">
<p><?php echo $img_message ?></p>
<form method="post" enctype="multipart/form-data">
	Select image to upload:
	<input id="upload" type="file" name="image">
	<input type="submit" value="Upload Image" name="image_upload">
</form>
</div>

<div class="box">
<h1>Change name, email and bio</h1>
<p><?php echo $gen_message ?></p>
<form method="post">
	<label>Name
		<input type="text" placeholder="Name" name="name" value="<?php echo $name ?>" required>
	</label>
	<label>Email
		<input type="email" placeholder="Email" name="email" value="<?php echo $email ?>" required>
	</label>
	<label>Bio
		<textarea class="bio" placeholder="Bio" name="bio"><?php echo $bio ?></textarea>
	</label>
	<input type="submit" value="Update info" name="info_update">
</form>
</div>

<div class="box">
<h1>Change password</h1>
<p><?php echo $pwd_message ?></p>
<form method="post">
	<label>Old password
		<input type="password" placeholder="Current password" name="cur_pwd" required>
	</label>
	<label>New password
		<input id="password" type="password" placeholder="New password" name="new_pwd" required>
	</label>
	<label>Repeat new password
		<input id="confirm_password" type="password" placeholder="Repeat new password" name="rep_new_pwd" required>
	</label>
	<input type="submit" value="Change password" name="password_change">
</form>
</div>
</div>
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
<!--  <script src="js/scripts.js"></script>-->
</body>
</html>
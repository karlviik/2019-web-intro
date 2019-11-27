<?php

include('includes/auth.php');
include('includes/db.php');
include('includes/utils.php');

if (!user_logged_in()) {
	header('Location: login.php');
}
$search = "%%";
$user_input = "";
if (isset($_GET["s"])) {
	$user_input = strtolower($_GET["s"]);
	$search = '%' . $user_input . '%';
}
if (isset($_POST["follow_toggle"])) {
	toggle_follow($db, (int)$_POST["follow_id"]);
}

$results = search($db, $search);
?>
<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">

	<title>Search</title>
	<link rel="stylesheet" href="css/styles.css">

</head>

<body>

<div class="nav-bar">
	<a href="home.php">Home</a><a
					href="profile.php">Profile</a>
	<form class="search"
	      action="search.php" method="get"><input
						type="text" class="search-bar" placeholder="Search for users" name="s"></form>
	<a
					href="edit.php">Edit</a><a
					href="logout.php">Logout</a>
</div>

<div class="container">
	<div class="box">
		<h1>Search results for "<?php echo sanit($user_input) ?>"</h1>
		<?php
		if (sizeof($results)) {
			for ($i = 0; $i < sizeof($results); $i++) {
				$username = sanit($results[$i][0]);
				$name = sanit($results[$i][1]);
				$image = sanit($results[$i][2]);
				$id = (int)$results[$i][3];
				$add = "";
				if ($id != $_SESSION['user_id']) {
					$follow = is_following($db, $id);
					if ($follow) {
						$follow_txt = "Unfollow";
						$follow_class = "follow";
					} else {
						$follow_txt = "Follow";
						$follow_class = "nofollow";
					}
					$add = "<div class='follow-btn seb'><form class='seb' method='post'>
									<input type='hidden' name='follow_id' value=\"$id\">
									<input class='follow-btn $follow_class' type='submit' value='$follow_txt' name=\"follow_toggle\">
								</form></div>";
				}
				echo "<div class='search-result'>
									<img class='icon seb' src=\"images/$image\" alt=\"\">
									<p class='seb'><a class='user' href='profile.php?u=$id'>$name @$username</a></p>
									$add
							</div>";
			}
		} else {
			echo "<p>No results :(</p>";
		}
		?>
	</div>
</div>
<!--  <script src="js/scripts.js"></script>-->
</body>
</html>
<?php

include ('includes/auth.php');
include ('includes/db.php');
include ('includes/utils.php');

if(!user_logged_in()) {
  header('Location: login.php');
}
if (isset($_POST["follow_toggle"])) {
	toggle_follow($db, (int)$_POST["follow_id"]);
}
if (isset($_POST["retweet"])) {
	retweet($db, $_POST["retweet_id"]);
}
if (isset($_POST["like_toggle"])) {
	toggle_like($db, $_POST["like_id"]);
}
if (isset($_POST["comment-post"])) {
	comment($db, $_POST["tweet-id"], $_POST["comment-post"]);
}
if (isset($_POST["tweet-content"])) {
	tweet($db, $_POST["tweet-content"]);
}
$tweets = get_home_tweets($db);

?>
<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8">

    <title>Mini Twitter</title>
    <link rel="stylesheet" href="css/styles.css">


</head>
<body>

<div class="nav-bar">
<a class="active" href="home.php">Home</a><a
					href="profile.php">Profile</a><form class="search"
					action="search.php" method="get"><input
						type="text" class="search-bar" placeholder="Search for users" name="s"></form><a
					href="edit.php">Edit</a><a
					href="logout.php">Logout</a>
</div>

<div class='container'>
	<div class="box">
		<h3>Tweet tweet</h3>
		<form method="post">
			<input class="tweetsubmit" type="text" placeholder="Tweet something maybe?" name="tweet-content">
		</form>
	</div>

	<div class='box'>
		<h1>Tweets by you and by those you follow</h1>
		<?php
		if (!sizeof($tweets)) {
			echo "You or anyone you follow hasn't made any tweets yet :(";
		} else {
			for ($i = 0; $i < sizeof($tweets); $i++) {
				if (isset($tweets[$i]['retweet_id'])) {
					$tweet_id = $tweets[$i]['retweet_id'];
					$original_tweet = get_tweet($db, $tweet_id);
					$tweet_target_user_id = $original_tweet['user_id']; // user that made the original tweet
					$time = $original_tweet['time']; // time of the original tweet
					$tweet_name = sanit($original_tweet['name']); // original author name
					$tweet_username = sanit($original_tweet['username']); // original author username
					$tweet_image = $original_tweet['image'];// orriginal author image
					$tweet_message = sanit($original_tweet['message']);
					$formatted_retweet_time = format_time($tweets[$i]['time']);
					$retweeter_id = $tweets[$i]['user_id'];
					$retweeter_name = sanit($tweets[$i]['name']);
					$retweeter_username = sanit($tweets[$i]['username']);
					$retweet_add = "<p class='retweet-add'><a class='user' href='profile.php?u=$retweeter_id'>$retweeter_name @$retweeter_username</a> retweeted this at $formatted_retweet_time</p><br>"; //TODO
				} else {
					$retweet_add = "";
					$tweet_id = $tweets[$i]['id'];
					$tweet_target_user_id = $tweets[$i]['user_id'];
					$time = $tweets[$i]['time'];
					$tweet_name = sanit($tweets[$i]['name']);
					$tweet_username = sanit($tweets[$i]['username']);
					$tweet_image = $tweets[$i]['image'];
					$tweet_message = sanit($tweets[$i]['message']);
				}
				$add = "";
				$tweet_comments = get_tweet_comments($db, $tweet_id);
				if ($tweet_target_user_id != $_SESSION['user_id']) {
					$follow = is_following($db, $tweet_target_user_id);
					if ($follow) {
						$follow_txt = "Unfollow";
						$follow_class = "follow";
					}
					else {
						$follow_txt = "Follow";
						$follow_class = "nofollow";
					}
					$add = "<div class='follow-btn seb'><form class='seb' method='post'>
									<input type='hidden' name='follow_id' value=\"$tweet_target_user_id\">
									<input class='follow-btn $follow_class' type='submit' value='$follow_txt' name=\"follow_toggle\">
								</form></div>";
				}
				$formatted_time = format_time($time);
				$liked = is_liked($db, $tweet_id);
				$likeclass = "liked";
				$liketext = "Unlike";
				if (!$liked) {
					$likeclass = "noliked";
					$liketext = "Like";
				}
				$likeadd = "<div class='follow-btn seb'><form class='seb' method='post'>
									<input type='hidden' name='like_id' value=\"$tweet_id\">
									<input class='follow-btn $likeclass' type='submit' value='$liketext' name=\"like_toggle\">
								</form></div>";
				$retweet = "<div class='follow-btn seb'><form class='seb' method='post'>
									<input type='hidden' name='retweet_id' value=\"$tweet_id\">
									<input class='follow-btn' type='submit' value='Retweet' name=\"retweet\">
								</form></div>";
				$commentarea = "<form method='post'>
													<input type='hidden' name='tweet-id' value='$tweet_id'>
													<input class='comment-box' type='text' placeholder='Enter a comment' name='comment-post'>
												</form>";
				$comments = "";
				for ($j = 0; $j < sizeof($tweet_comments); $j++) {
					$comment_time = format_time($tweet_comments[$j]['time']);
					$comment_user_id = $tweet_comments[$j]['user_id'];
					$comment_content = sanit($tweet_comments[$j]['comment']);
					$comment_user_username = sanit($tweet_comments[$j]['username']);
					$comment_user_name = sanit($tweet_comments[$j]['name']);
					$comment_image = $tweet_comments[$j]['image'];

					$commentadd = "
											<div class='comment'>
												<img class='icon seb' src=\"images/$comment_image\" alt=''>
												<p class='seb'>
													<a class='user' href='profile.php?u=$comment_user_id'>$comment_user_name @$comment_user_username</a> at $comment_time:
												</p>
												<p class='comment-text'>$comment_content</p>
											</div>";
					$comments = $comments . $commentadd;
				}
				echo "<div class='search-result'>
									$retweet_add
									<div class='tweet'>
									<img class='icon seb' src=\"images/$tweet_image\" alt=\"\">
									<p class='seb'><a class='user' href='profile.php?u=$tweet_target_user_id'>$tweet_name @$tweet_username</a></p>
									$add
									<p class='tweet-content'>$tweet_message</p>
									<p class='tweet-sub'>$formatted_time</p> $likeadd $retweet</div>
									$commentarea $comments
							</div>";
			}
		}
		?>
	</div>
<!--  <script src="js/scripts.js"></script>-->
</body>
</html>
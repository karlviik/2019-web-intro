<?php

// constants
define('DB_HOST', 'localhost');
define('DB_USER', 'st2014');
define('DB_PASSWORD', 'progress');
define('DB_SCHEMA', 'st2014');

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_SCHEMA);

mysqli_set_charset($db, "utf8");


function get_followers($db, $target_user_id)
{
	$query = $db->prepare(
			'SELECT 185043iaibv1users.username, 185043iaibv1users.name, 185043iaibv1users.image, 185043iaibv1users.id, 185043iaibv1follows.target_user_id 
				FROM 185043iaibv1users
				LEFT JOIN 185043iaibv1follows ON 185043iaibv1follows.stalker_user_id = ? AND 185043iaibv1follows.target_user_id = 185043iaibv1users.id
				WHERE EXISTS (SELECT * FROM 185043iaibv1follows WHERE stalker_user_id = 185043iaibv1users.id AND target_user_id = ?)');
	$query->bind_param('dd', $_SESSION['user_id'], $target_user_id);
	$query->execute();
	$result = $query->get_result();
	$returnthing = [];
	while ($t = mysqli_fetch_assoc($result)) {
		array_push($returnthing, $t);
	}
	return $returnthing;
}

function get_follow_targets($db, $target_user_id)
{
	$query = $db->prepare(
			'SELECT 185043iaibv1users.username, 185043iaibv1users.name, 185043iaibv1users.image, 185043iaibv1users.id, 185043iaibv1follows.target_user_id 
				FROM 185043iaibv1users
				LEFT JOIN 185043iaibv1follows ON 185043iaibv1follows.stalker_user_id = ? AND 185043iaibv1follows.target_user_id = 185043iaibv1users.id
				WHERE EXISTS (SELECT * FROM 185043iaibv1follows WHERE stalker_user_id = ? AND target_user_id = 185043iaibv1users.id)');
	$query->bind_param('dd', $_SESSION['user_id'], $target_user_id);
	$query->execute();
	$result = $query->get_result();
	$returnthing = [];
	while ($t = mysqli_fetch_assoc($result)) {
		array_push($returnthing, $t);
	}
	return $returnthing;
}

function get_tweets($db, $target_user_id) {
	$query = $db->prepare(
			'SELECT 185043iaibv1tweets.id, 185043iaibv1tweets.time, 185043iaibv1tweets.retweet_id, 185043iaibv1tweets.message
				FROM 185043iaibv1tweets
				WHERE 185043iaibv1tweets.user_id = ?
				ORDER BY 185043iaibv1tweets.time DESC');
	$query->bind_param('d', $target_user_id);
	$query->execute();
	$result = $query->get_result();
	$tweets = [];
	while ($t = mysqli_fetch_assoc($result)) {
		array_push($tweets, $t);
	}
	for ($i = 0; $i < sizeof($tweets); $i++) {
		$id = $tweets[$i]['id'];
		if (isset($tweets[$i]['retweet_id'])) {
			$id = (int)$tweets[$i]['retweet_id'];
			$tweets[$i]['retweet'] = get_tweet($db, $id);
		}
		$tweets[$i]['comments'] = get_tweet_comments($db, $id);
	}
	return $tweets;
}

function get_home_tweets($db) {
	$query = $db->prepare('
								SELECT 185043iaibv1tweets.id, 185043iaibv1tweets.time, 185043iaibv1tweets.retweet_id, 185043iaibv1tweets.user_id, 185043iaibv1tweets.message, 185043iaibv1users.username, 185043iaibv1users.name, 185043iaibv1users.image
                FROM 185043iaibv1tweets
                JOIN 185043iaibv1users ON 185043iaibv1tweets.user_id = 185043iaibv1users.id
                WHERE EXISTS (SELECT * FROM 185043iaibv1follows WHERE stalker_user_id = ? AND target_user_id = 185043iaibv1users.id) OR 185043iaibv1users.id = ?
                ORDER BY 185043iaibv1tweets.time DESC'
	);
	$query->bind_param('dd', $_SESSION['user_id'], $_SESSION['user_id']);
	$query->execute();
	$result = $query->get_result();
	$tweets = [];
	while ($t = mysqli_fetch_assoc($result)) {
		array_push($tweets, $t);
	}
	for ($i = 0; $i < sizeof($tweets); $i++) {
		$id = $tweets[$i]['id'];
		if (isset($tweets[$i]['retweet_id'])) {
			$id = (int)$tweets[$i]['retweet_id'];
			$tweets[$i]['retweet'] = get_tweet($db, $id);
		}
		$tweets[$i]['comments'] = get_tweet_comments($db, $id);
	}
	return $tweets;
}

function get_tweet($db, $tweet_id) {
	$query = $db->prepare(
			'SELECT 185043iaibv1tweets.id, 185043iaibv1tweets.time, 185043iaibv1tweets.retweet_id, 185043iaibv1tweets.user_id, 185043iaibv1tweets.message, 185043iaibv1users.username, 185043iaibv1users.name, 185043iaibv1users.image
				FROM 185043iaibv1tweets
				JOIN 185043iaibv1users ON 185043iaibv1users.id = 185043iaibv1tweets.user_id
				WHERE 185043iaibv1tweets.id = ?');
	$query->bind_param('d', $tweet_id);
	$query->execute();
	return mysqli_fetch_assoc($query->get_result());
}

function has_retweeted($db, $tweet_id) {
	$query = $db->prepare(
			'SELECT *
				FROM 185043iaibv1tweets
				WHERE retweet_id = ? AND user_id = ?');
	$query->bind_param('dd', $tweet_id, $_SESSION['user_id']);
	$query->execute();
	$result = $query->get_result();
	if (!$result->num_rows) {
		return false;
	}
	return true;
}
function retweet($db, $tweet_id) {
	if (!has_retweeted($db, $tweet_id)) {
		master_tweet($db, $tweet_id, null);
	}
}

function tweet($db, $message) {
	master_tweet($db, null, $message);
}

function master_tweet($db, $tweet_id, $message) {
	$query = $db->prepare("INSERT INTO 185043iaibv1tweets(time, retweet_id, user_id, message) SELECT NOW(), ?, ?, ?");
	$query->bind_param('dds', $tweet_id, $_SESSION['user_id'], $message);
	$query->execute();
}

function toggle_like($db, $tweet_id) {
	if(is_liked($db, $tweet_id)) {
		$query = $db->prepare("DELETE FROM 185043iaibv1likes WHERE user_id = ? AND tweet_id = ?");
	} else {
		$query = $db->prepare("INSERT INTO 185043iaibv1likes(user_id, tweet_id) VALUES (?, ?)");
	}
	$query->bind_param('dd', $_SESSION['user_id'], $tweet_id);
	$query->execute();
}

function comment($db, $tweet_id, $comment) {
	$query = $db->prepare("INSERT INTO 185043iaibv1comments(time, tweet_id, user_id, comment) SELECT NOW(), ?, ?, ?");
	$query->bind_param('dds', $tweet_id, $_SESSION['user_id'], $comment);
	$query->execute();
}

function toggle_follow($db, $user_id) {
	if(is_following($db, $user_id)) {
		$query = $db->prepare("DELETE FROM 185043iaibv1follows WHERE target_user_id = ? AND stalker_user_id = ?");
	} else {
		$query = $db->prepare("INSERT INTO 185043iaibv1follows(target_user_id, stalker_user_id) VALUES (?, ?)");
	}
	$query->bind_param('dd', $user_id, $_SESSION['user_id']);
	$query->execute();
}

function get_tweet_comments($db, $tweet_id) {
	$query = $db->prepare(
			'SELECT 185043iaibv1comments.time, 185043iaibv1comments.user_id, 185043iaibv1comments.comment, 185043iaibv1users.username, 185043iaibv1users.name, 185043iaibv1users.image
				FROM 185043iaibv1comments
				JOIN 185043iaibv1users ON 185043iaibv1comments.user_id = 185043iaibv1users.id
				WHERE 185043iaibv1comments.tweet_id = ?
				ORDER BY 185043iaibv1comments.time DESC');
	$query->bind_param('d', $tweet_id);
	$query->execute();
	$result = $query->get_result();
	$comments = [];
	while($t = mysqli_fetch_assoc($result)) {
		array_push($comments, $t);
	}
	return $comments;
}

function is_following($db, $target_user_id) {
	$query = $db->prepare(
			'SELECT *
				FROM 185043iaibv1follows
				WHERE target_user_id = ? AND stalker_user_id = ?');
	$query->bind_param('dd', $target_user_id, $_SESSION['user_id']);
	$query->execute();
	$result = $query->get_result();
	if (!$result->num_rows) {
		return false;
	}
	return true;
}

function is_liked($db, $tweet_id) {
	$query = $db->prepare(
			'SELECT *
				FROM 185043iaibv1likes
				WHERE tweet_id = ? AND user_id = ?');
	$query->bind_param('dd', $tweet_id, $_SESSION['user_id']);
	$query->execute();
	$result = $query->get_result();
	if (!$result->num_rows) {
		return false;
	}
	return true;
}

function search($db, $str) {
	$results = [];
	$query = $db->prepare(
			'SELECT 185043iaibv1users.username, 185043iaibv1users.name, 185043iaibv1users.image, 185043iaibv1users.id 
								FROM 185043iaibv1users
						    WHERE lower(185043iaibv1users.username) LIKE ? 
						       OR lower(185043iaibv1users.name) LIKE ?');
	$query->bind_param('ss', $str, $str);
	$query->execute();
	$result = $query->get_result();
	while ($t = mysqli_fetch_row($result)) {
		array_push($results, $t);
	}
	return $results;
}
<?php

function dump($var) {
  echo '<pre>';
  print_r($var);
  echo '</pre>';
}

function sanit($var) {
  return (string)htmlspecialchars($var);
}

function format_time($s) {
	$time = strtotime($s);
	return date('G:i j M Y', $time);
}

<?php
require "settings.php";
try {
	$conn = new mysqli($dburl, $dbuser, $dbuserpass, "car_rental", $dbport);
	if (!$conn) {
		http_response_code(503);
		die();
	}
} catch (Exception $e) {
	http_response_code(503);
	die();
}

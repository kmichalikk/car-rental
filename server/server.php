<?php
session_start();
require_once "usermgmt.php";

header("Content-Type: application/json");
if (isset($_POST["target"])) {
	switch ($_POST["target"]) {
		case "register":
			if (isset($_POST["nick"]) && isset($_POST["password"]) && isset($_POST["email"]) && isset($_POST["type"])) {
				$registerStatus;
				switch ($_POST["type"]) {
					case "admin":
						if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
							$registerStatus = registerUser($_POST["nick"], $_POST["password"], $_POST["email"], ACCOUNT_ADMIN);
						} else {
							http_response_code(401);
							echo (json_encode(["ok" => false, "msg" => "you must be admin to add admins"]));
							return;
						}
						break;
					case "user":
						$registerStatus = registerUser($_POST["nick"], $_POST["password"], $_POST["email"], ACCOUNT_USER);
						break;
					default:
						http_response_code(400);
						echo (json_encode(["ok" => false, "msg" => "bad request: there is no such account type"]));
						return;
						break;
				}
				if ($registerStatus->status == "ok") {
					echo (json_encode(["ok" => true]));
				} else if ($registerStatus->status == "dbfail") {
					http_response_code(500);
					echo (json_encode(["ok" => false, "msg" => $registerStatus->additionalInfo]));
				} else if ($registerStatus->status == "exists") {
					http_response_code(409);
					echo (json_encode(["ok" => false, "msg" => "conflict: user exists"]));
				}
			} else {
				http_response_code(400);
				echo (json_encode(["ok" => false, "msg" => "bad request: nick, password or email not specified"]));
			}
			break;
		case "login":
			if (isset($_POST["nick"]) && isset($_POST["password"])) {
				$loginStatus = tryLogin($_POST["nick"], $_POST["password"]);
				if ($loginStatus->status == "ok") {
					$_SESSION["loggedInUser"] = $loginStatus->additionalInfo;
					echo (json_encode(["ok" => true]));
				} else if ($loginStatus->status == "loginfail") {
					http_response_code(401);
					echo (json_encode(["ok" => false, "msg" => "unauthorized: wrong password"]));
				} else if ($loginStatus->status == "dbfail") {
					http_response_code(500);
					echo (json_encode(["ok" => false, "msg" => $loginStatus->additionalInfo]));
				}
			} else {
				http_response_code(400);
				echo (json_encode(["ok" => false, "msg" => "bad request: nick or password not specified"]));
			}
			break;
		case "logout":
			unset($_SESSION["loggedInUser"]);
			echo (json_encode(["ok" => true]));
			break;
	}
} else {
	http_response_code(400);
	echo (json_encode(["ok" => false, "msg" => "bad request: no target specified"]));
}

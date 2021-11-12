<?php
session_start();
require_once "usermgmt.php";
require_once "reservemgmt.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
if (isset($_POST["target"])) {
	switch ($_POST["target"]) {
		case "hello":
			if (isset($_SESSION["loggedInUser"]))
				echo json_encode(["ok" => true, "nick" => $_SESSION["loggedInUser"]["nick"], "type" => $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN ? "admin" : "user"]);
			else
				echo json_encode(["ok" => false]);
			break;
		case "register":
			if (
				isset($_POST["nick"])
				&& false
				&& isset($_POST["password"])
				&& strlen($_POST["password"]) >= 8
				&& preg_match('/.*[a-z].*/', $_POST["password"])
				&& preg_match('/.*[0-9].*/', $_POST["password"])
				&& preg_match('/.*[A-Z].*/', $_POST["password"])
				&& preg_match('/.*[`~!@#$%^&*()\-_=+;:\'"\[\]\{\}\\|,\.<>\/?].*/', $_POST["password"])
				&& isset($_POST["email"])
				&& preg_match('/[\w\.-]+@[\w\.-]+\.[\w]+/', $_POST["email"])
				&& isset($_POST["type"])
			) {
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
				echo (json_encode(["ok" => false, "msg" => "bad request"]));
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
			logout();
			echo (json_encode(["ok" => true]));
			break;
		case "requestcar":
			if (isset($_SESSION["loggedInUser"])) {
				$hasAllParams = isset($_POST["carid"]) && intval($_POST["carid"]) > 0 && isset($_POST["startdatetime"]) && isset($_POST["enddatetime"]);
				$startdt = $hasAllParams ? date_create_from_format("Y-m-d H:i:s", $_POST["startdatetime"]) : false;
				$enddt = $hasAllParams ? date_create_from_format("Y-m-d H:i:s", $_POST["enddatetime"]) : false;
				if ($hasAllParams && $startdt && $enddt && $startdt > new DateTime() && $enddt > $startdt) {
					$requestStatus = tryRequestCar(intval($_POST["carid"]), $_SESSION["loggedInUser"]["id"], $_POST["startdatetime"], $_POST["enddatetime"]);
					if ($requestStatus->status == "ok") {
						echo (json_encode(["ok" => true]));
					} else if ($requestStatus->status == "nonexistent") {
						http_response_code(404);
						echo (json_encode(["ok" => false, "msg" => "no such car"]));
					} else if ($requestStatus->status == "booked") {
						http_response_code(403);
						echo (json_encode(["ok" => false, "msg" => "car already booked"]));
					} else if ($requestStatus->status == "dbfail") {
						http_response_code(500);
						echo (json_encode(["ok" => false, "msg" => $requestStatus->additionalInfo]));
					} else {
						http_response_code(500);
						echo (json_encode(["ok" => false, "msg" => "unknown error"]));
					}
				} else {
					http_response_code(400);
					echo (json_encode(["ok" => false, "msg" => "bad request"]));
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be logged in to request cars"]));
			}
			break;
		case "getcars":
			sleep(random_int(0, 5));
			$getResult = tryGetCars();
			if ($getResult->status == "ok") {
				echo (json_encode(["ok" => true, "data" => $getResult->additionalInfo]));
			} else if ($getResult->status == "dbfail") {
				http_response_code(500);
				echo (json_encode(["ok" => false, "msg" => $getResult->additionalInfo]));
			} else {
				http_response_code(500);
				echo (json_encode(["ok" => false, "msg" => "unknown error"]));
			}
			break;
		case "cardetails":
			if (isset($_POST["carid"]) && intval($_POST["carid"]) >= 0) {
				$getResult = tryGetDetails(intval($_POST["carid"]));
				if ($getResult->status == "ok") {
					echo (json_encode(["ok" => true, "data" => $getResult->additionalInfo]));
				} else if ($getResult->status == "nonexistent") {
					http_response_code(404);
					echo (json_encode(["ok" => false, "msg" => "no such car"]));
				} else if ($getResult->status == "dbfail") {
					http_response_code(500);
					echo (json_encode(["ok" => false, "msg" => $getResult->additionalInfo]));
				} else {
					http_response_code(500);
					echo (json_encode(["ok" => false, "msg" => "unknown error"]));
				}
			} else {
				http_response_code(400);
				echo (json_encode(["ok" => false, "msg" => "bad request"]));
			}
			break;
		case "getfilters":
			$filters = tryGetFilters();
			if ($filters->status == "dbfail") {
				http_response_code(500);
				echo (json_encode(["ok" => false, "msg" => $filters->additionalInfo]));
			} else {
				echo (json_encode(["ok" => true, "data" => $filters->additionalInfo]));
			}
			break;
		case "mycars":
			if (isset($_SESSION["loggedInUser"])) {
				$getResult = tryGetUserCars($_SESSION["loggedInUser"]["id"]);
				if ($getResult->status == "ok") {
					echo (json_encode(["ok" => true, "data" => $getResult->additionalInfo]));
				} else if ($getResult->status == "dbfail") {
					http_response_code(500);
					echo (json_encode(["ok" => false, "msg" => $getResult->additionalInfo]));
				} else {
					http_response_code(500);
					echo (json_encode(["ok" => false, "msg" => "unknown error"]));
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be logged in to request cars"]));
			}
			break;
		case "getrequests":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				$getRequests = tryGetRequests();
				if ($getRequests->status == "ok") {
					echo (json_encode($getRequests->additionalInfo));
				} else if ($getRequests->status == "dbfail") {
					http_response_code(500);
					echo (json_encode(["ok" => false, "msg" => $getRequests->additionalInfo]));
				} else {
					http_response_code(500);
					echo (json_encode(["ok" => false, "msg" => "unknown error"]));
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be logged as admin to accept requests"]));
			}
			break;
		case "acceptrequest":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				$hasAllParams = isset($_POST["reqid"]) && intval($_POST["reqid"]) > 0
					&& isset($_POST["startdatetime"]) && isset($_POST["enddatetime"]);
				$startdt = $hasAllParams ? date_create_from_format("Y-m-d H:i:s", $_POST["startdatetime"]) : false;
				$enddt = $hasAllParams ? date_create_from_format("Y-m-d H:i:s", $_POST["enddatetime"]) : false;
				if ($hasAllParams && $startdt && $enddt && $startdt > new DateTime() && $enddt > $startdt) {
					$acceptResult = tryAcceptRequest($_POST["reqid"], $_POST["startdatetime"], $_POST["enddatetime"]);
					if ($acceptResult->status == "ok") {
						echo (json_encode(["ok" => true]));
					} else if ($acceptResult->status == "nonexistent") {
						http_response_code(404);
						echo (json_encode(["ok" => false, "msg" => "no such request"]));
					} else if ($acceptResult->status == "dbfail") {
						http_response_code(500);
						echo (json_encode(["ok" => false, "msg" => $acceptResult->additionalInfo]));
					} else {
						http_response_code(500);
						echo (json_encode(["ok" => false, "msg" => "unknown error"]));
					}
				} else {
					http_response_code(400);
					echo (json_encode(["ok" => false, "msg" => "bad request"]));
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be logged as admin to accept requests"]));
			}
			break;
		default:
			http_response_code(400);
			echo (json_encode(["ok" => false, "msg" => "missing or wrong target"]));
	}
} else {
	http_response_code(400);
	echo (json_encode(["ok" => false, "msg" => "bad request: no target specified"]));
}

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
					echo (json_encode(["ok" => true, "type" => $loginStatus->additionalInfo["type"] == ACCOUNT_ADMIN ? "admin" : "user"]));
				} else if ($loginStatus->status == "loginfail") {
					http_response_code(401);
					echo (json_encode(["ok" => false, "msg" => $loginStatus->additionalInfo]));
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
		case "getservertime":
			$serverDateTime = getServerDateTime();
			if ($serverDateTime !== false)
				echo json_encode(["ok" => true, "time" => $serverDateTime->getTimestamp()]);
			else
				echo json_encode(["ok" => false]);
			break;
		case "updateservertime":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				if (isset($_POST["datetime"]) && date_create_from_format("Y-m-d H:i:s", $_POST["datetime"])) {
					$updateResult = tryUpdateTime(substr($_POST["datetime"], 0, -5) . "00:00");
					if ($updateResult->status == "ok") {
						echo json_encode(["ok" => true]);
					} else if ($updateResult->status == "dbfail") {
						http_response_code(500);
						echo json_encode(["ok" => false, "msg" => $updateResult->additionalInfo]);
					} else {
						http_response_code(500);
						echo json_encode(["ok" => false, "msg" => "unknown error"]);
					}
				} else {
					http_response_code(400);
					echo json_encode(["ok" => false, "msg" => "bad request"]);
				}
			} else {
				http_response_code(401);
				echo json_encode(["ok" => false, "msg" => "you must be admin to update time"]);
			}
			break;
		case "requestcar":
			$serverDateTime = getServerDateTime();
			if (isset($_SESSION["loggedInUser"]) && !isUserBlocked($_SESSION["loggedInUser"]["id"])) {
				$hasAllParams = isset($_POST["carid"]) && intval($_POST["carid"]) > 0 && isset($_POST["startdatetime"]) && isset($_POST["enddatetime"]);
				$startdt = $hasAllParams ? date_create_from_format("Y-m-d H:i:s", $_POST["startdatetime"]) : false;
				$enddt = $hasAllParams ? date_create_from_format("Y-m-d H:i:s", $_POST["enddatetime"]) : false;
				if ($hasAllParams && $startdt && $enddt && $startdt > $serverDateTime && $enddt > $startdt) {
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
			if (isset($_SESSION["loggedInUser"]) && !isUserBlocked($_SESSION["loggedInUser"]["id"])) {
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
					echo (json_encode(["ok" => true, "data" => $getRequests->additionalInfo]));
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
			$serverDateTime = getServerDateTime();
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				$hasAllParams = isset($_POST["reqid"]) && intval($_POST["reqid"]) >= 0
					&& isset($_POST["startdatetime"]) && isset($_POST["enddatetime"]);
				$startdt = $hasAllParams ? date_create_from_format("Y-m-d H:i:s", $_POST["startdatetime"]) : false;
				$enddt = $hasAllParams ? date_create_from_format("Y-m-d H:i:s", $_POST["enddatetime"]) : false;
				if ($hasAllParams && $startdt && $enddt && $startdt > $serverDateTime && $enddt > $startdt) {
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
		case "getusers":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				$users = tryGetUsers();
				if ($users->status == "ok") {
					echo json_encode(["ok" => true, "data" => $users->additionalInfo]);
				} else if ($users->status == "dbfail") {
					http_response_code(500);
					echo json_encode(["ok" => false, "msg" => $users->additionalInfo]);
				} else {
					http_response_code(500);
					echo json_encode(["ok" => false, "msg" => "unknown error"]);
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be admin to list users"]));
			}
			break;
		case "getlate":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				$lateResult = tryGetLateUsers();
				if ($lateResult->status == "ok") {
					echo json_encode(["ok" => true, "data" => $lateResult->additionalInfo]);
				} else if ($lateResult->status == "dbfail") {
					http_response_code(500);
					echo json_encode(["ok" => false, "msg" => $lateResult->additionalInfo]);
				} else {
					http_response_code(500);
					echo json_encode(["ok" => false, "msg" => "unknown error"]);
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be logged as admin to list late users"]));
			}
			break;
		case "returncar":
			if (isset($_POST["bookid"]) && ctype_digit($_POST["bookid"])) {
				if (isset($_SESSION["loggedInUser"])) {
					$ret;
					if ($_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
						$ret = tryReturnCar($_POST["bookid"]);
					} else if ($_SESSION["loggedInUser"]["type"] == ACCOUNT_USER) {
						$ret = tryReturnCarRestricted($_SESSION["loggedInUser"]["id"], $_POST["bookid"]);
					}
					switch ($ret->status) {
						case "ok":
							echo json_encode(["ok" => true]);
							break;
						case "dbfail":
							http_response_code(500);
							echo json_encode(["ok" => false, "msg" => $ret->additionalInfo]);
							break;
						case "nonexistent":
							http_response_code(404);
							echo json_encode(["ok" => false, "msg" => "nonexistent"]);
							break;
						default:
							http_response_code(500);
							echo json_encode(["ok" => false, "msg" => "unknown error"]);
							break;
					}
				} else {
					http_response_code(401);
					echo json_encode(["ok" => false, "msg" => "you must be logged in"]);
				}
			} else {
				http_response_code(400);
				echo json_encode(["ok" => false, "msg" => "bad request"]);
			}
			break;
		case "grantadmin":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				if (isset($_POST["userid"]) && ctype_digit($_POST["userid"]) == true) {
					$grantResult = tryGrantAdmin($_POST["userid"]);
					switch ($grantResult->status) {
						case "ok":
							echo (json_encode(["ok" => true]));
							break;
						case "alreadyadmin":
							http_response_code(400);
							echo (json_encode(["ok" => false, "msg" => "user already is admin"]));
							break;
						case "nonexistent":
							http_response_code(404);
							echo (json_encode(["ok" => false, "msg" => "user doesn't exist"]));
							break;
						case "dbfail":
							http_response_code(500);
							echo (json_encode(["ok" => false, "msg" => $grantResult->additionalInfo]));
							break;
						default:
							http_response_code(500);
							echo (json_encode(["ok" => false, "msg" => "unknown error"]));
							break;
					}
				} else {
					http_response_code(400);
					echo (json_encode(["ok" => false, "msg" => "bad request"]));
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be admin to grant admin access to users"]));
			}
			break;
		case "blockuser":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				if (isset($_POST["userid"]) && ctype_digit($_POST["userid"])) {
					$blockResult = tryBlockUser($_POST["userid"]);
					if ($blockResult->status == "ok") {
						echo json_encode(["ok" => true]);
					} else if ($blockResult->status == "dbfail") {
						http_response_code(500);
						echo json_encode(["ok" => false, "msg" => $blockResult->additionalInfo]);
					} else {
						http_response_code(500);
						echo json_encode(["ok" => false, "msg" => "unknown error"]);
					}
				} else {
					http_response_code(400);
					echo json_encode(["ok" => false, "msg" => "bad request"]);
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be admin to block users"]));
			}
			break;
		case "unblockuser":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				if (isset($_POST["userid"]) && ctype_digit($_POST["userid"])) {
					$unblockResult = tryUnblockUser($_POST["userid"]);
					if ($unblockResult->status == "ok") {
						echo json_encode(["ok" => true]);
					} else if ($unblockResult->status == "dbfail") {
						http_response_code(500);
						echo json_encode(["ok" => false, "msg" => $unblockResult->additionalInfo]);
					} else {
						http_response_code(500);
						echo json_encode(["ok" => false, "msg" => "unknown error"]);
					}
				} else {
					http_response_code(400);
					echo json_encode(["ok" => false, "msg" => "bad request"]);
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be admin to block users"]));
			}
			break;
		case "getblocked":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				$getResult = tryGetBlockedUsers();
				if ($getResult->status == "ok") {
					echo json_encode(["ok" => true, "data" => $getResult->additionalInfo]);
				} else if ($getResult->status == "dbfail") {
					http_response_code(500);
					echo json_encode(["ok" => false, "msg" => $getResult->additionalInfo]);
				} else {
					http_response_code(500);
					echo json_encode(["ok" => false, "msg" => "unknown error"]);
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be admin to get blocked users"]));
			}
			break;
		case "checkscheduler":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				$res = checkScheduler();
				if ($res->status == "ok") {
					echo json_encode(["ok" => true, "state" => $res->additionalInfo]);
				} else if ($res->status == "dbfail") {
					http_response_code(500);
					echo json_encode(["ok" => false, $res->additionalInfo]);
				} else {
					http_response_code(500);
					echo json_encode(["ok" => false, "unexpected error"]);
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be admin to change scheduler settings"]));
			}
			break;
		case "setscheduler":
			if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"]["type"] == ACCOUNT_ADMIN) {
				if (isset($_POST["on"]) && ctype_digit($_POST["on"])) {
					$res = $_POST["on"] == 0 ? schedulerOff() : schedulerOn();
					if ($res->status == "ok") {
						echo json_encode(["ok" => true]);
					} else if ($res->status == "dbfail") {
						http_response_code(500);
						echo json_encode(["ok" => false, $res->additionalInfo]);
					} else {
						http_response_code(500);
						echo json_encode(["ok" => false, "unexpected error"]);
					}
				} else {
					http_response_code(400);
					echo json_encode(["ok" => false, "bad request"]);
				}
			} else {
				http_response_code(401);
				echo (json_encode(["ok" => false, "msg" => "you must be admin to change scheduler settings"]));
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

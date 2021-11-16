<?php

require_once "connect.php";
require_once "utils.php";

const ACCOUNT_ADMIN = 0;
const ACCOUNT_USER = 1;

class User
{
	public string $nick;
	public int $type;
	function __construct($nick, $type)
	{
		$this->nick = $nick;
		$this->type = $type;
	}
}

function registerUser($username, $pass, $email, $accountType)
{
	global $conn;
	$query = $conn->query("SELECT count(*) FROM users WHERE nick = '$username'");
	if ($query->fetch_row()[0] == 0) {
		$insertStmt = $conn->prepare("INSERT INTO users VALUES(null, ?, ?, ?, ?, 0)");
		$hashedPass = password_hash($pass, PASSWORD_DEFAULT);
		$insertStmt->bind_param("sssi", $username, $hashedPass, $email, $accountType);
		if ($insertStmt->execute()) {
			return new ReturnState("ok", null);
		} else {
			return new ReturnState("dbfail", $conn->error);
		}
	} else {
		return new ReturnState("exists", null);
	}
}

function tryLogin($username, $pass)
{
	global $conn;
	$stmt = $conn->prepare("SELECT ID, nick, type, password, blocked FROM users WHERE users.nick = ?");
	$stmt->bind_param('s', $username);
	if ($stmt->execute()) {
		$result = $stmt->get_result();
		if ($result) {
			$data = $result->fetch_assoc();
			$passOK = $data !== null ? password_verify($pass, $data["password"]) : false;
			if ($passOK && $data["blocked"] == 0) {
				return new ReturnState("ok", ["id" => $data["ID"], "nick" => $data["nick"], "type" => $data["type"]]);
			} else if ($passOK && $data["blocked"] == 1) {
				return new ReturnState("loginfail", "blocked");
			} else {
				return new ReturnState("loginfail", "wrongpass");
			}
		} else {
			return new ReturnState("dbfail", $conn->error);
		}
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function logout()
{
	unset($_SESSION["loggedInUser"]);
}

function tryGetUsers()
{
	global $conn;
	$query = $conn->query("SELECT ID, nick, email FROM users WHERE type = 1");
	if ($query) {
		$data = $query->fetch_all();
		if ($data) {
			$parsed = [];
			foreach ($data as $row) {
				$parsed[] = [
					"id" => $row[0],
					"nick" => $row[1],
					"email" => $row[2]
				];
			}
			return new ReturnState("ok", $parsed);
		} else {
			return new ReturnState("dbfail", $conn->error);
		}
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function tryGrantAdmin($userid)
{
	global $conn;
	$query = $conn->query("SELECT type FROM users WHERE ID = $userid");
	if ($query) {
		$type = $query->fetch_row();
		if ($type !== null) {
			if ($type[0] == 1) {
				$grantQuery = $conn->query("UPDATE users SET type = 0 WHERE ID = $userid");
				if ($grantQuery) {
					return new ReturnState("ok", null);
				} else {
					return new ReturnState("dbfail", $conn->error);
				}
			} else {
				return new ReturnState("alreadyadmin", null);
			}
		} else {
			return new ReturnState("nonexistent", null);
		}
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function tryBlockUser($userid)
{
	global $conn;
	$query = $conn->query("BEGIN");
	if (!$query) {
		return new ReturnState("dbfail", $conn->error);
	}
	$query = $conn->query("UPDATE users SET blocked=1 WHERE ID=$userid");
	if (!$query) {
		$query = $conn->query("ROLLBACK");
		return new ReturnState("dbfail", $conn->error);
	}
	$query = $conn->query("DELETE FROM curr_booked WHERE userID=$userid");
	if (!$query) {
		$query = $conn->query("ROLLBACK");
		return new ReturnState("dbfail", $conn->error);
	}
	$query = $conn->query("DELETE FROM pending_requests WHERE userID=$userid");
	if (!$query) {
		$query = $conn->query("ROLLBACK");
		return new ReturnState("dbfail", $conn->error);
	}
	$query = $conn->query("COMMIT");
	return new ReturnState("ok", null);
}

function tryUnblockUser($userid)
{
	global $conn;
	$query = $conn->query("UPDATE users SET blocked=0 WHERE ID=$userid");
	if ($query) {
		if ($conn->affected_rows > 0) {
			return new ReturnState("ok", null);
		} else {
			return new ReturnState("nonexistent", null);
		}
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function tryGetBlockedUsers()
{
	global $conn;
	$query = $conn->query("SELECT ID, nick, email FROM users WHERE blocked=1");
	if ($query) {
		$parsed = [];
		foreach ($query->fetch_all() as $row) {
			$parsed[] = [
				"id" => $row[0],
				"nick" => $row[1],
				"email" => $row[2]
			];
		}
		return new ReturnState("ok", $parsed);
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function isUserBlocked($userid)
{
	global $conn;
	$query = $conn->query("SELECT blocked FROM users WHERE ID=$userid");
	if ($query && $query->num_rows > 0 && $query->fetch_row()[0] == 1)
		return true;
	else
		return false;
}

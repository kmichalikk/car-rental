<?php
require_once "connect.php";
class ReturnState
{
	public $status;
	public $additionalInfo;
	function __construct($status, $additionalInfo)
	{
		$this->status = $status;
		$this->additionalInfo = $additionalInfo;
	}
}

function getServerDateTime()
{
	global $conn;
	$query = $conn->query("SELECT time FROM curr_time");
	$dt = new DateTime($query->fetch_row()[0]);

	return $dt;
}

function tryUpdateTime($newtime)
{
	global $conn;
	$query = $conn->query("UPDATE curr_time SET time='$newtime'");
	if ($query) {
		return new ReturnState("ok", null);
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function checkScheduler()
{
	global $conn;
	$query = $conn->query("SHOW VARIABLES WHERE VARIABLE_NAME='event_scheduler'");
	if ($query) {
		return new ReturnState("ok", $query->fetch_row()[1] == 'ON');
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function schedulerOn()
{
	global $conn;
	$query = $conn->query("SET GLOBAL event_scheduler='ON'");
	if ($query) {
		return new ReturnState("ok", null);
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function schedulerOff()
{
	global $conn;
	$query = $conn->query("SET GLOBAL event_scheduler='OFF'");
	if ($query) {
		return new ReturnState("ok", null);
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

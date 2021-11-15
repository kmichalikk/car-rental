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

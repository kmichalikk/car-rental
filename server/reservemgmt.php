<?php
require_once "connect.php";
require_once "utils.php";

$mapQueriedIDs = function ($val) {
	return $val[0];
};

function tryRequestCar($carID, $userID, $borrowTime)
{
	global $conn, $mapQueriedIDs;
	$queryBooked = $conn->query("SELECT cars.ID FROM cars INNER JOIN curr_booked ON curr_booked.carID = cars.ID;");
	$queryAvail = $conn->query("SELECT cars.ID FROM cars");
	if ($queryBooked && $queryAvail) {
		$bookedCarsIDs = array_map($mapQueriedIDs, $queryBooked->fetch_all());
		$availCarsIDs = array_map($mapQueriedIDs, $queryAvail->fetch_all());
		if (array_search($carID, $availCarsIDs) !== false) {
			if (array_search($carID, $bookedCarsIDs) === false) {
				$stmt = $conn->prepare("INSERT INTO pending_requests VALUES(null, ?, ?, ?)");
				$stmt->bind_param("iii", $userID, $carID, $borrowTime);
				if ($stmt->execute())
					return new ReturnState("ok", null);
				else
					return new ReturnState("dbfail", $conn->error);
			} else {
				return new ReturnState("booked", null);
			}
		} else {
			return new ReturnState("nonexistent", null);
		}
	}
}

function tryGetCars()
{
	global $conn;
	$query = $conn->query("select cars.ID, curr_booked.userID as booked, pending_requests.userID as requested
	 from cars left join curr_booked on curr_booked.carID = cars.ID
	 left join pending_requests on pending_requests.carID = cars.ID;");
	if ($query) {
		$cars = [];
		$data = $query->fetch_all();
		foreach ($data as $row) {
			if (!isset($cars[$row[0]]))
				$cars[$row[0]] = ["booked" => false, "requestCount" => 0];
			if ($row[1] != NULL)
				$cars[$row[0]]["booked"] = true;
			if ($row[2] != NULL)
				$cars[$row[0]]["requestCount"]++;
		}
		$parsed = [];
		foreach (array_keys($cars) as $key) {
			$parsed[] = ["id" => $key, "booked" => $cars[$key]["booked"], "requestCount" => $cars[$key]["requestCount"]];
		}
		return new ReturnState("ok", $parsed);
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function tryGetUserCars($userID)
{
	global $conn;
	$query = $conn->query("select curr_booked.carID as booked, null as requested from curr_booked
	 inner join users on curr_booked.userID = users.ID where users.ID = $userID
	 UNION ALL
	 select null as booked, pending_requests.carID as requested from pending_requests
	 inner join users on pending_requests.userID = users.ID where users.ID = $userID;");
	if ($query) {
		$cars = array_map(function ($val) use ($userID) {
			// 0 - booked, 1 - requested
			if ($val[0] != NULL)
				// nie będzie w zapytaniu powtórek tego samego id dla booked i requests
				// ponieważ samochód booked nie moze być później requestowany
				return ["id" => $val[0], "booked" => true, "requested" => false];
			else
				return ["id" => $val[1], "booked" => false, "requested" => true];
		}, $query->fetch_all());
		return new ReturnState("ok", $cars);
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

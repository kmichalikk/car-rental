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
				if ($stmt && $stmt->execute())
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
	$query = $conn->query("select cars.ID, curr_booked.userID as booked, pending_requests.userID as requested,
	 cars.img_url as url
	 from cars left join curr_booked on curr_booked.carID = cars.ID
	 left join pending_requests on pending_requests.carID = cars.ID;");
	if ($query) {
		$cars = [];
		$data = $query->fetch_all();
		foreach ($data as $row) {
			if (!isset($cars[$row[0]]))
				$cars[$row[0]] = ["booked" => false, "requestCount" => 0, "url" => $row[3]];
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

function tryGetRequests()
{
	global $conn;
	$query = $conn->query("SELECT pending_requests.ID AS reqID, pending_requests.userID AS userID,
		users.nick AS userNick, pending_requests.carID AS carID, car_makes.name AS carMake, cars.model AS carModel,
		borrow_time FROM pending_requests INNER JOIN users ON pending_requests.userID = users.ID
		INNER JOIN cars ON pending_requests.carID = cars.ID INNER JOIN car_makes ON car_makes.ID = cars.makeID");
	if ($query) {
		$request = $query->fetch_assoc();
		$data = [];
		while ($request) {
			$data[] = $request;
			$request = $query->fetch_assoc();
		}
		return new ReturnState("ok", $data);
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function tryAcceptRequest($reqID, $borrowTime)
{
	global $conn;
	$queryReqs = $conn->query("SELECT pending_requests.userID, pending_requests.carID
			FROM pending_requests WHERE ID = $reqID");
	if ($queryReqs) {
		$res = $queryReqs->fetch_all();
		if (count($res) == 1) {
			$reqData = $res[0];
			$stmt = $conn->prepare("INSERT INTO curr_booked VALUES(null,?,?,?,?);");
			$endDate = date("Y-m-j", time() + $borrowTime);
			$stmt->bind_param("iisi", $reqData[0], $reqData[1], $endDate, $borrowTime);
			if ($stmt && $stmt->execute()) {
				return new ReturnState("ok", null);
			} else {
				return new ReturnState("dbfail", $conn->error);
			}
		} else {
			return new ReturnState("nonexistent", null);
		}
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

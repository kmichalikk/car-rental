<?php
require_once "connect.php";
require_once "utils.php";

$mapQueriedIDs = function ($val) {
	return $val[0];
};

function tryRequestCar($carID, $userID, $startdt, $enddt)
{
	global $conn, $mapQueriedIDs;
	$queryBooked = $conn->query("SELECT cars.ID FROM cars INNER JOIN curr_booked ON curr_booked.carID = cars.ID;");
	$queryAll = $conn->query("SELECT cars.ID FROM cars");
	if ($queryBooked && $queryAll) {
		$bookedCarsIDs = array_map($mapQueriedIDs, $queryBooked->fetch_all());
		$allCarsIDs = array_map($mapQueriedIDs, $queryAll->fetch_all());
		if (array_search($carID, $allCarsIDs) !== false) {
			if (array_search($carID, $bookedCarsIDs) === false) {
				$stmt = $conn->prepare("INSERT INTO pending_requests VALUES(null, ?, ?, ?, ?)");
				$stmt->bind_param("iiss", $userID, $carID, $startdt, $enddt);
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

function tryGetDetails($carID)
{
	$all = tryGetCars();
	if ($all->status == "ok") {
		$res = array_filter($all->additionalInfo, function ($val) use ($carID) {
			return $val["id"] == $carID;
		});
		if (count($res) > 0) {
			return new ReturnState("ok", array_values($res)[0]);
		} else {
			return new ReturnState("nonexistent", null);
		}
	} else {
		return $all;
	}
}

function tryGetCars()
{
	global $conn;
	$query = $conn->query("SELECT cars.ID,
	curr_booked.userID AS booked,
	pending_requests.userID AS requested,
	cars.img_url AS url,
	car_makes.name AS make,
	cars.model AS model,
	colors.name AS color,
	body_types.name AS body,
	cars.power AS power,
	drive_types.name AS drive,
	cars.price AS price
	FROM cars LEFT JOIN curr_booked ON curr_booked.carID = cars.ID
	LEFT JOIN pending_requests ON pending_requests.carID = cars.ID
	INNER JOIN car_makes ON cars.makeID = car_makes.ID
	INNER JOIN colors ON cars.color_id = colors.ID
	INNER JOIN body_types ON cars.body_id = body_types.ID
	INNER JOIN drive_types ON cars.drive_id = drive_types.ID;");
	if ($query) {
		$cars = [];
		$data = $query->fetch_all();
		foreach ($data as $row) {
			if (!isset($cars[$row[0]]))
				$cars[$row[0]] = [
					"id" => $row[0],
					"booked" => false,
					"requestCount" => 0,
					"url" => $row[3],
					"make" => $row[4],
					"model" => $row[5],
					"color" => $row[6],
					"body" => $row[7],
					"power" => $row[8],
					"drive" => $row[9],
					"price" => $row[10]
				];
			if ($row[1] != NULL)
				$cars[$row[0]]["booked"] = true;
			if ($row[2] != NULL)
				$cars[$row[0]]["requestCount"]++;
		}
		$parsed = [];
		foreach (array_keys($cars) as $key) {
			$parsed[] = $cars[$key];
		}
		return new ReturnState("ok", $parsed);
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function tryGetFilters()
{
	global $conn;
	$queryMakes = $conn->query("SELECT ID, name FROM car_makes");
	$queryColors = $conn->query("SELECT ID, name, hex FROM colors");
	$queryDrives = $conn->query("SELECT ID, name FROM drive_types");
	$queryCarBodies = $conn->query("SELECT ID, name FROM body_types");
	if ($queryMakes && $queryColors && $queryDrives && $queryCarBodies) {
		$makes = array_map(function ($val) {
			return ["id" => $val[0], "name" => $val[1]];
		}, $queryMakes->fetch_all());
		$colors = array_map(function ($val) {
			return ["id" => $val[0], "name" => $val[1], "hex" => $val[2]];
		}, $queryColors->fetch_all());
		$drives = array_map(function ($val) {
			return ["id" => $val[0], "name" => $val[1]];
		}, $queryDrives->fetch_all());
		$bodies = array_map(function ($val) {
			return ["id" => $val[0], "name" => $val[1]];
		}, $queryCarBodies->fetch_all());
		return new ReturnState("ok", ["makes" => $makes, "colors" => $colors, "drives" => $drives, "bodies" => $bodies]);
	} else {
		return new ReturnState("dbfail", $conn->error);
	}
}

function tryGetUserCars($userID)
{
	global $conn;
	$query = $conn->query("SELECT car_makes.name, cars.model,
		pending_requests.preferred_start, pending_requests.preferred_end,
		'1' AS requested, NULL AS booked FROM pending_requests
		INNER JOIN cars ON pending_requests.carID = cars.ID
		INNER JOIN car_makes ON cars.makeID = car_makes.ID
		WHERE pending_requests.userID=$userID
		UNION ALL
		SELECT car_makes.name, cars.model,
		curr_booked.borrow_start, curr_booked.borrow_end,
		NULL AS requested, '1' AS booked FROM curr_booked
		INNER JOIN cars ON curr_booked.carID = cars.ID
		INNER JOIN car_makes ON cars.makeID = car_makes.ID
		WHERE curr_booked.userID=$userID;");
	if ($query) {
		$cars = array_map(function ($val) {
			return [
				"make" => $val[0],
				"model" => $val[1],
				"start" => $val[2],
				"end" => $val[3],
				"requested" => $val[4] == '1',
				"booked" => $val[5] == '1'
			];
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
		preferred_start as preferredStart, preferred_end AS preferredEnd FROM pending_requests
		INNER JOIN users ON pending_requests.userID = users.ID
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

function tryAcceptRequest($reqID, $startdt, $enddt)
{
	global $conn;
	$queryReqs = $conn->query("SELECT pending_requests.userID, pending_requests.carID
			FROM pending_requests WHERE ID = $reqID");
	if ($queryReqs) {
		$res = $queryReqs->fetch_all();
		if (count($res) == 1) {
			$reqData = $res[0];
			$stmt = $conn->prepare("INSERT INTO curr_booked VALUES(null,?,?,?,?);");
			$stmt->bind_param("iiss", $reqData[0], $reqData[1], $startdt, $enddt);
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

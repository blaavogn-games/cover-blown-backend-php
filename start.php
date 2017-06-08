<?php
	require_once('db.php');

	$players = 4;
	$singleWeapons = 3;
	$singlePersons = 3;

	$CIHintCounter = array();
	$personRoles = array();

	function contains($arr, $v){
		for($i = 0; $i < count($arr); $i++){
			if($arr[$i] == $v){
				return true;
			}
		}
		return false;
	}

	function createDeck($values, $singles, $avoid){
		global $mode, $personRoles, $CIHintCounter;

		$tuples = array();

		for($i = 0; $i < count($values); $i++){
			if($i == $avoid){
				continue;
			}
			for($j = $i + 1; $j < count($values); $j++){
				if($j == $avoid){
					continue;
				}

				if(!array_key_exists($values[$i], $CIHintCounter)){
					$CIHintCounter[$values[$i]] = 0;
				}
				if(!array_key_exists($values[$j], $CIHintCounter)){
					$CIHintCounter[$values[$j]] = 0;
				}

				if((contains($personRoles, $values[$i]) && $CIHintCounter[$values[$i]] > 0) ||
					(contains($personRoles, $values[$j]) && $CIHintCounter[$values[$j]] > 0)
					){continue;}

				$CIHintCounter[$values[$i]]++;
				$CIHintCounter[$values[$j]]++;
				array_push($tuples, "'$values[$i]','$values[$j]'");
			}
		}
		for($i = 0; $i < count($values); $i++){
			for($j = 0; $j < $singles; $j++){
				if($i == $avoid or (contains($personRoles, $values[$i]) )){
					continue;
				}
				array_push($tuples, "'$values[$i]',''");
			}
		}
		return $tuples;
	}

	function insertDeck($values, $gameId, $type, $mysqli){
		for($i = 0; $i < count($values); $i++){
				$q = "insert into deck(info1, info2, type, game_id) values(".$values[$i].", $type, $gameId)";
				if(!$mysqli->query($q)){
					echo $values[$i]."<br>";
					echo $q."<br>";
					echo $mysqli->error;
					die();
				}
		}
	}

	$persons = array(
		"Crystal Cortez",
		"Theodore Huff",
		"Douglas Barnett",
		"Morris Dixon",
		"Amanda White",
		"Jackie Roberson",
		"Gene Hubbard",
		"Randy Anderson",
		"Lamar Lambert",
		"Patricia Woods"
	);

	$weapons = array(
		"Meat Grinder",
		"Meat Tenderiser",
		$mysqli->real_escape_string("Driver's Gloves"),
		$mysqli->real_escape_string("Butcher's Cleaver"),
		"Bolt Gun",
		"Katana",
		"Baseball Bat",
		"Garrotte",
		"Solvent"
	);

	//Drawing CI's and Murder
	shuffle($persons);
	shuffle($weapons);

	$draw = array_slice($persons, 0, 1, false);

	for($i = 0; $i < $players; $i++){
		$personRoles[$i] = $persons[$i];
	}

	//Drawing weapon and murder
	$murderId = rand(0, $players - 1);

	$wI = 0;

	$murderWeapon = $weapons[$wI];

	$weapons = array_values($weapons);
	$personsDeck = createDeck($persons, $singlePersons, $murderId);
	$weaponsDeck = createDeck($weapons, $singleWeapons, $wI);
	shuffle($personsDeck);
	shuffle($weaponsDeck);

	$date = new DateTime();
	$date->add(new DateInterval('PT4M'));
	$timeLimit = $date->getTimestamp();

	$mysqli->begin_transaction();
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

	for($i = 0; $i < 4; $i++){
		$mysqli->query("
			INSERT INTO player
			SET
				name = '$personRoles[$i]',
				IRLname = '',
				lastInfo = -1,
				discovered = 0,
				joined = 0
			");
	}

	$playerQuery = $mysqli->query("
		SELECT id
		FROM player
		ORDER BY id DESC
		LIMIT 0,4
		");

	$playerIds = array();
	$i = 0;
	while($row = $playerQuery->fetch_array()){
		$playerIds[$i++] = $row['id'];
	}

	$query = "
		INSERT INTO game
		SET
			murder 		= '".$mysqli->real_escape_string($personRoles[$murderId])."',
			weapon 		= '".$mysqli->real_escape_string($murderWeapon)."',
			murderId 	= $murderId,
			round 		= 0,
			timeLimit = $timeLimit,
			player0		= $playerIds[0],
			player1		= $playerIds[1],
			player2		= $playerIds[2],
			player3		= $playerIds[3]
	";
	$mysqli->query($query);

	$result = $mysqli->query("select MAX(id) as id from game");
	$gameId = $result->fetch_array(MYSQLI_ASSOC)["id"];

	$mysqli->query("
		UPDATE player
		SET
			game_id 	= $gameId
		WHERE
			id in ($playerIds[0],$playerIds[1],$playerIds[2],$playerIds[3])
	");

	insertDeck($personsDeck, $gameId, 0, $mysqli);
	insertDeck($weaponsDeck, $gameId, 1, $mysqli);

	for($i = 0; $i < 4; $i++){
		$msg = "";

		if($murderId == $i){
			$msg = "Hello friend, it is me ".$personRoles[$i].". I had to kill her or our deal was going to fail."
						."Do not let them know that I used ".$murderWeapon." or my cover will be blown. Unfortunately the ".$murderWeapon." is now in police custody.".$personRoles[$i]." out!";
		}else{
			$msg = "Bonjour, it is me ".$personRoles[$i].". I have successfully infiltrated the criminal underworld."
			      ."Do not accuse me or I will not be able to provide you with any information".$personRoles[$i]." over and out!";
		}

		$msg = $mysqli->real_escape_string($msg);

		if(!$mysqli->query("
			insert into msg(msg, player, type, game_id)
			values('$msg', ".$playerIds[3 - $i].", 1, $gameId)
		")){
			echo $mysqli->error;
			die();
		}
	}

	$mysqli->commit();
	$mysqli->close();

	$response = new stdClass;
	$response->gameId = $gameId;

	echo json_encode($response);
	die();
?>
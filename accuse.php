<?php
	require_once('db.php');
	require_once('JsonUnwrapper.php');

	class request{
		public $murder, $weapon, $game, $player;
	}

	$mysqli->begin_transaction();

	$request = JsonUnwrapper::FetchData(new request(), $mysqli);

	$game = 
		$mysqli->query("
			SELECT * 
			FROM game 
			WHERE id=$request->game
		")->fetch_array();
	
	$playerName = 
		$mysqli->query("
			SELECT IRLname 
			FROM player 
			WHERE id=$request->player
		")->fetch_array()["IRLname"];
	
	$msg = $playerName." has accused ".$request->murder." for committing the murder with ".$request->weapon.".";

	if($game["murder"] === $request->murder and $game["weapon"] === $request->weapon){
		$msg .= " It is correct!";		
	}else if($game["murder"] === $request->murder or $game["weapon"] === $request->weapon){
		$msg .= " It is not correct, but there is something fishy about the combination.";		
	}else{
		$msg .= " It is not correct!";		
	}
	
	if(!$mysqli->query("
		UPDATE player
		SET discovered = 1
		WHERE game_id = $request->game
		AND name = '$request->murder'
		")){
		echo $mysqli->error;
		die();
	}
			
	$mysqli->query("
		INSERT INTO msg(msg, type, player, game_id) 
				    VALUES('$msg', 0, 0, $request->game)
		");

	$nextRound = $game['round'] + 1;
	$date = new DateTime();
	$date->add(new DateInterval('PT3M'));
	$timeLimit = $date->getTimestamp();
	$mysqli->query("
			UPDATE game 
			SET 
				round = $nextRound, 
				timeLimit = $timeLimit
			WHERE id = $request->game");

	$mysqli->commit();
	$mysqli->close();
	echo "{ }";
	die();	
?>
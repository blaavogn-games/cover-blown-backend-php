<?php
	require_once('db.php');
	require_once('JsonUnwrapper.php');

	class request{
		public $type, $player, $game;
	}

	$mysqli->begin_transaction();
	$request = JsonUnwrapper::FetchData(new request(), $mysqli);

	$player = 
		$mysqli->query("
			SELECT 
				(
					SELECT round 
					FROM game
					where id = $request->game
				) as round,
				lastInfo,
				discovered
			FROM player 
			WHERE id = $request->player
		")->fetch_array();

	if($player["lastInfo"] >= $player["round"]){ //Check call is allowed
		die(); 
	}

	$result = 
		$mysqli->query("
			SELECT * 
			FROM deck 
			WHERE game_id=$request->game 
			AND type=$request->type limit 1
		");
	$hint = $result->fetch_array();
	$mysqli->query("delete from deck where id = ".$hint['id']);

	$msg = "";
	if($player["discovered"]){ 
		$msg = "Hey friend. Ever since you and your dumb friends blew my cover by accusing me of the murder, I have had to hide downtown. I have no information for you. Hurry up and solve the case so I can get back to work.";
	}elseif($request->type == 0){ //Person
		if($hint['info2'] !== ""){
			switch(rand(0,2)){
				case 0:
					$msg = "Hey friend. I’ve been busting my ass off to find some new information for you and finally it has paid off. I heard solid rumors that ".$hint['info1']." was paying the family a visit on the night of the murder. Furthermore surveillance footage from the airport shows that ".$hint['info2']." was boarding a flight on the day of the murder. Hence both ".$hint['info1']." and ".$hint['info2']." have solid alibis.";
					break;
				case 1:
					$msg = "Hello sir, i’ve got some interesting news about two of your suspects. A private investigator I snuck up told me that he was on a stakeout on the night of the murder. He saw ".$hint['info1']." and ".$hint['info2']." entering a cheap motel room together. I will leave out the salacious details of their meeting, bottom line is that both ".$hint['info1']." and ".$hint['info2']." have a solid alibi.";
					break;
				case 2:
					$msg = "Yo bossman, news from the underworld coming your way! Both ".$hint['info1']." and ".$hint['info2']." has waterproof alibis. For the sake of my own security I cannot give you any details. Just trust me on this one. ".$hint['info1']." and ".$hint['info2']." was definitely not involved in the murder.";
					break;
			}
		}else{
			switch(rand(0,2)){
				case 0:
					$msg = "Hey friend. I’ve been busting my ass off to find some new information for you and finally it has paid off. I heard solid rumors that ".$hint['info1']." was paying the family a visit on the night of the murder. Hence ".$hint['info1']." has a solid alibi.";
					break;
				case 1:
					$msg = "Hello sir, i’ve discovered something about one of your suspects. ". $hint['info1']." was in Michigan the morning after the murder and can therefore not have committed it. It is a waste of time to look for more dirt on ".$hint['info1'].".";
					break;
				case 2:
					$msg = "Yo bossman, news from the underworld coming your way! ".$hint['info1']." has a waterproof alibi. For the sake of my own security I cannot give you any details. Just trust me on this one. ".$hint['info1']." was definitely not involved in the murder.";
					break;	
			}
		}
	}else{ //Weapon
		if($hint['info2'] !== ""){
			switch(rand(0,2)){
				case 0:
					$msg = "What’s up cop! The ".$hint['info1']." and the ".$hint['info2']." you’ve been suspicious about was not involved in the murder. Forensics show no proof of human DNA or any other suspicious clues. This means that you do not have to be concerned about the ".$hint['info1']." and the ".$hint['info2']." anymore.";
					break;
				case 1:
					$msg = "Sir! I just heard some interesting news from forensics. The ".$hint['info1']." and the ".$hint['info2']." you have had under suspicion in relation to the case you are working are clean. Some nutcase in administration mixed some index numbers up and both the ".$hint['info1']." and the ".$hint['info2']." were accidentally linked to your case.";
					break;
				case 2:
					$msg = "Hello my friend! I have some interesting information in relation to a solid rumour murmuring in the underworld. Apparently both the ".$hint['info1']." and the ".$hint['info2']." was carefully planted as evidence in your case material. In reality both the ".$hint['info1']." and the ".$hint['info2']." are clean. My best bet is that one of your colleagues is trying to cover up the murder.";
					break;
			}
		}else{
			switch(rand(0,2)){
				case 0:
					$msg = "What’s up cop! The ".$hint['info1']." you’ve been suspicious about was not involved in the murder. Forensics show no proof of human DNA or any other suspicious clues. This means that you do not have to be concerned about the ".$hint['info1']." anymore.";
					break;
				case 1:
					$msg = "Sir! I just heard some interesting news from forensics. The ".$hint['info1']." you have had under suspicion in relation to the case you are working is clean. Some nutcase in administration mixed some index numbers up and the ".$hint['info1']." was accidentally linked to your case.";
					break;
				case 2:
					$msg = "Hello my friend! I have some interesting information in relation to a solid rumour murmuring in the underworld. Apparently the ".$hint['info1']." was carefully planted as evidence in your case material. The ".$hint['info1']." is clean. My best bet is that one of your colleagues is trying to cover up the murder.";
					break;
			}
		}
	}

	$msg = $mysqli->real_escape_string($msg);

	$mysqli->query("insert into msg(msg, type, player, game_id) values('$msg', 1, $request->player, $request->game)");
	
	$mysqli->query("
		update player
		set
			lastInfo = $player[round]
		where id = $request->player
	");

	$mysqli->commit();
	$mysqli->close();

	echo "{}";
	die();	
?>
<?php
  require_once('db.php');
  require_once('JsonUnwrapper.php');

  class response{
    public $gameId, $timeLeft, $round, $started;
	
		public $players = array();
    public $lastInfo, $msgs = array();
  }

  class msg{
    public $id, $msg, $type;

    function __construct($id, $msg, $type) {
      $this->id = $id;
      $this->msg = $msg;
      $this->type = $type;
    }
  }

  class request{
    public $game, $player;
  }

  $mysqli->begin_transaction();
  $request = JsonUnwrapper::FetchData(new request(), $mysqli);

  $query = "
    SELECT
      timeLimit,
      round,
      lastInfo,
      NOT EXISTS( SELECT 1 FROM player WHERE joined = 0 and game_id = $request->game ) as started
    FROM game, player
    WHERE
      game.id = $request->game AND
      player.id = $request->player";

  $mysqlResult =  $mysqli->query($query);
  $row = $mysqlResult->fetch_array();

  $date = new DateTime();

  $response           = new response();
  $response->timeLeft = $row['timeLimit'] - $date->getTimestamp();
  $response->round    = $row['round'];
  $response->started  = $row['started'];
  $response->lastInfo = $row['lastInfo'];
  $response->gameId   = $request->game;

  if($response->timeLeft < 0){
    $nextRound = $row['round'] + 1;
    $date->add(new DateInterval('PT3M'));
    $timeLimit = $date->getTimestamp();
    $mysqli->query("
      UPDATE game
      SET
        round = $nextRound,
        timeLimit = $timeLimit
      WHERE id = $request->game");
    $response->timeLeft = 60;
    $response->round = $nextRound;
    //$response->usedHint = false;
  }

  $mysqlResult = $mysqli->query("select * from msg where game_id = $request->game and (player = $request->player or player = 0)");

  while($row = $mysqlResult->fetch_array()){
    array_push($response->msgs, new msg($row["id"], $row["msg"], $row["type"]));
  }

  $playerResult = $mysqli->query("select IRLname from player where game_id = $request->game order by id asc");

  while($row = $playerResult->fetch_array()){
    array_push($response->players, $row["IRLname"]);
  }

  $mysqli->commit();
  $mysqli->close();
  echo json_encode($response);
  die();
?>

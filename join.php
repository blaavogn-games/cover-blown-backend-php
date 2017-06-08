<?php
  require_once('db.php');
  require_once('JsonUnwrapper.php');

  class request{
    public $game, $name;
  }
  
  class response{
    public $player;

    function __construct($player) {
      $this->player = $player;
    }
  }

  $mysqli->begin_transaction();
  $request = JsonUnwrapper::FetchData(new request(), $mysqli);

  $joinId = 
    $mysqli->
    query("
      SELECT MIN(id) as joinId
      FROM player
      WHERE 
        game_id = $request->game AND
        joined = 0
    ")->
    fetch_array()[0];

  $startCheck = $mysqli->query("
    SELECT 1 
    FROM player 
    WHERE 
      joined = 0 and 
      game_id = $request->game
  ");

  $date = new DateTime();
  $date->add(new DateInterval('PT3M'));
  $timeLimit = $date->getTimestamp();

  if($startCheck->num_rows == 1){
    
    if(!$mysqli->query("
      UPDATE game
      SET round = 1,
        timeLimit = $timeLimit
      where id = $request->game
    ")){
      echo $mysqli->error;
      die();
    }
  }

  $mysqli->query("
    UPDATE player
    SET 
      IRLname = '$request->name',
      joined = 1
    where id = $joinId
  ");

  $mysqli->commit();
  $mysqli->close();
  echo json_encode(new response($joinId)); 
?>
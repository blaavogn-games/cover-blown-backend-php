<?php
  //$mysqli = new mysqli('localhost','root','', "gd_proto");
  // $mysqli = new mysqli('sql','root','NkW39NkW', "cover_blown");
  $mysqli = new mysqli('localhost','root','', "gd_proto");
	
  $mysqli = new mysqli("blaavogn.dk.mysql", "blaavogn_dk", "6ER7sCve", "blaavogn_dk");

	if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
	}

?>
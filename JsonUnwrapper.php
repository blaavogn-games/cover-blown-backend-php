<?php
	class JsonUnwrapper {
		public static function FetchData($datamodel, $mysqli){
			$input = file_get_contents('php://input');
			$jsonObj = json_decode($input);	

			if($jsonObj === NULL){
			    echo "The received object can't be JSON-decoded. \n";
			    echo "Recieved: \n";
			    echo $input;
			    http_response_code(400);
			    die();
			}

	    $jsonObjVars  = get_object_vars($jsonObj);
	    $modelVars    = get_object_vars($datamodel);

	    foreach($modelVars as $key => $value){
	      if(!isset($jsonObjVars[$key])){
	      	echo "The received JSON-object does not have the property: ".$key;
	        http_response_code(400);
	        die();
	      }
	      $datamodel->$key = $mysqli->real_escape_string($jsonObjVars[$key]);
	    } 
	    return $datamodel;
		}
	}
?> 

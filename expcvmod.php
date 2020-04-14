<?php

if (substr(php_sapi_name(), 0, 3 ) != "cli" && (substr(php_sapi_name(), 0, 3 ) != "cgi" )) {
    die("ALGOMA TOOLKIT GENERATE FILTER must runs from console");
}

error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ERROR);
ini_set("display_errors", 1);


// INPUT: user, password, dbSrc, dbDst


$user  = $argv[1];
$pass  = $argv[2];
$dbSrc  = $argv[3];
$dbDst  = $argv[4];
$module  = $argv[5];


if (count($argv) != 6){
    die("Wrong number of arguments \n "
            . "USAGE: php {$argv[0]} username password dbSrc dbDst module \n "
	);
}


$dbSrcConn = new mysqli('localhost', $user, $pass, $dbSrc);
// Check connection
if ($dbSrcConn->connect_error) {
    die("Connection failed: " . $dbSrcConn->connect_error);
}
global $dbSrcConn;


$dbDstConn = new mysqli('localhost', $user, $pass, $dbSrc);
// Check connection
if ($dbDstConn->connect_error) {
    die("Connection failed: " . $dbDstConn->connect_error);
}
$result = $dbSrcConn->query("SELECT id+1 as id FROM vtiger_customview_seq LIMIT 0,1");
$row = $result->fetch_assoc();
$cvidTo = $row['id'];

	

// Carico tutti i report per quella determinata cartella
$query = "SELECT cvid FROM vtiger_customview WHERE viewname NOT IN ('All') AND entitytype = '{$module}'";
$result = $dbSrcConn->query($query);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $customViews[] = $row['cvid'];
    }
} else {
    die("Nessun report in questa cartella");
}



$tables = array ('vtiger_customview', 'vtiger_cvadvfilter', 'vtiger_cvadvfilter_grouping', 'vtiger_cvcolumnlist', 'vtiger_cvstdfilter');


foreach($customViews as $cvidfr){
	
	$query  = "-- --------{$module} CVID: {$cvidfr}--------- --";
	$query .= PHP_EOL;
	
	
	
	foreach ($tables as $table){
		$query .= makeRecoverySQL($table, $cvidfr, $cvidTo);
	}
	 echo $query;
	$cvidTo = $cvidTo + 1;
	file_put_contents($module.'_customViews.sql', $query.PHP_EOL, FILE_APPEND);
}


file_put_contents($module.'_customViews.sql', "UPDATE vtiger_customview_seq SET id = id+1".PHP_EOL, FILE_APPEND);



function makeRecoverySQL($table,$cvidfrom,$cvidto)
{
    // get the record          
	global $dbSrcConn;
	
	
    $sql = "SELECT * FROM " . $table . " WHERE cvid = {$cvidfrom}";
	
	$result = $dbSrcConn->query($sql);
	if(!($result)){
		die($sql);
	}

	
	if ($result->num_rows > 0) { 

		// echo("RIGHE CI SONO");
		$insertSQL = '-- '. $table . '['.$result->num_rows .' rows] ' .PHP_EOL;
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$insertSQL .= "INSERT INTO " . $table . " SET ";
			foreach ($row as $field => $value) {
				if($field == 'cvid'){
					$value = $cvidto;
				}
				$insertSQL .= " " . $field . " = '" . $value . "', ";
			}
			$insertSQL = trim($insertSQL, ", ");
			$insertSQL .= ';'.PHP_EOL;
		}
	} else{
		echo ("$table NO ROW");
	}
	
    return $insertSQL.PHP_EOL;
}
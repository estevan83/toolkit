<?php
// Nessun timeout per il cron
set_time_limit(0);

if (substr(php_sapi_name(), 0, 3 ) != "cli" && (substr(php_sapi_name(), 0, 3 ) != "cgi" )) {
    die("ALGOMA MASSIVE IMPORT TOOLKIT must runs from console");
}

error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ERROR);
ini_set("display_errors", 1);



$host  = 'localhost';
$user  = 'arcoplex';
$pass  = 'vt1g3r++CRM';

$dbsrc = 'arcoplex_vtig486';
$dbdst = 'arcoplex_staging';


// Create connection
$conn = new mysqli($host, $user, $pass);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "select routine_name, routine_type, routine_schema, routine_definition from information_schema.routines where routine_schema = '".$dbsrc."';";
$result = $conn->query($sql);
unlink ('QUERYGENERATE.txt');
if ($result->num_rows > 0) {
	
    // output data of each row
    while($row = $result->fetch_assoc()) {
        // echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
		$type = $row['routine_type'];
		$name = $row['routine_name'];
		$procedure = $row['routine_definition'];
		$query = "DROP $type if exisxt $dbdst.$name;".PHP_EOL;
		$query .= "DELIMITER $$".PHP_EOL;
		$query .= "CREATE $type $dbdst.$name(";
		

		$params = "select * FROM information_schema.PARAMETERS WHERE specific_name = '".$name."';";
		$res = $conn->query($params);

		if ($res->num_rows > 0) {
			
			while($rw = $res->fetch_assoc()) {
				// die (print_r($rw,true));
				$inout = $rw['PARAMETER_MODE'];
				$key = $rw['PARAMETER_NAME'];
				$type = $rw['DATA_TYPE'];
				$lenght = $rw['CHARACTER_MAXIMUM_LENGTH'];
				$query .= $inout . ' ' . $key . ' ' . $type . ' (' . $lenght . '),'; 
				$rws[] = $rw;
			}
			$query = substr($query, 0, strlen($query)-1);
        
		}
		$query .= ')'.PHP_EOL;
		$query .= $procedure;
		$query .= '$$'.PHP_EOL;
		// die($query);
		//$row['params'] = $rws;
		// $query = print_r($row,true);
	//	file_put_contents('QUERYGENERATE.txt', $query .PHP_EOL, FILE_APPEND);


    }
} 
else{
	die("no rows");
}

$sql = "SELECT REPLACE(CONCAT('DROP VIEW IF EXISTS ', 'arcoplex_staging.', TABLE_NAME, ';\n CREATE VIEW ', 'arcoplex_staging.', TABLE_NAME,' AS ', view_definition) , 'arcoplex_vtig486', 'arcoplex_staging') as vista FROM information_schema.views ";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
	
	while($rw = $res->fetch_assoc()) {
		file_put_contents('QUERYGENERATE.txt', $rw['vista'] .PHP_EOL, FILE_APPEND);
	}
	
}


/*
foreach ($procedures as $procedure){
	$sql = 'SHOW CREATE PROCEDURE '.$procedure;
	
	// die ($sql. '                  ');
	$result = $conn->query($sql);
	if (!$result){
		die("error DC");
	}
	$row = $result->fetch_assoc();
	die (print_r($row,true));
	$tmp = $row['create procedure'];
	die($tmp);
}

*/





?>
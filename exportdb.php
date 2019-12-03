<?php





// Nessun timeout per il cron
set_time_limit(0);

if (substr(php_sapi_name(), 0, 3 ) != "cli" && (substr(php_sapi_name(), 0, 3 ) != "cgi" )) {
    die("ALGOMA MASSIVE IMPORT TOOLKIT must runs from console");
}

error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ERROR);
ini_set("display_errors", 1);

// INPUT host, user, password, porta, dbsrc, dbdst
// output  dbdst.view.sql
// output  dbdst.routines.sql
// output  dbdst.events.sql ----> TBD tadella forse events



//  php importProcedure.php localhost arcoplex+CRM arcoplex_vtig486 arcoplex_staging
$host  = $argv[1];
$user  = $argv[2];
$pass  = $argv[3];

$dbsrc = $argv[4];
$dbdst = $argv[5];

if (count($argv) != 6){
    die("wrong number of arguments \n "
            . "USAGE: php exportdb.php host user password dbSource dbDestination \n ");
}


unlink ($dbdst.'.routines.sql');
unlink ($dbdst.'.view.sql');
unlink ($dbdst.'.events.sql');
// Create connection
$conn = new mysqli($host, $user, $pass);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "select routine_name, routine_type, routine_schema, routine_definition from information_schema.routines where routine_schema = '".$dbsrc."';";
$result = $conn->query($sql);
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
		file_put_contents($dbdst.'.routines.sql', $query .PHP_EOL, FILE_APPEND);


    }
} 
else{
	die("no rows");
}

$sql = "SELECT REPLACE(CONCAT('DROP VIEW IF EXISTS ', '$dbdst.', TABLE_NAME, ';\n CREATE VIEW ', '$dbdst.', TABLE_NAME,' AS ', view_definition, ';') , '$dbsrc', '$dbdst') as vista FROM information_schema.views ";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
	
	while($rw = $res->fetch_assoc()) {
		file_put_contents($dbdst.'.view.sql', $rw['vista'] .PHP_EOL, FILE_APPEND);
	}
	
}


$sql = "SELECT CONCAT('DROP EVENT if EXISTS ', '$dbdst.',event_name, ';\nCREATE EVENT ','$dbdst.',event_name, '\nON SCHEDULE EVERY ',interval_value,' ',interval_field,'\n DO \n',event_definition,';') as event FROM information_schema.events";
$res = $conn->query($sql);
if ($res->num_rows > 0) {
	while($rw = $res->fetch_assoc()) {
		file_put_contents($dbdst.'.events.sql', $rw['event'] .PHP_EOL, FILE_APPEND);
	}
}

?>
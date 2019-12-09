<?php

// Nessun timeout per il cron
set_time_limit(0);

if (substr(php_sapi_name(), 0, 3 ) != "cli" && (substr(php_sapi_name(), 0, 3 ) != "cgi" )) {
    die("ALGOMA DV EXPORT (views procedure and events) must runs from console");
}

error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ERROR);
ini_set("display_errors", 1);

$drop = true; // se sono false ci metti davanti il commento
$create = true; // se vale false ci metti il commento

// INPUT host, user, password, porta, dbsrc, dbdst
// output  dbdst.view.sql
// output  dbdst.routines.sql
// output  dbdst.events.sql ----> TBD tadella forse events

//   php exportdb.php localhost arcoplex vt1g3r++CRM arcoplex_vtig486 arcoplex_staging

$host  = $argv[1];
$user  = $argv[2];
$pass  = $argv[3];

$dbsrc = $argv[4];
$dbdst = $argv[5];

if (count($argv) != 6){
    die("Wrong number of arguments \n "
            . "USAGE: php ". $argv[0] . " host user password dbSource dbDestination \n "
			. "mysql -h host -u user -p'password' database \n "
			. "source database.[views|routines|events].sql \n"
	);
}


@unlink ($dbdst.'.routines.sql');
@unlink ($dbdst.'.views.sql');
@unlink ($dbdst.'.events.sql');
// Create connection
$conn = new mysqli($host, $user, $pass);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "select routine_name, routine_type, routine_schema, routine_definition from information_schema.routines where routine_schema = '".$dbsrc."' ;";
// and (routine_name = 'html_UnEncode' or routine_name ='update_synchro_maps')
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	
    // output data of each row
    while($row = $result->fetch_assoc()) {
        // echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
		$type = $row['routine_type'];
		$name = $row['routine_name'];
		$procedure = $row['routine_definition'];

		

		$query = '';
		/*if (!$drop && !$create){
			$query .= '/*';
		}*/
		if ($create){
			$query .= "DELIMITER $$".PHP_EOL;
		}
		
		if ($drop){
			$query .= "DROP $type if exists $dbdst.$name;".PHP_EOL;
		}

		
		if ($create){
		
			$query .= "CREATE $type $dbdst.$name(";
			

			$params = "select * FROM information_schema.PARAMETERS WHERE specific_name = '".$name."' and SPECIFIC_SCHEMA = '".$dbsrc."';";
			$res = $conn->query($params);

			if ($res->num_rows > 0) {
				
				while($rw = $res->fetch_assoc()) {
					
					$inout = $rw['PARAMETER_MODE'];
					$key = $rw['PARAMETER_NAME'];
					$datatype = $rw['DATA_TYPE'];
					$lenght = $rw['CHARACTER_MAXIMUM_LENGTH'];
					if(!empty($inout)){
					$query .= /*$inout . ' ' . */$key . ' ' . $datatype ;
						if (is_numeric($lenght)){
							$query .='('. $lenght. ')' ; 
						}
					$query .=', ';
					}
					else{
						$return = 'returns ' . $datatype.' ';
						if (is_numeric($lenght)){
							$return .='('. $lenght. ')' ; 
						}
					}
					$rws[] = $rw;
				}
				$query = substr($query, 0, strlen($query)-2);
			
			}
			$query .= ') ';
			if ($type == 'FUNCTION'){
				$query .=$return;
			}
			$query .= PHP_EOL;
			$query .= $procedure;
			$query .= '$$'.PHP_EOL;
		
		}
	/*	if (!$create){
			$query .= '';
		}*/
		// die($query);
		//$row['params'] = $rws;
		// $query = print_r($row,true);
		file_put_contents($dbdst.'.routines.sql', $query .PHP_EOL, FILE_APPEND);


    }
} 
else{
	die("no rows");
}

$sql = "SELECT REPLACE(CONCAT('DROP VIEW IF EXISTS ', '$dbdst.', TABLE_NAME, ';\n!_!_!CREATE VIEW ', '$dbdst.', TABLE_NAME,' AS ', view_definition, ';') , '$dbsrc', '$dbdst') as vista FROM information_schema.views where TABLE_SCHEMA ='".$dbsrc."'";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
	
	while($rw = $res->fetch_assoc()) {
		$comment = explode('!_!_!', $rw['vista'] );
		if (!$drop){
			$comment[0] = '-- '. $comment[0];
		}
		
		if (!$create){
			
			$comment[1] = '-- '. $comment[1];
		}
		$vista = $comment[0] .$comment[1];
		file_put_contents($dbdst.'.views.sql', $vista .PHP_EOL, FILE_APPEND);
	}
	
}


$sql = "SELECT CONCAT('DROP EVENT if EXISTS ', '$dbdst.',event_name, ';\n!_!_!CREATE EVENT ','$dbdst.',event_name, '\nON SCHEDULE EVERY ',interval_value,' ',interval_field,'\n DO \n',event_definition,';') as event FROM information_schema.events";
$res = $conn->query($sql);
if ($res->num_rows > 0) {
	while($rw = $res->fetch_assoc()) {
		$comment = explode('!_!_!', $rw['event'] );
		if (!$drop){
			$comment[0] = '-- '. $comment[0];
		}
		
		if (!$create){
			$comment[1] = '-- '. $comment[1];
		}
		$event = $comment[0].$comment[1];
		file_put_contents($dbdst.'.events.sql',$event.PHP_EOL, FILE_APPEND);
	}
}

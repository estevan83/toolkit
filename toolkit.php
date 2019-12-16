<?php

$r1 = getcwd();
chdir('..');
$r2 = getcwd();


$root = str_replace($r2.'/','',$r1);



include_once 'config.inc.php' ;
include_once 'vtlib/Vtiger/Module.php';
include_once 'modules/Users/Users.php';
include_once 'modules/Emails/mail.php';
include_once 'include/utils/utils.php';

global $adb;
// Nessun timeout per il cron
set_time_limit(0);

if (substr(php_sapi_name(), 0, 3 ) != "cli" /*&& (substr(php_sapi_name(), 0, 3 ) != "cgi" )*/) {
    die("ALGOMA TOOLKIT must runs from console");
}


echo ("WELCOME TO ALGOMA TOOLKIT".PHP_EOL);
echo ("PER USCIRE PREMI CNTR+C".PHP_EOL);
echo ("SELEZIONA COSA VUOI FARE".PHP_EOL);



$a = new toolKit($dbconfig,$adb, $root);
$a->selectValue();


class toolKit{
	protected $selectedOption;
	protected $dbconfig;
	protected $adb;
	
	protected $root;
	
	
	function __construct($dbconfig , $adb , $root){
		$this->dbconfig = $dbconfig;
		$this->adb = $adb;
		$this->root = $root;
	}
	
	
	protected static function option(){
		echo ("Digita 1 per effetturare il Backup del database ".PHP_EOL);
		echo ("Digita 2 per effetturare il salvataggio dei filtri ".PHP_EOL);
		echo ("Digita 3 per effetturare il salvataggio dei workflow ".PHP_EOL);
		echo ("Digita 4 per effetturare il salvataggio delle routine (function e procedure) ".PHP_EOL);
		echo ("Digita 5 per effetturare il salvataggio delle viste ".PHP_EOL);
		echo ("Digita 6 per effetturare il salvataggio degli eventi ".PHP_EOL);
		echo ("Digita 7 per muovere o rinominare un campo ".PHP_EOL);
		echo ("Digita 8 per resettare il cron ".PHP_EOL);
		echo ("Digita 9 per eseguire del codice SQL ".PHP_EOL);
		echo ("Digita 10 per eliminare tutte le tabelle del DB ".PHP_EOL);
		
		echo (PHP_EOL);
		echo ("Digita cntr + c per uscire ".PHP_EOL);
		return;
	}
	
	
	protected function endFunction(){
		echo PHP_EOL;
		echo PHP_EOL;
		echo PHP_EOL;
		echo ("Selezionare un altra opzione oppure premere cntr+c per uscire".PHP_EOL);
		$this->selectValue();
	}
	
	public function selectValue(){
		toolKit::option();
		$this->selectedOption = trim(fgets(STDIN));
		$this->mainFunction();
	}
	
	protected function mainFunction(){
		$option = $this->selectedOption;
		unset($this->selectedOption);
		switch (intval($option)) {
			case 1 : 	
						$this->databaseBackup();
						break;
			case 2 : 	
						$this->generateFilter();
						break;
			case 3 : 	
						$this->generateWorkflow(); 
						break;
			case 4 : 	
						$this->importRoutine(); 
						break;
			case 5 : 	
						$this->importViste(); 
						break;
			case 6 : 	
									$this->importEvent(); 
									break;
			case 7 : 	
									$this->mvField(); 
									break;
			case 8:
						$this->resetCron();
						break;
			case 9:
						$this->runSqlCode();
						break;
			case 10:
						$this->generateTruncate();
						break;
						
						
						
						
						
			default :
						$this->endFunction();
						break;
		}
	}
	
	
	
	protected function generateTruncate(){
	//	print_r($this->adb);
	//	die("finito");
		$db = $this->dbconfig['db_name'];
		echo ("PREPARAZIONE SCRIPT PER ELIMINARE TUTTE LE TABELLE DAL DATABASE: {$db}".PHP_EOL);
		$file = "{$this->root}/droptablefrom_{$db}.sql";
		@unlink ($file);
		$truncate = "SELECT 'SET FOREIGN_KEY_CHECKS = 0;' as schemaresult 
					union
					SELECT
						CONCAT('DROP TABLE  ', '{$db}','.',TABLE_NAME,';') as schemaresult 

					FROM
						information_schema.tables

					WHERE
						table_schema = ? 
					UNION
					SELECT 'SET FOREIGN_KEY_CHECKS = 1;' as schemaresult ";
		
		$result = $this->adb->pquery($truncate, array($db));
		
		if(!$result){
			// echo($this->adb->database->ErrorMsg());
			throw new Exception($this->adb->database->ErrorMsg(), $this->adb->database->ErrorNo());
		}
		while($row = $this->adb->fetchByAssoc($result)){
			// {$db}echo ("arrivato");
			// die(print_r($row, true));
		   $query .= $row['schemaresult'].PHP_EOL;
		}

		file_put_contents($file, $query);
		$this->endFunction();
	}
	
	
	
	protected function runSqlCode(){
		echo ("INSERISCI CREDENZIALI DEL DATABASE".PHP_EOL);
		echo ("HOST".PHP_EOL);
		$host = trim(fgets(STDIN));
		echo ("USER".PHP_EOL);
		$user = trim(fgets(STDIN));
		echo ("PASSWORD".PHP_EOL);
		$pass = trim(fgets(STDIN));
		echo ("###IL FILE DOVRÀ CONTENERE 'USE [nome_database];' ###".PHP_EOL);
		echo ("NOME DEL FILE .sql".PHP_EOL);
		$file = trim(fgets(STDIN));
		
		
		$sql = file_get_contents("{$this->root}/".$file);
		// Check connection
		
		
		$cmd = "mysql -u {$user} -p'{$pass}' -h {$host} -e '{$sql}'";
//		die ("sql: ".$cmd);
		
		echo ("EXECUTING SQL ... $sql".PHP_EOL);
		
		exec($cmd);
        $this->endFunction();
	}
	
	
	
	
	
	
	protected function mvField(){
            $file = "{$this->root}/mvrnField.txt";
            @unlink($file);
            echo ("INSERISCI NOME DEL DB IN CUI VERRANNO FATTE LE OPERAZIONI".PHP_EOL);
            $db = trim(fgets(STDIN));
            echo ("INSERISCI NOME DEL CAMPO DA SPOSTARE".PHP_EOL);
            $fieldSRC = trim(fgets(STDIN));
            echo ("INSERISCI IL TIPO DI DATO DEL CAMPO".PHP_EOL);
            $typeOfData = trim(fgets(STDIN));
            echo ("INSERISCI NOME DELLA TABELLA DOVE SI TROVA IL CAMPO".PHP_EOL);
            $tableSRC = trim(fgets(STDIN));
            echo ("INSERISCI IL CAMPO UNIVOCO DELA TABELLA SORGENTE".PHP_EOL);
            $keySRC = trim(fgets(STDIN));
            echo ("INSERISCI NOME CHE AVRÀ IL CAMPO".PHP_EOL);
            $fieldDST = trim(fgets(STDIN));
            echo ("INSERISCI NOME DELLA TABELLA DOVE VERRÀ CREATO IL CAMPO".PHP_EOL);
            $tableDST = trim(fgets(STDIN));
            echo ("INSERISCI IL CAMPO UNIVOCO DELA TABELLA DI DESTINAZIONE".PHP_EOL);
            $keyDST = trim(fgets(STDIN));
            
            $query = "UPDATE {$db}.vtiger_field SET columnname='{$fieldDST}', tablename='{$tableDST}', fieldname='{$fieldDST}' WHERE columnname='{$fieldSRC}' and tablename='{$tableSRC}';".PHP_EOL;

            $query .= "ALTER TABLE {$db}.{$tableDST} ADD COLUMN {$fieldDST} {$typeOfData};".PHP_EOL;

            $query .= "UPDATE {$db}.{$tableDST} INNER JOIN {$db}.{$tableSRC} ON {$tableDST}.{$keyDST} = {$tableSRC}.{$keySRC} SET {$tableDST}.{$fieldDST} = {$tableSRC}.{$fieldSRC};".PHP_EOL;

            $query .= "ALTER TABLE {$db}.{$tableSRC} DROP COLUMN {$fieldSRC};".PHP_EOL;
            
            file_put_contents($file, $query);
	
            $this->endFunction();
        }
	
	
	
	protected function databaseBackup(){
		echo ("INIZIO BACKUP DEL DATABASE: {$this->dbconfig['db_name']}".PHP_EOL);
		
		$date = date('Ymd_His');
		echo ("INSERISCI NOME FILE DOVE VERRÀ SALVATO IL FILE es backup ##Non mettere Estensioni##".PHP_EOL);
        $file ="{$this->root}/". trim(fgets(STDIN)).$date.'.sql';
		
		echo ("DIGITA 1 PER SALVARE IL BACKUP IN FORMATO ZIP".PHP_EOL);
        $zip = trim(fgets(STDIN));
		
		
		$date = date('Ymd_His');

		$cmd="mysqldump --user={$this->dbconfig['db_username']} --password='{$this->dbconfig['db_password'] }' --host={$this->dbconfig['db_server']} {$this->dbconfig['db_name']} --result-file={$file}";
		if(intval($zip) == 1){
			$cmd .= "&& zip {$file}.zip {$file}";	
		}
		echo "Running...". PHP_EOL . $cmd. PHP_EOL;

		exec($cmd);

		echo "DONE" . PHP_EOL;
		
		$this->endFunction();
	}
	
	
	
	protected function resetCron(){
            echo ("RESET CRON DEL DATABASE: {$this->dbconfig['db_name']}".PHP_EOL);
            echo ("INSERISCI IL NOME DEL CRON DA RESETTARE".PHP_EOL);
            $cronName = trim(fgets(STDIN));
            $query = "update vtiger_cron_task set laststart=0, lastend = 0, status=1 where name = '{$cronName}' ";
            $password = "'{$this->dbconfig['db_password']}'";
            $cmd = 'mysql -u '.$this->dbconfig['db_username'].' -h '.$this->dbconfig['db_server'].' -p'.$password.' '.$this->dbconfig['db_name'].' -e "'.$query.'" && sh cron/vtigercron.sh';
			exec($cmd);

			echo "DONE" . PHP_EOL;
// file_put_contents('prova.txt', $cmd);
			$this->endFunction();
        }
	
	
	
	
	protected function generateFilter(){
		
		$file = "{$this->root}/insertFiltri.txt";
		@unlink($file);
		$tables = array ('vtiger_customview', 'vtiger_cvadvfilter', 'vtiger_cvadvfilter_grouping', 'vtiger_cvcolumnlist', 'vtiger_cvstdfilter');
		echo ("INSERISCI L'ID DEL FILTRO DI ORIGINE".PHP_EOL);
		$cvidfr = trim(fgets(STDIN));
		echo ("INSERISCI L'ID DEL FILTRO DI DESTINAZIONE".PHP_EOL);
		$cvidto = trim(fgets(STDIN));
		
		foreach ($tables as $table){
			$query .= $this->makeRecoveryFilterSQL($table, $cvidfr, $cvidto);
		}
		$result = file_put_contents($file, $query.PHP_EOL, FILE_APPEND);
		if ($result){
			echo ("FILTRI SALVATI NEL FILE insertFiltri.txt".PHP_EOL);
		}
		$this->endFunction();
	}
	
	
	
	
	
	protected function makeRecoveryFilterSQL($table,$cvidfrom,$cvidto)
	{
		// get the record          
		$adb = $this->adb;
		
		$sql = "SELECT * FROM " . $table . " WHERE cvid = ?";
		$params = array($cvidfrom);
		$result = $adb->pquery($sql, $params);
			
		if(!$result){
			throw new Exception($adb->database->ErrorMsg(), $adb->database->ErrorNo());
		}

		$insertSQL = '-- '. $table . '['.$adb->num_rows($result).' rows] ' .PHP_EOL;
		while($row = $adb->fetchByAssoc($result))
		{
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
		return $insertSQL.PHP_EOL;
	}

	
	
	
	
	
	
	
	protected function generateWorkflow(){
		$file = "{$this->root}/insertWorkflow.txt";
		@unlink($file);
		$tables = array (
						'com_vtiger_workflows'
						,'com_vtiger_workflowtasks'
						//,'com_vtiger_workflow_activatedonce'
				);
				
		echo ("INSERISCI L'ID DEL WORKFLOW DI ORIGINE".PHP_EOL);
		$cvidfr = trim(fgets(STDIN));
		echo ("INSERISCI L'ID DEL WORKFLOW DI DESTINAZIONE".PHP_EOL);
		$cvidto = trim(fgets(STDIN));
		
		foreach ($tables as $table){
			$query .= $this->makeRecoveryWorkflowSQL($table, $cvidfr, $cvidto);
		}
		$result = file_put_contents($file, $query.PHP_EOL, FILE_APPEND);
		if ($result){
			echo ("WORKFLOW SALVATI NEL FILE insertWorkflow.txt".PHP_EOL);
		}
		
		
		$this->endFunction();
	}
	
	
	
	
	
	
	
	
	
	protected function makeRecoveryWorkflowSQL($table,$cvidfrom,$cvidto)
	{
		// get the record          
		$adb = $this->adb;
		
		$sql = "SELECT * FROM " . $table . " WHERE workflow_id = ?";
		$params = array($cvidfrom);
		$result = $adb->pquery($sql, $params);
			
		if(!$result){
			throw new Exception($adb->database->ErrorMsg(), $adb->database->ErrorNo());
		}

		$insertSQL = '-- '. $table . '['.$adb->num_rows($result).' rows] ' .PHP_EOL;
		while($row = $adb->fetchByAssoc($result))
		{
			$insertSQL .= "INSERT INTO " . $table . " SET ";
			foreach ($row as $field => $value) {
				if($field == 'workflow_id' && $wfidt == 1){
					$value = "(select max(id) from com_vtiger_workflows_seq)";
					$insertSQL .= " " . $field . " = " . $value . ", ";
				}
				else if($field == 'task_id' && $taskid == 1){
					$value = "(select max(id) from com_vtiger_workflowtasks_seq)";
					$insertSQL .= " " . $field . " = " . $value . ", ";
				}
				else{
					$insertSQL .= " " . $field . " = '" . $value . "', ";
				}
				
			}
			$insertSQL = trim($insertSQL, ", ");
			$insertSQL .= ';'.PHP_EOL;
			
			if($taskid == 1)
				$insertSQL .= 'update com_vtiger_workflowtasks_seq set id = id+1;'.PHP_EOL;
		}
		if($wfidt == 1)
			$insertSQL .= 'update com_vtiger_workflows_seq set workflow_id = id+1;'.PHP_EOL; 
		return $insertSQL.PHP_EOL;
	}
	
	
	
	protected function checkData($conn, $dbsrc, $definer, $file){
		$sql = "select routine_name, routine_type, routine_schema, routine_definition from information_schema.routines where routine_schema = '".$dbsrc."' and definer != '".$definer."';";
		// and (routine_name = 'html_UnEncode' or routine_name ='update_synchro_maps')
		echo ("EXECUTING SQL ... $sql".PHP_EOL);
		$result = $conn->query($sql);
	// 	echo ("ROW FOUNDED : ".$result->num_rows);
	//	echo (PHP_EOL);
		file_put_contents($file, '/* SKIPPED ROUTINES... ->' .PHP_EOL, FILE_APPEND);
		if ($result->num_rows > 0) {
				// output data of each row
				while($row = $result->fetch_assoc()) {
					$query = $row['routine_name'];
					file_put_contents($file, $query .PHP_EOL, FILE_APPEND);
				}
		}
		file_put_contents($file, '*/' .PHP_EOL, FILE_APPEND);
	}
	
	
	
	
	
	
	protected function importRoutine($dbdata = null){
		if ($db == null){
			// INPUT host, user, password, porta, dbsrc, dbdst
			echo ("INSERISCI DATI DEL DATABASE DI DESTINAZIONE".PHP_EOL);
			echo ("HOST".PHP_EOL);
			$host = trim(fgets(STDIN));
			echo ("USER".PHP_EOL);
			$user = trim(fgets(STDIN));
			echo ("PASSWORD".PHP_EOL);
			$pass = trim(fgets(STDIN));
			echo ("NOME DATABASE SORGENTE".PHP_EOL);
			$dbsrc = trim(fgets(STDIN));
			echo ("NOME DATABASE DESTINAZIONE".PHP_EOL);
			$dbdst = trim(fgets(STDIN));
		}
		else{
			$host = $dbdata[0];
			$user = $dbdata[1];
            $pass = $dbdata[2];
			$dbsrc = $dbdata[3];
			$dbdst = $dbdata[4];
		}
		echo ("DIGITA 1 PER SCRIVERE SIA I DROP CHE I CREATE".PHP_EOL);
		echo ("DIGITA 2 PER SCRIVERE SOLO I DROP".PHP_EOL);
		echo ("DIGITA 3 PER SCRIVERE SOLOI CREATE".PHP_EOL);
		$input = trim(fgets(STDIN));
		
		/* DA CANCELLARE SOLO PER TEST
		$host = 'localhost';
		$user = 'atalanta';

                $pass = 'Ribo2019!';
		$dbsrc = 'atalanta_starter';
		$dbdst = 'atalanta_demosta';
		$input = '1';*/
		$file = $this->root.'/'.$dbdst.'.routines.sql';
		@unlink($file);
		
		if($input == 2){
			$drop = true; 
			$create = false;
		}
		else if($input == 3){
			$drop = false; 
			$create = true;
		}
		else{
			$drop = true; 
			$create = true;
		}
		$definer=$user.'@'.$host;
		// Create connection
		$conn = new mysqli($host, $user, $pass);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		$this->checkData($conn, $dbsrc, $definer, $file);
		$sql = "select routine_name, routine_type, routine_schema, routine_definition from information_schema.routines where routine_schema = '".$dbsrc."' and definer = '".$definer."';";
		// and (routine_name = 'html_UnEncode' or routine_name ='update_synchro_maps')
		echo ("EXECUTING SQL ... $sql".PHP_EOL);
		$result = $conn->query($sql);
	// 	echo ("ROW FOUNDED : ".$result->num_rows);
	//	echo (PHP_EOL);
		if ($result->num_rows > 0) {
			// output data of each row
			while($row = $result->fetch_assoc()) {
				// echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
				// die (print_r($row, true));
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
				// 	echo ($query.PHP_EOL);
					$res = file_put_contents($file, $query .PHP_EOL, FILE_APPEND);
				/*	if (!$result){
						throw new Exception("ERRORE IMPORTAZIONE DC");
					}*/
		

				}
			//	die("Arrivato");
				
			} 
			$conn->close();
			$dbdata = array ($host , $user , $pass, $dbsrc , $dbdst);
			
			echo ("DIGITA 1 SE IMPORTARE ANCHE GLI EVENTI".PHP_EOL);
			echo ("DIGITA 2 SE IMPORTARE ANCHE LE VISTE".PHP_EOL);
			echo ("PER TORNARE AL MENU PRINCIPALE DIGITA QUALCOSA A CASO".PHP_EOL);
			$other = trim(fgets(STDIN));
			if ($other == 1){
				$this->importEvents($dbdata);
			}
			else if ($other == 2){
				$this->importviews($dbdata);
			}
		$this->endFunction();	
	}
	
	
	
	
	protected function importViste($dbdata = null){
		if ($db == null){
			// INPUT host, user, password, porta, dbsrc, dbdst
			echo ("INSERISCI DATI DEL DATABASE DI DESTINAZIONE".PHP_EOL);
			echo ("HOST".PHP_EOL);
			$host = trim(fgets(STDIN));
			echo ("USER".PHP_EOL);
			$user = trim(fgets(STDIN));
			echo ("PASSWORD".PHP_EOL);
			$pass = trim(fgets(STDIN));
			echo ("NOME DATABASE SORGENTE".PHP_EOL);
			$dbsrc = trim(fgets(STDIN));
			echo ("NOME DATABASE DESTINAZIONE".PHP_EOL);
			$dbdst = trim(fgets(STDIN));
		}
		else{
			$host = $dbdata[0];
			$user = $dbdata[1];
            $pass = $dbdata[2];
			$dbsrc = $dbdata[3];
			$dbdst = $dbdata[4];
		}
		echo ("DIGITA 1 PER SCRIVERE SIA I DROP CHE I CREATE".PHP_EOL);
		echo ("DIGITA 2 PER SCRIVERE SOLO I DROP".PHP_EOL);
		echo ("DIGITA 3 PER SCRIVERE SOLOI CREATE".PHP_EOL);
		$input = trim(fgets(STDIN));
		
		/* DA CANCELLARE SOLO PER TEST
		$host = 'localhost';
		$user = 'atalanta';

                $pass = 'Ribo2019!';
		$dbsrc = 'atalanta_starter';
		$dbdst = 'atalanta_demosta';
		$input = '1';*/
		$file = $this->root.'/'.$dbdst.'.views.sql';
		@unlink($file);
		
		if($input == 2){
			$drop = true; 
			$create = false;
		}
		else if($input == 3){
			$drop = false; 
			$create = true;
		}
		else{
			$drop = true; 
			$create = true;
		}
		// Create connection
		$conn = new mysqli($host, $user, $pass);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
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
				file_put_contents($file , $vista .PHP_EOL, FILE_APPEND);
			}
			
		}
		
		echo ("DIGITA 1 SE IMPORTARE ANCHE GLI EVENTI".PHP_EOL);
		echo ("DIGITA 2 SE IMPORTARE ANCHE LE ROUTINE".PHP_EOL);
		echo ("PER TORNARE AL MENU PRINCIPALE DIGITA QUALCOSA A CASO".PHP_EOL);
			$other = trim(fgets(STDIN));
			if ($other == 1){
				$this->importEvents($dbdata);
			}
			else if ($other == 2){
				$this->importRoutine($dbdata);
			}
		$this->endFunction();	


	}		
	
	
	
	protected function importEvent($dbdata = null){
		if ($db == null){
			// INPUT host, user, password, porta, dbsrc, dbdst
			echo ("INSERISCI DATI DEL DATABASE DI DESTINAZIONE".PHP_EOL);
			echo ("HOST".PHP_EOL);
			$host = trim(fgets(STDIN));
			echo ("USER".PHP_EOL);
			$user = trim(fgets(STDIN));
			echo ("PASSWORD".PHP_EOL);
			$pass = trim(fgets(STDIN));
			echo ("NOME DATABASE SORGENTE".PHP_EOL);
			$dbsrc = trim(fgets(STDIN));
			echo ("NOME DATABASE DESTINAZIONE".PHP_EOL);
			$dbdst = trim(fgets(STDIN));
		}
		else{
			$host = $dbdata[0];
			$user = $dbdata[1];
            $pass = $dbdata[2];
			$dbsrc = $dbdata[3];
			$dbdst = $dbdata[4];
		}
		echo ("DIGITA 1 PER SCRIVERE SIA I DROP CHE I CREATE".PHP_EOL);
		echo ("DIGITA 2 PER SCRIVERE SOLO I DROP".PHP_EOL);
		echo ("DIGITA 3 PER SCRIVERE SOLOI CREATE".PHP_EOL);
		$input = trim(fgets(STDIN));
		
		/* DA CANCELLARE SOLO PER TEST
		$host = 'localhost';
		$user = 'atalanta';

                $pass = 'Ribo2019!';
		$dbsrc = 'atalanta_starter';
		$dbdst = 'atalanta_demosta';
		$input = '1';*/
		$file  = $this->root.'/'.$dbdst.'.events.sql';
		@unlink($file);
		
		if($input == 2){
			$drop = true; 
			$create = false;
		}
		else if($input == 3){
			$drop = false; 
			$create = true;
		}
		else{
			$drop = true; 
			$create = true;
		}
		// Create connection
		$conn = new mysqli($host, $user, $pass);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
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
				file_put_contents($file ,$event.PHP_EOL, FILE_APPEND);
			}
		}


		echo ("DIGITA 1 SE IMPORTARE ANCHE LE VISTE".PHP_EOL);
		echo ("DIGITA 2 SE IMPORTARE ANCHE LE ROUTINE".PHP_EOL);
		echo ("PER TORNARE AL MENU PRINCIPALE DIGITA QUALCOSA A CASO".PHP_EOL);
			$other = trim(fgets(STDIN));
			if ($other == 1){
				$this->importViste($dbdata);
			}
			else if ($other == 2){
				$this->importRoutine($dbdata);
			}
		$this->endFunction();	

	
}
}


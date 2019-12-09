<?php

chdir('..');

// Nessun timeout per il cron
set_time_limit(0);

if (substr(php_sapi_name(), 0, 3 ) != "cli" && (substr(php_sapi_name(), 0, 3 ) != "cgi" )) {
    die("ALGOMA TOOLKIT WORKFLOW  EXPORTER must runs from console");
}

error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ERROR);
ini_set("display_errors", 1);


$wfidf  = 0;
$wfidt  = 0;   
$taskid  = 0;


/*echo "count par". count($argv);

print_r($argv);*/

if (count($argv) == 2){
    $wfidf  = $argv[1];
    $wfidt  =  $wfidf;   
}

else if (count($argv) == 3){
    $wfidf  = $argv[1];
    $wfidt  = $argv[2];

}

else if (count($argv) == 4){
    $wfidf  = $argv[1];
    $wfidt  = $argv[2];
    $taskid  = $argv[3];
}


else {
    die("Wrong number of arguments \n "
            . "USAGE: php {$argv[0]} wfidf [wfidt] [taskid] \n "
            . "wfidf => workflow to export\n "
            . "wfidt =>  [0|1] If 1 use next id, 0 ignore\n "
            . "taskid =>  [0|1] If 1 use next id, 0 ignore\n "
	);
}


include_once 'vtlib/Vtiger/Module.php';
include_once 'modules/Users/Users.php';
include_once 'modules/Emails/mail.php';
include_once 'include/utils/utils.php';
global $adb;

$tables = array (
                    'com_vtiger_workflows'
                    ,'com_vtiger_workflowtasks'
                    //,'com_vtiger_workflow_activatedonce'
            );


foreach ($tables as $table){
	$query .= makeRecoverySQL($table, $cvidfr, $cvidto);
}

//echo $query;




function makeRecoverySQL($table,$cvidfrom,$cvidto)
{
    // get the record          
	global $adb;
	
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

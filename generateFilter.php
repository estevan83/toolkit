<?php



// Nessun timeout per il cron
set_time_limit(0);

if (substr(php_sapi_name(), 0, 3 ) != "cli" && (substr(php_sapi_name(), 0, 3 ) != "cgi" )) {
    die("ALGOMA TOOLKIT GENERATE FILTER must runs from console");
}

error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ERROR);
ini_set("display_errors", 1);


$cvidfr  = 0;
$cvidto  = 0;   

/*echo "count par". count($argv);

print_r($argv);*/

if (count($argv) == 2){
    $cvidfr  = $argv[1];
    $cvidto  =  $cvidfr;   
}

else if (count($argv) != 3){
    die("Wrong number of arguments \n "
            . "USAGE: php {$argv[0]} cvidfrom [cvidto] \n "
	);
}

else{
   
    $cvidfr  = $argv[1];
    $cvidto  = $argv[2];

    
}
/*
echo $cvidfr;
echo $cvidto;
*/

include_once 'vtlib/Vtiger/Module.php';
include_once 'modules/Users/Users.php';
include_once 'modules/Emails/mail.php';
include_once 'include/utils/utils.php';
global $adb;

$tables = array ('vtiger_customview', 'vtiger_cvadvfilter', 'vtiger_cvadvfilter_grouping', 'vtiger_cvcolumnlist', 'vtiger_cvstdfilter');

foreach ($tables as $table){
	$query .= makeRecoverySQL($table, $cvidfr, $cvidto);
}
echo $query;




function makeRecoverySQL($table,$cvidfrom,$cvidto)
{
    // get the record          
	global $adb;
	
    $sql = "SELECT * FROM " . $table . " WHERE cvid = ?";
	$params = array($cvidfrom);
	$result = $adb->pquery($sql, $params);
        
        if(!$result){
            throw new Exception($adb->database->ErrorMsg(), $adb->database->ErrorNo());
         }

	$row = $adb->fetchByAssoc($result);

    if ($row == null)
    {
        return;
    }

	$insertSQL = '-- '. $table . PHP_EOL;
    $insertSQL .= "INSERT INTO " . $table . " SET ";
    foreach ($row as $field => $value) {
        if($field == 'cvid'){
            $value = $cvidto;
        }
        $insertSQL .= " " . $field . " = '" . $value . "', ";
    }
    $insertSQL = trim($insertSQL, ", ");
    $insertSQL .= ';'.PHP_EOL.PHP_EOL;
    return $insertSQL;
}

<?php


include_once 'vtlib/Vtiger/Module.php';
include_once 'modules/Users/Users.php';
include_once 'modules/Emails/mail.php';
include_once 'include/utils/utils.php';
global $adb;

$tables = array ('vtiger_customview', 'vtiger_cvadvfilter', 'vtiger_cvadvfilter_grouping', 'vtiger_cvcolumnlist', 'vtiger_cvstdfilter');

$cvid  = 4;// $argv[1];

try{
    foreach ($tables as $table){
        $query .= makeRecoverySQL($table, $cvid);
        
        
           // file_put_contents("generatedFilter.txt", $query.';'.PHP_EOL, FILE_APPEND);
        
        
      //   $query[] = makeRecoverySQL($table,$cvid);
    }
    echo $query;
}
catch(Exception $ex){
    $a =1;
}



function makeRecoverySQL($table, $cvid)
{
    // get the record          
	global $adb;
	
    $sql = "SELECT * FROM " . $table . " WHERE cvid = ?";
	$params = array($cvid);
	$result = $adb->pquery($sql, $params);
        
        if(!$result){
            throw new Exception($adb->database->ErrorMsg(), $adb->database->ErrorNo());
         }

	$row = $adb->fetchByAssoc($result);

        if ($row == null)
        {
            return;
        }
  //  $result = mysql_query($selectSQL, $adb);
   // $row = mysql_fetch_assoc($result); 

    $insertSQL = "INSERT INTO " . $table . " SET ";
    foreach ($row as $field => $value) {
        $insertSQL .= " " . $field . " = '" . $value . "', ";
    }
    $insertSQL = trim($insertSQL, ", ");
    $insertSQL .= ';'.PHP_EOL;
    return $insertSQL;
}


<?php

/* 
    Algoma SRL 

    Tool di migrazione dati da una fonte dati al CRM.
*/

$VERSION = "5.00 2020-02-22";
date_default_timezone_set('Europe/Rome');

//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
$dirname = realpath(dirname(__FILE__) . '/..');
//echo $dirname;
set_include_path($dirname);
chdir($dirname);

set_time_limit(0);
if (substr(php_sapi_name(), 0, 3) != "cli" && (substr(php_sapi_name(), 0, 3) != "cgi")) {
    die("ALGOMA MASSIVE IMPORT TOOLKIT must runs from console");
}

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1);

$Vtiger_Utils_Log = false;

include_once 'vtlib/Vtiger/Module.php';
include_once 'modules/Users/Users.php';
include_once 'modules/Emails/mail.php';
include_once 'include/utils/utils.php';
/*
if(!@include("../vtigertools/modulebuilderhelper.class.php")) {
    if(!@include("modulebuilderhelper.class.php")) {
        die("Failed to include 'modulebuilderhelper.class.php'");
    }
}*/

/*@include_once 'include/Algoma/SmartOneHelper.php';*/

include_once 'includes/main/WebUI.php';
require_once 'modules/Emails/mail.php';



$groupIdNameCache = array();


// Nessun timeout per il cron
set_time_limit(0);


error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ERROR);
ini_set("display_errors", 1);
// Turn off debugging level
$Vtiger_Utils_Log = false;

define(PUSHNEVER, 0);
define(PUSHALWAYS, 1);
define(PUSHERROR, 2);

$sendMail     = PUSHNEVER;
$sendTelegram = PUSHNEVER;

$telegramRecipients = array(
	//'657351827', //paolo
	'139720777' // estevan
);

$telegramApiKey = '390120497:AAGsEe3gQEv_kT7ElcVk3_CNhCCL7xPZO7s';

define(NONE, -1);
define(INFO, 0);
define(ERROR, 1);
define(FATAL, 2);
define(DEBUG, 4);

// define(LOGLEVEL, DEBUG);
define(LOGLEVEL, FATAL);

if (file_exists($argv[1]))
    require_once $argv[1];
else
    die("File not exist XXX {$argv[1]}");

$run = date("d");

$run = 'schema/logs/' . $run;
// $run = 'logs/' . $run;

global $adb;

$companyDetails = getCompanyDetails();


foreach ($map['sources'] as $tblName => $source) {
    if ($sendTelegram == PUSHALWAYS || ($sendTelegram == PUSHERROR && $failed > 0)) {
        telegramLog("[AlgomaCRM  - Avvio importazione]", $companyDetails['companyname'], $tblName, $run . $source['errorlogfile']);
    }
    $reftable = $map['referencetable'];

    writeLog($run . $source['errorlogfile'], "ALGOMACRM SYNCRHONIZER  Version {$VERSION} - Ing. Estefan Civera www.algoma.it", INFO);
    writeLog($run . $source['errorlogfile'], "Connetting to source database", INFO);

    $dbconfig = $map['datasource'];
    $dbSrc    = new PearDatabase($dbconfig['driver'], $dbconfig['host'], $dbconfig['database'], $dbconfig['user'], $dbconfig['password']);

    if (!isset($reftable)) {
        writeLog($run . $source['errorlogfile'], "WARNING!!! Reference table is not defined", DEBUG);
    } else {
        writeLog($run . $source['errorlogfile'], "Reference table is " . $reftable, DEBUG);
    }

    writeLog($run . $source['errorlogfile'], "Log Admin User", INFO);

    $user               = new Users();
    $current_user       = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());
    $crmEntity          = new CRMEntity();
    $reportcounter      = array();
    $moduleInstanceList = array();
    $fieldInstanceList  = array();
    $inserted           = 0;
    $updated            = 0;
    $failed             = 0;
    $count              = 0;
    $beginTime          = date("Y-m-d H:i:s");
    $isUpdate           = false;

    writeLog($run . $source['errorlogfile'], "Reading data to synchronize (may be long time operation)", INFO);
    writeLog($run . $source['errorlogfile'], "Run query: " . $source['query'], DEBUG);
	
	
	if ($source['initquerydata'] != '') {
        $resImport = $adb->query($source['initquerydata']);
        
        if ($resImport == false) {
            writeLog($run . $source['errorlogfile'], "Error executing query " . $source['initquerydata'], ERROR);
            throw new Exception($adb->database->ErrorMsg(), $adb->database->ErrorNo());
        }
    }

    if ($source['resetdata'] != '' && $reftable != '') {
        $resImport = $dbSrc->query('truncate table ' . $reftable);
        $resImport = $dbSrc->query($source['resetdata']);
        if ($resImport == false) {
            writeLog($run . $source['errorlogfile'], "Error executing query " . $source['query'], ERROR);
            throw new Exception($dbSrc->database->ErrorMsg(), $dbSrc->database->ErrorNo());
        }
    }
    $resImport = $dbSrc->query($source['query']);
    if ($resImport == false) {
        writeLog($run . $source['errorlogfile'], "Error executing query " . $source['query'], ERROR);
        throw new Exception($dbSrc->database->ErrorMsg(), $dbSrc->database->ErrorNo());
    }
    $count = $dbSrc->getRowCount($resImport);
    writeLog($run . $source['errorlogfile'], "Found $count items to import...", INFO);
    $i = 0;
    while ($row = $dbSrc->fetchByAssoc($resImport)) {
        $i++;
        writeLog($run . $source['errorlogfile'], "Run {$i}/$count....", INFO);
        writeLog($run . $source['errorlogfile'], "DB Row to import..." . PHP_EOL . print_r($row, true), DEBUG);
        try {
            $autoinc         = "";
            $crmentitycustom = false;
            if (!is_numeric($row['crmid'])) {
                $nextID = $adb->getUniqueID("vtiger_crmentity");
            } else {
                $nextID = $row['crmid'];
            }
            foreach ($source['tablemapping'] as $tablename => $fields) {
                if ($crmentitycustom == false && $tablename === 'vtiger_crmentity') {
                    $crmentitycustom = true;
                    if (!isset($fields['modifiedtime']))
                        $fields['modifiedtime'] = array(
                            'keyword',
                            "now()"
                        );
                    if (!is_numeric($row['crmid'])) {
                        if (!isset($fields['createdtime']))
                            $fields['createdtime'] = array(
                                'keyword',
                                "now()"
                            );
                    }
                }
                $keyvalue        = array();
                $params          = array();
                $query           = "insert into {$tablename} (";
                $updt            = "update {$tablename} set ";
                $values          = "";
                $updateWhere     = '';
                $updateWhereCond = '';
                foreach ($fields as $dest => $src) {
                    if ($src[0] === 'field') {
                        if ($values != '') {
                            $query .= ",";
                            $values .= ",";
                            $updt .= ",";
                        }
                        $query .= "{$dest}";
                        $values .= "?";
                        $updt .= "{$dest} =?";
                        $params[]                           = $row[$src[1]];
                        $keyvalue[$tablename . '.' . $dest] = $row[$src[1]];
                    } else if ($src[0] === 'fieldnohtml') {
                        if ($values != '') {
                            $query .= ",";
                            $values .= ",";
                            $updt .= ",";
                        }
                        $query .= "{$dest}";
                        $values .= "?";
                        $updt .= "{$dest} =?";
                        $clear                              = sanitizeValue($row[$src[1]]);
                        $params[]                           = $clear;
                        $keyvalue[$tablename . '.' . $dest] = $clear;
                    } else if ($src[0] === 'autoinc') {
                        if (!is_numeric($row['crmid'])) {
                            if ($values != '') {
                                $query .= ",";
                                $values .= ",";
                                $updt .= ",";
                            }
                            $query .= "{$dest}";
                            $values .= "?";
                            $updt .= "{$dest} =?";
                            $auto                               = $crmEntity->setModuleSeqNumber('increment', $src[1]);
                            $keyvalue[$tablename . '.' . $dest] = $auto;
                            $params[]                           = $auto;
                        }
                    } else if ($src[0] === 'unique') {
                        if ($values != '') {
                            $query .= ",";
                            $values .= ",";
                            $updt .= ",";
                        }
                        $query .= "{$dest}";
                        $values .= "?";
                        $updt .= "{$dest} =?";
                        $params[]                           = $nextID;
                        $keyvalue[$tablename . '.' . $dest] = $nextID;
                        $updateWhere                        = " where $dest = ?";
                        $updateWhereCond                    = $nextID;
                    } else if ($src[0] === 'value') {
                        if ($values != '') {
                            $query .= ",";
                            $values .= ",";
                            $updt .= ",";
                        }
                        $query .= "{$dest}";
                        $values .= "?";
                        $updt .= "{$dest} =?";
                        $params[]                           = $src[1];
                        $keyvalue[$tablename . '.' . $dest] = $src[1];
                    } else if ($src[0] === 'keyword') {
                        if ($values != '') {
                            $query .= ",";
                            $values .= ",";
                            $updt .= ",";
                        }
                        $query .= "{$dest}";
                        $values .= $src[1];
                        $updt .= "{$dest} ={$src[1]}";
                    } else if ($src[0] === 'function') {
                        if ($values != '') {
                            $query .= ",";
                            $values .= ",";
                            $updt .= ",";
                        }
                        $query .= "{$dest}";
                        $values .= "?";
                        $updt .= "{$dest} =?";
                        $res = call_user_func_array($src[1], array(
                            $current_user,
                            $dbSrc,
                            $adb,
                            $dest,
                            $row
                        ));
                        writeLog($run . $source['errorlogfile'], $src[1] . "::" . print_r($row, true) . " => " . print_r($res, true), DEBUG);
                        $keyvalue[$tablename . '.' . $dest] = $res;
                        $params[]                           = $res;
                    } else if ($src[0] === 'list') {
                        writeLog($run . $source['errorlogfile'], $src[1] . ":: dest=" . $dest . " ==> " . print_r($src, true) . " => " . print_r($row, true), DEBUG);
                        if (!isset($moduleInstanceList[$src[1]])) {
                            $moduleInstanceList[$src[1]] = Vtiger_Module::getInstance($src[1]) or die("Module instance is null");
                        }
                        $moduleInstance = $moduleInstanceList[$src[1]];
                        if (!isset($fieldInstanceList[$dest])) {
                            $fieldInstanceList[$src[2]] = Vtiger_Field::getInstance($dest, $moduleInstance) or die("field  instance is null");
                        }
                        $fieldInstance = $fieldInstanceList[$src[2]];
                        $clear         = sanitizeValue($row[$src[2]]);
                        if ($clear != '') {
                            $fieldInstance->setPicklistValues(array(
                                $clear
                            ));
                        }
                        if ($values != '') {
                            $query .= ",";
                            $values .= ",";
                            $updt .= ",";
                        }
                        $query .= "{$dest}";
                        $values .= "?";
                        $updt .= "{$dest} =?";
                        $params[]                           = $clear;
                        $keyvalue[$tablename . '.' . $dest] = $clear;
                    } else {
                        throw new Exception("No valid type  {$src[0]} for field  {$dest}");
                    }
                }
                $query .= ")values ($values)";
                $updt .= $updateWhere;
                $params[] = $updateWhereCond;
                if (is_numeric($row['crmid'])) {
                    writeLog($run . $source['errorlogfile'], "UPDATE " . $updt, DEBUG);
                } else {
                    writeLog($run . $source['errorlogfile'], "INSERT " . $query, DEBUG);
                }
                if (is_numeric($row['crmid'])) {
                    $res      = $adb->pquery($updt, $params);
                    $isUpdate = true;
                } else {
                    $lastel   = array_pop($params);
                    $res      = $adb->pquery($query, $params);
                    $isUpdate = false;
                }
                writeLog($run . $source['errorlogfile'], "Key values params for query..." . PHP_EOL . print_r($keyvalue, true), DEBUG);
                if ($res == false) {
                    $failed++;
                    writeLog($run . $source['errorlogfile'], $adb->database->ErrorMsg(), $adb->database->ErrorNo(), ERROR);
                    writeLog($run . $source['errorlogfile'], "Key values params for query..." . PHP_EOL . print_r($keyvalue, true), ERROR);
                    throw new Exception($adb->database->ErrorMsg(), $adb->database->ErrorNo());
                }
            }
            $isUpdate == true ? $updated++ : $inserted++;
            /*if ($crmentitycustom == false) {
                writeLog($run . $source['errorlogfile'], "CRM Entity standard", DEBUG);
                $query  = "";
                $values = "";
                if ($isUpdate == true) {
                    $query  = "REPLACE INTO `vtiger_crmentity` ( 
								`crmid`, `smcreatorid`, `smownerid`, `modifiedby`, 
								`setype`,  
								`version`, `presence`, `deleted`, 
								`label`, `searchlabel`,
								`modifiedtime`
							) 
							VALUES (
								?, ?, ?, ?, 
								?, 
								?, ?, ?,
								?, ?,
								now()
							)";
                    $params = array(
                        $nextID,
                        $current_user->id,
                        $current_user->id,
                        $current_user->id,
                        $source['setype'],
                        0,
                        1,
                        0,
                        $row[$source['label']],
                        $row[$source['searchlabel']]
                    );
                } else {
                    $query  = "REPLACE INTO `vtiger_crmentity` ( 
								`crmid`, `smcreatorid`, `smownerid`, `modifiedby`, 
								`setype`,  
								`version`, `presence`, `deleted`, 
								`label`, `searchlabel`,
								`modifiedtime`, `createdtime`
							) 
							VALUES (
								?, ?, ?, ?, 
								?, 
								?, ?, ?,
								?, ?,
								now(), now()
							)";
                    $params = array(
                        $nextID,
                        $current_user->id,
                        $current_user->id,
                        $current_user->id,
                        $source['setype'],
                        0,
                        1,
                        0,
                        $row[$source['label']],
                        $row[$source['searchlabel']]
                    );
                }
                $now    = date("Y-m-d H:i:s");
                $params = array(
                    $nextID,
                    $current_user->id,
                    $current_user->id,
                    $current_user->id,
                    $source['setype'],
                    $now,
                    $now,
                    0,
                    1,
                    0,
                    $row[$source['label']],
                    $row[$source['searchlabel']]
                );
                $res    = $adb->pquery($query, $params);
                if ($res == false) {
                    writeLog($run . $source['errorlogfile'], $adb->database->ErrorMsg(), $adb->database->ErrorNo(), ERROR);
                    writeLog($run . $source['errorlogfile'], "Params for query..." . PHP_EOL . print_r($params, true), ERROR);
                    throw new Exception($adb->database->ErrorMsg(), $adb->database->ErrorNo());
                }
            }*/
            if (isset($reftable)) {
                writeLog($run . $source['errorlogfile'], "Syncro {$i}/$count....", DEBUG);
                syncronizeTable($dbSrc, $row, $source['primarykey'], $tblName, $nextID, $reftable, $run . $source['errorlogfile']);
            } else {
                writeLog($run . $source['errorlogfile'], "Cannot Syncro {$i}/$count.... no reftable specified", DEBUG);
            }
        }
        catch (Exception $e) {
            $failed++;
            writeLog($run . $source['errorlogfile'], $e->getMessage(), ERROR);
        }
        $reportcounter[$tblName] = array(
            'INSERTED' => $inserted,
            'UPDATED' => $updated,
            'FAILED' => $failed,
            'TOTAL' => $count
        );
        writeLog($run . $source['errorlogfile'], $tblName . PHP_EOL . print_r($reportcounter, true), DEBUG);
    }
	
	if ($source['endquerydata'] != '') {
        $resImport = $adb->query($source['endquerydata']);
        
        if ($resImport == false) {
            writeLog($run . $source['errorlogfile'], "Error executing ENDQUERYDARTA query " . $source['endquerydata'], ERROR);
            throw new Exception($adb->database->ErrorMsg(), $adb->database->ErrorNo());
        }
    }
	
    $endTime = date("Y-m-d H:i:s");
    $subject = "[AlgomaCRM  - Esito importazione " . $companyDetails['companyname'] . "] :: $tblName ";
    $bdyTL   = "[AlgomaCRM  - Esito importazione " . $companyDetails['companyname'] . "] :: $tblName " . PHP_EOL . "Elementi totali {$reportcounter[$tblName][TOTAL]}" . PHP_EOL . "Elementi creati {$reportcounter[$tblName][INSERTED]}" . PHP_EOL . "Elementi aggiornati {$reportcounter[$tblName][UPDATED]}" . PHP_EOL . "Elementi falliti {$reportcounter[$tblName][FAILED]}" . PHP_EOL . "Importazione avviata il {$beginTime} e terminata il {$endTime}" . PHP_EOL;
    writeLog($run . $source['errorlogfile'], $bdyTL, INFO);
    $body = nl2br($bdyTL);
    if ($sendTelegram == PUSHALWAYS || ($sendTelegram == PUSHERROR && $failed > 0)) {
        telegramLog($companyDetails['companyname'], $subject, $bdyTL, $run . $source['errorlogfile']);
    }
    if ($sendMail == PUSHALWAYS || ($sendMail == PUSHERROR && $failed > 0)) {
        if ($current_user->email1 != '') {
            writeLog($run . $source['errorlogfile'], "Send mail to {$current_user->email1}", INFO);
            $sended = send_mail('', $current_user->email1, "AlgomaCRM Reporting Tool", $current_user->email1, $subject, $body);
        }
        if ($current_user->email2 != '') {
            writeLog($run . $source['errorlogfile'], "Send mail to {$current_user->email2}", INFO);
            $sended = send_mail('', $current_user->email2, "AlgomaCRM Reporting Tool", $current_user->email1, $subject, $body);
        }
        if ($sended == FALSE) {
            writeLog($run . $source['errorlogfile'], "Impossible to send result", INFO);
        }
    } else {
        writeLog($run . $source['errorlogfile'], "No need to send mail to {$current_user->email1} {$current_user->email2}", INFO);
    }
}
function sanitizeValue($des)
{
    $clear = strip_tags($des);
    $clear = htmlspecialchars_decode($clear, ENT_QUOTES);
    $clear = html_entity_decode($clear);
    $clear = urldecode($clear);
    $clear = trim($clear);
    return $clear;
}
function writeLog($file, $msg, $type = DEBUG)
{
    $logLevel = array(
        'INFO',
        'ERROR',
        'FATAL',
        'DEBUG'
    );
    if ($type <= LOGLEVEL || $type == ERROR || $type == FATAL) {
        $now = date("Y-m-d H:i:s");
        echo "$now | $logLevel[$type] => $msg" . PHP_EOL;
        $res = file_put_contents($file, "$now | $logLevel[$type] => $msg" . PHP_EOL, FILE_APPEND | LOCK_EX);
        if ($res == false)
            throw new Exception("cannot write log file");
    }
}
function syncronizeTable(PearDatabase $adb, $row, $pk, $source, $id, $tbl, $logfile)
{
    $keyvalue = array(
        'table' => $tbl,
        'pk' => $row[$pk],
        'entity' => $source,
        'crmid' => $id
    );
    writeLog($logfile, "syncronizeTable " . print_r($keyvalue, true), DEBUG);
    if ($adb->isMySQL()) {
        writeLog($logfile, "MySql replace" . PHP_EOL . "replace into $tbl(pk, entity,crmid, lastsync) values(?,?, ?, now())", DEBUG);
        $res = $adb->pquery("replace into $tbl(pk, entity,crmid, lastsync) values(?,?, ?, now())", array(
            $row[$pk],
            $source,
            $id
        ));
        if ($res == false) {
            writeLog($logfile, $adb->database->ErrorMsg(), $adb->database->ErrorNo(), ERROR);
            throw new Exception($adb->database->errorMsg(), $adb->database->ErrorNo());
        }
        writeLog($logfile, print_r($keyvalue, true), DEBUG);
    } else {
        $now   = date("Y-m-d H:i:s");
        $sincp = array(
            $row[$pk],
            $source,
            $id,
            $now,
            $id,
            $source
        );
        $upd   = "UPDATE  $tbl SET pk =?,  entity =?, crmid = ?, lastsync=? WHERE crmid=? and entity=?;";
        writeLog($logfile, $upd, DEBUG);
        writeLog($logfile, print_r($sincp, true), DEBUG);
        $res = $adb->pquery($upd, $sincp);
        if ($res == false) {
            writeLog($logfile, $adb->database->ErrorMsg(), $adb->database->ErrorNo(), ERROR);
            throw new Exception($adb->database->errorMsg(), $adb->database->ErrorNo());
        }
        $updflag = true;
        if ($adb->getAffectedRowCount($res) == 0) {
            $updflag = false;
            $upd     = "INSERT INTO  $tbl(pk, entity,crmid, lastsync) SELECT ?, ?, ?,?";
            writeLog($logfile, $upd, DEBUG);
            $sincp = array(
                $row[$pk],
                $source,
                $id,
                $now
            );
            writeLog($logfile, $sincp, DEBUG);
            $res = $adb->pquery($upd, $sincp);
            if ($res == false) {
                writeLog($logfile, $adb->database->ErrorMsg(), $adb->database->ErrorNo(), ERROR);
                throw new Exception($adb->database->errorMsg(), $adb->database->ErrorNo());
            }
        }
        $keyvalue = array(
            'table' => $tbl,
            'pk' => $row[$pk],
            'entity' => $source,
            'crmid' => $id
        );
        writeLog($logfile, print_r($keyvalue, true), DEBUG);
    }
    if ($res == false) {
        writeLog($logfile, $adb->database->ErrorMsg(), $adb->database->ErrorNo(), ERROR);
        throw new Exception($adb->database->errorMsg(), $adb->database->ErrorNo());
    }
    return $res;
}
function telegramLog($customer, $subject, $message, $logfile)
{
   global $telegramRecipients;
   global $telegramApiKey;
	
    writeLog($logfile, "Send telegram push notification", $subject, INFO);

    $message         = str_ireplace($breaks, "\r\n", $message);
    $message         = strip_tags($message);
    $curl = curl_init();
	foreach($telegramRecipients as $rec){
		
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.telegram.org/bot$telegramApiKey/sendmessage",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_POSTFIELDS => "text=$message&chat_id=$rec",
		  CURLOPT_HTTPHEADER => array(
			"content-type: application/x-www-form-urlencoded"
		  ),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
	}
    curl_close($curl);	
	
    writeLog($logfile, print_r($result, true), "", INFO);
}
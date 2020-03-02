<?php
//die("Algoma Massive user importer");
//-----------------------------------------------------------------------------------------------
// Algoma Massive user importer
// Version 1.00 2017/12/12
// Ing. Estefan Civera - www.estefancivera.net													
//-----------------------------------------------------------------------------------------------

/* Url del CRM */
$url = 'http://ribo.algoma.it';
/* User admin of CRM */
$wsuser = "admin";
/* User Access key */
$wsaccesskey = "VuLW6wTNNeef0JAu";
/* User file */
$usersfile = "utenti.csv";
/* Field separator */
$separator = ",";
/* Field enclosed in */
$encloser = '"';

///////////////////////////////////////////////////////////////////////////////////////////////////
// 								DO NOT EDIT THE CODE BELOW
///////////////////////////////////////////////////////////////////////////////////////////////////
error_reporting(E_ERROR);
ini_set('display_errors', 1);

echo '
//-----------------------------------------------------------------------------------------------
// Algoma Massive user importer
// Version 1.00 2017/12/12
// Ing. Estefan Civera - www.estefancivera.net													
//-----------------------------------------------------------------------------------------------
';

include_once('vtwsclib/Vtiger/WSClient.php');

$client = new Vtiger_WSClient($url);
$login = $client->doLogin($wsuser, $wsaccesskey);
if(!$login) 
	die('Login Failed');

$total =0;
$failed = 0;
$inserted = 0;

$assoc=array(); 
$first=true; 
if (($handle = fopen($usersfile, "r")) !== FALSE) { 
	while (($data = fgetcsv($handle, 10000, $separator, $encloser)) !== FALSE) { 
		if ($first) { 
			$assoc=$data; 
			$first=false; 
		} 
		else { 
			$total++;

			$send=array(); 
			foreach ($assoc as $key=>$name) { 			
			
					$send['date_format']='dd-mm-yyyy';
					$send['hour_format']='24';
					$send['start_hour']='08:00';
					$send['end_hour']='23:00';
					$send['activity_view']='This Week';
					$send['lead_view']='Today';
					$send['reminder_interval']='1 Hour';
					$send['theme']='bluelagoon';
					$send['language']='it_it';
					$send['time_zone']='Europe/Rome';
					$send['reminder_interval']='1 Hour';
					$send['currency_id']='21x1';// ws_entity
					$send['currency_grouping_pattern']='123,456,789';
					$send['currency_decimal_separator']='.';
					$send['currency_grouping_separator']=' ';
					$send['currency_symbol_placement']='$1.0';
					$send['no_of_currency_decimals']='2';
					$send['truncate_trailing_zeros']='1';
					$send['dayoftheweek']='Monday';
					$send['callduration']='5';
					$send['othereventduration']='5';
					$send['calendarsharedtype']='public';
					$send['defaulteventstatus']='Planned';
					$send['defaultactivitytype']='Call';
					
					$send[$name]=$data[$key]; 
			} 
			print_r($send); 
						
			$record = $client->doCreate('Users',$send); 
			if($client->getRecordId($record['id'])== null) {
				$failed++;
				echo "Error during user creation:<BR>";
				print_r($client->lastError());
			}
			else{
				
				$inserted++;
				$recordid = $client->getRecordId($record['id']); 
				print_r($send);
				echo "User generated with code ". $recordid . PHP_EOL;
			}
			echo PHP_EOL;
		} 
	} 
	fclose($handle); 
}

echo "STATUS OF IMPORTING..." . PHP_EOL;
echo "INSERTED $inserted of $total" . PHP_EOL;
echo "FAILED $failed of $total" . PHP_EOL;












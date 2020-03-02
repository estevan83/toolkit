<?php
/**
 * This uses the SMTP class alone to check that a connection can be made to an SMTP server,
 * authenticate, then disconnect
 */

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

$server = 'smtp.gmail.com';
$port = 587;
$username = 'ticketprussiani@gmail.com';
$password ='Prussiani25062018';/*t1cketprussiani*/

$customer ='Prussiani CRM';
$subject = 'AlgomaCRM Test send mail';


require '../PHPMailerAutoload.php';

//Create a new SMTP instance
$smtp = new SMTP;

//Enable connection-level debug output
$smtp->do_debug = SMTP::DEBUG_CONNECTION;

$maxtimes = 3;

$errors = array();

for($i=0; $i<$maxtimes; $i++){
	echo "Tentativo $i";
	try {
		//Connect to an SMTP server
		if (!$smtp->connect($server, $port)) {
			throw new Exception('Connect failed');
		}
		//Say hello
		if (!$smtp->hello(gethostname())) {
			throw new Exception('EHLO failed: ' . $smtp->getError()['error']);
		}
		//Get the list of ESMTP services the server offers
		$e = $smtp->getServerExtList();
		//If server can do TLS encryption, use it
		if (is_array($e) && array_key_exists('STARTTLS', $e)) {
			$tlsok = $smtp->startTLS();
			if (!$tlsok) {
				throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
			}
			//Repeat EHLO after STARTTLS
			if (!$smtp->hello(gethostname())) {

				throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
			}
			//Get new capabilities list, which will usually now include AUTH if it didn't before
			$e = $smtp->getServerExtList();
		}
		//If server supports authentication, do it (even if no encryption)
		if (is_array($e) && array_key_exists('AUTH', $e)) {
			if ($smtp->authenticate($username, $password)) {
				echo "Connected ok!";
				// forza uscita
				$i = $maxtimes;
			} else {
				throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
			}
		}
	} catch (Exception $e) {
		$errors[] = $e->getMessage();
		sleep(2);
	}
}

if(count($errors)>0){
	$msg = "";
	for($j=0; $j<count($errors);$j++){
		$msg .= "Tentativo ". ($j+1) . " : ". $errors[$j] . PHP_EOL;
	}
	telegramLog($customer, $subject, $msg, "\n");
}
//Whatever happened, close the connection.
$smtp->quit(true);

echo "Fine dello script";

function telegramLog($customer, $subject, $message){
	//API Url
	$url = 'https://bot.algoma.it/algoma/wssend.php?secret=AAGsEe3gQEv_kT7ElcVk3_CNhCCL7xPZO7';
	 
	//Initiate cURL.
	$ch = curl_init($url);
	
	$breaks = array("<br />","<br>","<br/>");  
    $message = str_ireplace($breaks, "\r\n", $message);  
	$message =	strip_tags($message);
	 
	//The JSON data.
	$jsonData = array(
		'customer' => $customer,
		'title' => $subject,
		'message' => $message,
		'secret' => 'alfaomega'
	);
	 
	//Encode the array into JSON.
	$jsonDataEncoded = json_encode($jsonData);
	 
	//Tell cURL that we want to send a POST request.
	curl_setopt($ch, CURLOPT_POST, 1); 
	//Attach our encoded JSON string to the POST fields.
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
	 
	//Set the content type to application/json
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
	 
	//Execute the request
	$result = curl_exec($ch);
}


<?php

//to Hide All Errors:

error_reporting(0);
ini_set('display_errors', 0);

//to Show All Errors:
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';



echo "-------------------------------------------------";
echo "Algoma Toolkit CUSTOMER PORTAL Password generator";
echo "-------------------------------------------------";

$password  = Vtiger_Functions::generateRandomPassword();
$enc_password = Vtiger_Functions::generateEncryptedPassword($password);


echo "Password = $password ";
echo "Enc password  = $enc_password  ";
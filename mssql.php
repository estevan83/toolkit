<?php
$myServer = "XXXXX:1433";
$myUser = "";
$myPass = "";
$myDB = "";

$dbhandle = mssql_connect($myServer, $myUser, $myPass)  or die(mssql_get_last_message()); 

print_r($dbhandle);
die();
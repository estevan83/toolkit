<?php
die("UTILIZZARE FILE imapTest.php");
// phpinfo();
// die();
/* connect to gmail */
/* $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'colloquiatt@gmail.com';
$password = 'ttcolloquia2019'; */


// http://testcrm.ribo.it/		imapTest.php?host={imap.gmail.com:993/imap/ssl}INBOX&username=algomatest2@gmail.com&password=Alg0matest
// imapTest.php?host={10.0.0.13:993}INBOX&username=ribo\crmlead&password=R1b02019!Algoma
$hostname = $_GET["host"];
$username = $_GET["username"];
$password = $_GET["password"];

/*
$hostname = '{10.0.0.13:993/imap/ssl/novalidate-cert}INBOX'; // -> Cannot connect to Gmail: [CLOSED] IMAP connection broken (server response) 
$hostname = '{10.0.0.13:993/imap}INBOX';
$hostname = '{10.0.0.13:143/imap}INBOX'; 
$username = 'ribo\crmlead';
$password = 'R1b02019!Algoma';*/
/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox,'ALL');

/* if emails are returned, cycle through each... */
if($emails) {
  
  /* begin output var */
  $output = '';
  
  /* put the newest emails on top */
  rsort($emails);
  
  /* for every email... */
  
  echo count($emails);
} 

/* close the connection */
imap_close($inbox);
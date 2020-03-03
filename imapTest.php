
<html>
<body>

<form action="imapTest.php" method="post">
E-mail: <input type="text" name="email" value="<?php echo (isset($_GET['email']) ? $_GET['email'] : '')?>"><br>
Password: <input type="password" name="password" value="<?php echo (isset($_GET['password']) ? $_GET['password'] : '')?>"><br>
Server: <input type="text" name="host" value="<?php echo (isset($_GET['host']) ? $_GET['host'] : '')?>"><br>
Porta: <input type="text" name="port" value="<?php echo (isset($_GET['port']) ? $_GET['port'] : '')?>"><br>
<input type="hidden" name="via" value="993">
<input type="submit">
</form>

</body>
</html>
<?php
if ((empty($_POST["email"]) && empty($_GET["email"])) || (empty($_POST["password"]) && empty($_GET["password"])) || (empty($_POST["host"]) && empty($_GET["host"])) || (empty($_POST["port"]) && empty($_GET["port"]))){
	die();
}

echo ("PROVA CONNESSIONE ...");
echo ("<br>");
	
/*
$hostname = '{outlook.office365.com:993/imap/ssl}INBOX';
$username = 'giovanni.ongaro@omca.it';
$password = 'Benvenuto2019$';
*/
$hostname = '{'.$_POST["host"].':'.$_POST["port"].'/imap/ssl}INBOX';     // '{outlook.office365.com:993/imap/ssl}INBOX';
$username = $_POST["email"];
$password = $_POST["password"];

echo ("SERVER");
echo ("<br>");
echo ($hostname);
echo ("<br>");
echo ("USERNAME");
echo ("<br>");
echo ($username);
echo ("<br>");
echo ("PASSWORD");
echo ("<br>");
echo ($password);
echo ("<br>");
/*
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'algomacrm@gmail.com';
$password = 'alg0macrm';*/
/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to '.$hostname.': ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox,'ALL');
die("<h1>Connesso</h1>");
/* if emails are returned, cycle through each... */
if($emails) {
  
  /* begin output var */
  $output = '';
  
  /* put the newest emails on top */
  rsort($emails);
  
  /* for every email... */
  foreach($emails as $email_number) {
	
	/* get information specific to this email */
	$overview = imap_fetch_overview($inbox,$email_number,0);
	$message = imap_fetchbody($inbox,$email_number,2);
	
	/* output the email header information */
	$output.= '<div class="toggler '.($overview[0]->seen ? 'read' : 'unread').'">';
	$output.= '<span class="subject">'.$overview[0]->subject.'</span> ';
	$output.= '<span class="from">'.$overview[0]->from.'</span>';
	$output.= '<span class="date">on '.$overview[0]->date.'</span>';
	$output.= '</div>';
	
	/* output the email body */
	$output.= '<div class="body">'.$message.'</div>';
  }
  
  echo $output;
} 

/* close the connection */
imap_close($inbox);





<html>
<body>

<form action="imapTest.php" method="post">
E-mail from: <input type="text" name="emailfrom" value="<?php echo (isset($_GET['emailfrom']) ? $_GET['emailfrom'] : '')?>"><br>
Password: <input type="password" name="password" value="<?php echo (isset($_GET['password']) ? $_GET['password'] : '')?>"><br>
E-mail to: <input type="text" name="emailto" value="<?php echo (isset($_GET['emailto']) ? $_GET['emailto'] : '')?>"><br>
Server: <input type="text" name="host" value="<?php echo (isset($_GET['host']) ? $_GET['host'] : '')?>"><br>
Porta: <input type="text" name="port" value="<?php echo (isset($_GET['port']) ? $_GET['port'] : '')?>"><br>
<input type="hidden" name="via" value="993">
<input type="submit">
</form>

</body>
</html>


<?php

if ((empty($_POST["emailfrom"]) && empty($_GET["emailfrom"])) || (empty($_POST["emailto"]) && empty($_GET["emailto"])) || (empty($_POST["password"]) && empty($_GET["password"])) || (empty($_POST["host"]) && empty($_GET["host"])) || (empty($_POST["port"]) && empty($_GET["port"]))){
	die();
}


/**
 * This example shows making an SMTP connection with authentication.
 */
//die("aa");
//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require '../PHPMailerAutoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer;
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 2;
//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';
//Set the hostname of the mail server
// $mail->Host = "email-smtp.eu-central-1.amazonaws.com";
$mail->Host = $_POST['host'];
//Set the SMTP port number - likely to be 25, 465 or 587
// $mail->Port = 587;
$mail->Port = $_POST['port'];;
//$mail->SMTPSecure = false;
$mail->SMTPAutoTLS = true;

//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = "AKIAWHMCKGIRHRIM66ED";
//Password to use for SMTP authentication
// $mail->Password = "BCc6rAvcmn+dzE30CnqYOd4UQDcOTonxJj7cgXPy9Gt4";
$mail->Password = $_POST['password'];
//Set who the message is to be sent from
// $mail->setFrom("noreply@oneonly.it", "noreply@oneonly.it");
$mail->setFrom($_POST['emailfrom'], $_POST['emailfrom']);
//Set an alternative reply-to address
//$mail->addReplyTo('replyto@example.com', 'First Last');
//Set who the message is to be sent to
// $mail->addAddress('estefan.civera@gmail.com', 'Civera Estefan');
$mail->addAddress($_POST['emailto'], $_POST['emailto']);

//Set the subject line
$mail->Subject = 'Algoma CRM - Messaggio di test per verifica delle credenziali';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
//Replace the plain text body with one created manually
$mail->AltBody = 'Messaggio di test per verifica delle credenziali';
//Attach an image file
$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}

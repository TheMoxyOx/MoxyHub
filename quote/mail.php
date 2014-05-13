<?php
require_once 'mail/lib/swift_required.php';
include_once('jcart/jcart/jcart.php');

//generate email
ob_start(); // Start output buffering
echo "<style>
 jcart-paypal-checkout {display:none;}
 </style>";

echo $_GET["firstName"]."<br>".$_GET["lastName"]."<br>".$_GET["company"]."<br>".$_GET["new"]."<br>".$_GET["address1"]."<br>".$_GET["address2"]."<br>".$_GET["city"]."<br>".$_GET["state"]."<br>".$_GET["zip"]."<br>".$_GET["phone"]."<br>".$_GET["email"]."<br>";
			$jcart->display_cart();
			$email = ob_get_contents(); // Store buffer in variable

			ob_end_clean(); // End buffering and clean up


		


// Create the Transport
$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
  ->setUsername('sprucefeinstein@gmail.com')
  ->setPassword('Feinstein12345')
  ;

/*
You could alternatively use a different transport such as Sendmail or Mail:

// Sendmail
$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

// Mail
$transport = Swift_MailTransport::newInstance();
*/

// Create the Mailer using your created Transport
$mailer = Swift_Mailer::newInstance($transport);




// Create a message
$message = Swift_Message::newInstance('New Order')
  ->setFrom(array('sprucefeinstein@gmail.com' => 'Spruce Feinstein'))
  ->setTo(array('sprucefeinstein@gmail.com' => 'Spruce Feinstein'))
  ->setBody($email, 'text/html')
  ;

// Send the message
$result = $mailer->send($message);

?>
blah blah
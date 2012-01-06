<?php 
session_start(); 
include_once('mailer.php'); 
echo sendEmail();
$mess='';
function sendEmail () {
	$email = ''; 
	$admin_email = ''; //email to
	if ( validate( $mess ) ) {
		$name  = stripcslashes( $_REQUEST['name'] ); 
		$user_email =  stripcslashes( $_REQUEST['email']);
		$user_phone =  stripcslashes( $_REQUEST['phone']); 
		$user_aphone =  stripcslashes( $_REQUEST['aphone']); 
		$time =  stripcslashes( $_REQUEST['time']);
		$day =  stripcslashes( $_REQUEST['day']);
		$windows =  stripcslashes( $_REQUEST['windows']);
		$doors =  stripcslashes( $_REQUEST['doors']);
		$email .= '<p> <b>Phone:</b> ' . $user_phone. '<br /> </p>';      
		$email .= '<p> <b>Alternative Phone:</b> ' . $user_aphone. '<br /> </p>';      
		$email .= '<p> <b>Contact me at:</b> ' . $time . ' <b>on: </b> ' . $day . '</p> '; 
		$email .= '<p> <b>No. of Windows:</b> ' . $windows . '<br /></p> '; 
		$email .= '<p> <b>No. of Doors: </b>' . $doors . '<br /></p> '; 
		$email .= '<p> <b> Name: </b> ' . $name . '<br /></p>';
		$email .= '<p> <b> Email: </b> ' . $user_email . '<br /> </p>'; 
		$htmlMail = new Mailer(); 
		$htmlMail->addAddrFromDelimString($admin_email);
		$htmlMail->addHtml($email);
		$htmlMail->buildMessage($name." <".$user_email.">", $codePage = 'us-ascii', $contentType = 'text/html');
		$subject = 'New Contact Us request from '. $name;
		// --- Triyng To send Email --- //
		if (!$htmlMail->send('', $subject))   {
			$mess = '1 ## Couldn\'t send your request now. Please try again later.' ;
		} else {
			$mess = '1 ## Your request has been successfully sent.  Thank you.';
		}
		include('func.php');
		$cid = webRequest($name,$user_phone,$user_aphone,$windows,$doors,$time,$day,$user_email);
		sendAnalytics($cid);
	}
	return $mess;
}
    
function validate ( &$mess ){
	$rez = true; 
	$REGEX_EMAIL = '|\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*|is' ;
	$REGEX_PHONE = '/^[0-9\+\(\)\-\.\s]{10,}$/'; 
	$mess = ''; 
	if  (isset($_REQUEST['name'] ) ) { 
		$name  = stripcslashes( $_REQUEST['name']);
		if ( strlen( trim ($name) ) < 2 ){
			$rez = false;
			$mess = '0 ## Name has to be at least 2 characters. ';
		}
	} else {
		  $rez = false;  
		  $mess .= '0 ## Please provide your name.';
	} 
	if  (isset($_REQUEST['email'] ) ) { 
	   $email  = stripcslashes( $_REQUEST['email']); 
	   if (!preg_match($REGEX_EMAIL, $email))
		{  
			$rez = false;
			$mess .= '0 ## Email address is incorrect. ';
		} 
	}else {
			  $rez = false;  
			  $mess .= '0 ## Please provide email.';
	}
	if  (isset($_REQUEST['phone'] ) ) { 
	   $phone  = stripcslashes( $_REQUEST['phone']); 
	   if (!preg_match($REGEX_PHONE, $phone))
		{  
			$rez = false;
			$mess .= '0 ## Phone number is incorrect. ';
		} 
	}else {
			  $rez = false;  
			  $mess .= '0 ## Please provide phone number.';
	}
	return $rez;   
}
?>



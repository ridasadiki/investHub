<?php
//https://harvesthubmw.000webhostapp.com/ussd/components/sms.php
include_once 'db.php';
include_once 'util.php';
include_once 'components/sms.php';
include_once 'components/user.php';

$phoneNumber = $_POST["from"];
$text = $_POST["text"];

$user = new User($phoneNumber);
$db = new DBConnector();
$pdo = $db->connectToDB();

$text = explode(",", $text);
$user->setProductName($text[0]);
$user->setProductUnit($text[1]);
$user->setProductPrice($text[2]);
$user->setProductQuantity($text[3]);

$user->submitProduct($pdo);
$msg = "Thank you for submitting your product for review.\rYour product details are:\r"
    . "Product Name: " . $text[0] . "\r"
    . "Unit: " . $text[1] . "\r"
    . "Price (MWK): " . $text[2] . "\r"
    . "Quantity: " . $text[3] . "\r";

$sms = new Sms($user->getPhone());
$result = $sms->sendSMS($msg);

/*
if (!$user->isUserRegistered($pdo)) {
$msg = "You Are Not Registered. Please Register To
Enjoy Our Services ";
$sms = new Sms($user->getPhone());
$result = $sms->sendSMS($msg);
} else {

$user->submitProduct($pdo);
$msg = "Thank you for submitting your product for review.\rYour product details are:\r"
. "Product Name: " . $text[0] . "\r"
. "Unit: " . $text[1] . "\r"
. "Price (MWK): " . $text[2] . "\r"
. "Quantity: " . $text[3] . "\r";

$sms = new Sms($user->getPhone());
$result = $sms->sendSMS($msg);
}

 */

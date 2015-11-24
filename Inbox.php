<?php
header('Content-Type: text/html; charset=utf-8');

include('FB.php');

$content = file_get_contents('list.txt');
$j = explode(',', $content);

$index = $_GET['TxtIndex'];

$number = $j[$index];

$_GET['TxtNumber'] = trim($number);

try{
	$fb = new FB(new Curl, $_GET['TxtAccount'], $_GET['TxtPassword']);
	$fb->send($_GET['TxtNumber'], $_GET['TxtMessage'].' '.$index);
}catch(Exception $e){
	echo $e->getMessage();
}
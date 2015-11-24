<?php

include('FB.php');

try{
	$fb = new FB(new Curl, $_GET['TxtAccount'], $_GET['TxtPassword']);
	$fb->login();
}catch(Exception $e){
	echo $e->getMessage();
}
<?php

include('FB.php');

$handler = $_GET['TxtFunction'];

try{
	$fb = new FB(new Curl, $_GET['TxtAccount'], $_GET['TxtPassword']);

	$fb->login();

	if(isset($_GET['Param2']))
	{
		$fb->$handler($_GET['TxtID'], $_GET['Param'], $_GET['Param2']);
	}else{
		$fb->$handler($_GET['TxtID'], $_GET['Param']);

	}
}catch(Exception $e){
	echo $e->getMessage();
}

exit;
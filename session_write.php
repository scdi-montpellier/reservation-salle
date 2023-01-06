<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
	
session_start();

if (isset($_GET['variable']) && isset($_GET['valeur']) )
{
	
	$name = $_GET['variable'];
	$valeur  = $_GET['valeur'];
	
	
	if (substr($name,0,strlen("description")) == "description")
		$_SESSION["$name"] = $valeur ;
	
	if (substr($name,0,strlen("info")) == "info")
		$_SESSION["$name"] = $valeur ;
	
	// var_dump($_SESSION);
}

?>
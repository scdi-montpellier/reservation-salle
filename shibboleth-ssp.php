<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	session_start();
	
	include_once("config.php");
	
	include ("fonction.php");
	
	if (substr(strtolower(get_full_url()),0,5)!="https")
		 die ("Veuillez accéder à cette page en https");

	$idp="";
	if (isset($_POST["idp"])) 
		$idp=strtolower($_POST["idp"]);
	
	if (trim($idp)=="")
		die("L'idp est manquant, impossible de continuer...");
	
	$idp_trouve = false;
	
	foreach ($gIdp as $key => $value){
		if ($value['id']==$idp)
		{
			$idp_trouve = true;
			break;
		}
	}
	
	if (!$idp_trouve)
		die("L'IDP '$idp' n'est pas dans la liste des IDP autorisés, impossible de continuer...");
	
	if (!$gIdp[$idp]['active'])
		die("L'authentification via '".$gIdp[$idp]['text']."' n'est pas encore disponible.");
	
	$codebu="";
	if (isset($_POST["codebu"])) 
		$codebu=strtoupper($_POST["codebu"]);
	
	$mmsid="";
	if (isset($_POST["mmsid"])) 
		$mmsid=strtoupper($_POST["mmsid"]);
	
	$holdingid="";
	if (isset($_POST["holdingid"])) 
		$holdingid=strtoupper($_POST["holdingid"]);
	
	$itemid="";
	if (isset($_POST["itemid"])) 
		$itemid=strtoupper($_POST["itemid"]);
	
	$cb="";
	if (isset($_POST["cb"])) 
		$cb=strtoupper($_POST["cb"]);
	
	$debut="";
	if (isset($_POST["debut"])) 
		$debut=strtoupper($_POST["debut"]);
	
	$fin="";
	if (isset($_POST["fin"])) 
		$fin=strtoupper($_POST["fin"]);
								
	require_once($gCheminSSP.'lib/_autoload.php');
	
	$saml_auth = new \SimpleSAML\Auth\Simple($gNomSP);
	
	$attributes = array();
	
	if ($saml_auth->isAuthenticated()) {
		$attributes = $saml_auth->getAttributes();    
	}
	else {
		
		$saml_auth->login(array(
				'saml:idp' => $gIdp[$idp]['server'],
			));
		
		$saml_auth->requireAuth();
	}
	
	$session = SimpleSAML_Session::getSessionFromRequest();
	$session->cleanup();
	
	// echo "idp : ".$idp."<br/>";
	// echo "module : ".$module."<br/>";
	// echo "uid : ".$attributes["uid"][0]."<br/>";
	// echo "eppn : ".$attributes["eduPersonPrincipalName"][0]."<br/>";
	
	// echo "sn : ".$attributes["sn"][0]."<br/>";
	// echo "givenname : ".$attributes["givenName"][0]."<br/>";
	// echo "mail : ".$attributes["mail"][0]."<br/>";
	
	// $eduPersonAffiliation = "";
	// foreach ($attributes["eduPersonAffiliation"] as &$value) {
		// $eduPersonAffiliation .= $value."-";
	// }
	// if ($eduPersonAffiliation!="")
		// $eduPersonAffiliation = substr($eduPersonAffiliation,0,-1);
	
	// echo "eduPersonAffiliation : ".$eduPersonAffiliation."<br/>";
	
	// echo "Session-ID : ".$attributes["eduPersonTargetedID"][0]."<br/>";
	
	// récupération des informations utiles pour la réservation
	$uid = "";  // ne sert pas
	$eppn = "";
	$mail = "";  // ne sert pas
	
	// récupération de l'uid // ne sert pas
	// if (isset($attributes["uid"][0]))   
		// $uid =  $attributes["uid"][0]; 

	// if (trim($uid)=="") // ne doit pas arriver
		// die ("Erreur d'authentification avec l'idp, l'attribut uid est vide; veuillez contacter l'administrateur du site internet.");
	
	// récupération de l'eppn
	if (isset($attributes["eduPersonPrincipalName"][0])) 
		$eppn = $attributes["eduPersonPrincipalName"][0];

	if (trim($eppn)=="") // ne doit pas arriver
		die ("Erreur d'authentification avec l'idp, l'attribut eppn est vide; veuillez contacter l'administrateur du site internet.");
	
	if (isset($attributes["mail"][0])) // ne sert pas
		$mail = strtolower($attributes["mail"][0]);
	
	$eppnc = "";
	$eppnc = urlsafe_b64encode(cryptText($eppn, $gKey));
	
	$dvc = "";
	$dvc = urlsafe_b64encode(cryptText(strtotime("now"), $gKey));
	
	$resa = "";
	$resa = urlsafe_b64encode(cryptText($codebu."¤".$mmsid."¤".$holdingid."¤".$itemid."¤".$cb."¤".$debut."¤".$fin, $gKey));
	
	$provenance = $bu[$codebu]['campus'];
	
	header("location: ".$gURLs."confirmation.php?eppnc=$eppnc&dvc=$dvc&resa=$resa&provenance=$provenance");	
?>
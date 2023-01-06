<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);
	session_start();

	include ("config.php");
	include ("fonction.php");
	
	$reponse = "KO";
	$message = "";
	$array['nom'] = "";
	$array['mail'] = "";
	$debug = "";
	
	$eppnc = "";
	
	if (isset($_POST["eppnc"])) 
		$eppnc=$_POST["eppnc"];
		
	$nomalma = "";
	$mailalma = "";
	$nomalmac ="";
	$mailalmac ="";
		
	if (trim($eppnc) != "" && strlen($eppnc) > 3)
	{
		// on decrypte l'eppnc
		$eppntmp = urlsafe_b64decode($eppnc);
		$eppn = decryptText($eppntmp,$gKey);

		// if (preg_match ('/^[A-Z0-9]+$/', $eppn) == 1 && strlen($eppn) > 3 )
		if (filter_var($eppn, FILTER_VALIDATE_EMAIL) && strlen($eppn) > 3 )
		{
			$url = "$gAdrAlma/almaws/v1/users/$eppn?apikey=$gTokenAlma&lang=fr";
			
			$xml = get_xml($url);
			
			if (!$xml==false)
			{
				$nomalma = get_name($xml);

				if ($nomalma!="")
				{
					
					$nomalmac = urlsafe_b64encode(cryptText($nomalma, $gKey));
					$mailalma = trim(get_mail_preferred($xml, false));
					
					if ($mailalma=="") // si pas de mail préféré, on prend le 1er de la liste
					{
						$mailalma = trim(get_first_mail($xml, false));
					}
					
					if ($mailalma!="")
					{
						if (filter_var($mailalma, FILTER_VALIDATE_EMAIL) && strlen($mailalma) > 3 )
						{
							
							$mailalmac = urlsafe_b64encode(cryptText($mailalma, $gKey));
							$reponse = "OK";
						}
						else
							$message .= "<i class='fa fa-times' aria-hidden='true'></i> Le compte lecteur '$eppn' ne comporte pas de mail correct ($mailalma). Impossible de continuer. Veuillez contacter votre bibliothèque.";
					}
					else
						$message .= "<i class='fa fa-times' aria-hidden='true'></i> Le compte lecteur '$eppn' ne comporte pas de mail. Impossible de continuer. Veuillez contacter votre bibliothèque.";
				}
				else
					$message .= "<i class='fa fa-times' aria-hidden='true'></i> Le compte lecteur '$eppn' ne comporte pas de nom. Impossible de continuer. Veuillez contacter votre bibliothèque.";
			}
			else
				$message .= "<i class='fa fa-times' aria-hidden='true'></i> L'identifiant compte lecteur '$eppn' n'existe pas. Impossible de continuer. Veuillez contacter votre bibliothèque.";
		}
		else // on ne devrait jamais passer ici !
			$message .= "<i class='fa fa-times' aria-hidden='true'></i> Impossible de lire les informations du compte lecteur, votre identifiant crypté n'est pas correct.";
	}
	else // on ne devrait jamais passer ici !
		$message .= "<i class='fa fa-times' aria-hidden='true'></i> Impossible de lire les informations du compte lecteur, le paramètre eppnc est incorrect.";
	
	$array['nom'] = $nomalma;
	$array['mail'] = $mailalma;
	
	$array['nomc'] = $nomalmac;
	$array['mailc'] = $mailalmac;
	
	$array['reponse'] = $reponse;
	$array['message'] = $message;
	$array['debug'] = $debug;
	
	echo json_encode($array);
	
?>
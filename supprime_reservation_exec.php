<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);
	session_start();

	include ("config.php");
	include ("fonction.php");
	
	$reponse = "KO";
	$message1 = "";
	$message2 = "";
	$debug = "";
	
	$eppnc = "";
	
	if (isset($_POST["eppnc"])) 
		$eppnc=$_POST["eppnc"];
	
	$requestidc = "";
	
	if (isset($_POST["requestidc"])) 
		$requestidc=$_POST["requestidc"];
		
	if (trim($eppnc) != "" && strlen($eppnc) > 3)
	{
		// on decrypte l'eppnc
		$eppntmp = urlsafe_b64decode($eppnc);
		$eppn = decryptText($eppntmp,$gKey);

		// if (preg_match ('/^[A-Z0-9]+$/', $eppn) == 1 && strlen($eppn) > 3 )
		if (filter_var($eppn, FILTER_VALIDATE_EMAIL) && strlen($eppn) > 3 )
		{
			
			// on decrypte le requestidc
			$requestidtmp = urlsafe_b64decode($requestidc);
			$requestid = decryptText($requestidtmp,$gKey);
			
			if ($requestid!="")
			{
				$ch = curl_init();
				$url = "$gAdrAlma/almaws/v1/users/$eppn/requests/$requestid";
				$queryParams = '?' . urlencode('reason') . '=' . urlencode('CancelledAtPatronRequest') . '&' . urlencode('note') . '=' . urlencode("Annulation demandée par ".$eppn." via l'application ".$gNomAppli) . '&' . urlencode('notify_user') . '=' . urlencode('true') . '&' .  urlencode('lang'). '='.  urlencode('fr'). '&' . urlencode('apikey') . '=' . urlencode($gTokenAlma);
				curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_HEADER, FALSE);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				$response = curl_exec($ch);
				curl_close($ch);
								
				if($response)
				{
					$xml_delete_result = simplexml_load_string($response);
					$error = (string) $xml_delete_result->errorsExist;
					if(strcmp($error, "true") == 0)
						{
							//start times are not equal, so use the udjusted time
							$message1 .=  "<i class='fa fa-times' aria-hidden='true'></i> Une erreur est apparue lors de l'annulation de la réservation N°" . $requestid;
							$errorDetail = (string) $xml_delete_result->errorList->error->errorMessage;
							$message1 .=  "<hr><i class='fa fa-exclamation-circle' aria-hidden='true'></i> L'erreur est : " . $errorDetail;
						}
				}
				else 
				{
					$reponse = "OK";
					$message1 .=  "<i class='fa fa-check-circle' aria-hidden='true'></i> La réservation N°" . $requestid . " a bien été annulée.";
					
					// on indique dans la BDD que la réservation est supprimé
					try {
						$dbh = new PDO('mysql:host='.$gaSql['server'].';dbname='.$gaSql['db'], $gaSql['user'], $gaSql['password']);
						
						$sql = "UPDATE demande_reservation_salle SET bsupprime=? WHERE uid=? AND requestid=?";
						$dbh->prepare($sql)->execute([1, $eppn, $requestid]);
						
						$dbh = null;
					} catch (PDOException $e) {
						$debug .= $e->getMessage();
					}
				}
				
				$message2 = "<i class='fa fa-home' aria-hidden='true'></i> Retour à la <a title=\"Retour à l'accueil\" href=\"$gURLs\">page d'accueil</a>.";
				
			}
			else
				$message1 .= "<i class='fa fa-times' aria-hidden='true'></i> Le numéro de réservation n'a pas été transmis. Impossible de continuer. Veuillez contacter votre bibliothèque.";
		}
		else // on ne devrait jamais passer ici !
			$message1 .= "<i class='fa fa-times' aria-hidden='true'></i> Impossible de lire les informations du compte lecteur, votre identifiant crypté n'est pas correct.";
	}
	else // on ne devrait jamais passer ici !
		$message1 .= "<i class='fa fa-times' aria-hidden='true'></i> Impossible de lire les informations du compte lecteur, le paramètre eppnc est incorrect.";
	
	$array['reponse'] = $reponse;
	$array['message1'] = $message1;
	$array['message2'] = $message2;
	$array['debug'] = $debug;
	
	echo json_encode($array);
	
?>
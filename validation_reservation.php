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
	$requestidc = "";
	$messagemail = "";
	
	$request_xml = "";
	
	$eppn = "";
	$eppnc = "";
	if (isset($_POST["eppnc"]))
		$eppnc=$_POST["eppnc"];	
	
	if ($eppnc != "") 
	{
		if ($eppnc!="" && strlen($eppnc) > 3)
		{
			$eppntmp = urlsafe_b64decode($eppnc);
			
			if ($eppntmp!="")
				$eppn = decryptText($eppntmp,$gKey);

			// on verifie que l'eppn est bien sous la forme d'un mail
			if (!(filter_var($eppn, FILTER_VALIDATE_EMAIL) && strlen($eppn) > 3 ))
				$message1 .= "<i class='fa fa-times-circle'></i> Impossible de lire les informations de la réservation, le paramètre eppn est vide ou incorrect.";
		}
		else
			$message1 .= "<i class='fa fa-times-circle'></i> Impossible de lire les informations de la réservation, le paramètre eppn est vide ou incorrect.";
		
	}
	else // on ne devrait jamais passer ici !
		$message1 .= "<i class='fa fa-times' aria-hidden='true'></i> Le paramètre eppn n'a pas été transmis.";
	
	if ($eppn!="")
	{
		$resac = "";
		if (isset($_POST["resac"]))
			$resac=$_POST["resac"];

		if ($resac != "") 
		{
			$resatmp = urlsafe_b64decode($resac);
									
			if ($resatmp!="")
				$resa = decryptText($resatmp,$gKey);
			
			if (strlen($resa)>10)
			{
				//RESA $codebu."¤".$mmsid."¤".$holdingid."¤".$itemid."¤"$cb."¤".$debut."¤".$fin
				$tabresa = explode('¤',$resa);
				
				$codebu = $tabresa[0];
				$mmsid = $tabresa[1];
				$holdingid = $tabresa[2];
				$itemid = $tabresa[3];
				$cb = $tabresa[4];
				$debut = $tabresa[5];
				$fin = $tabresa[6];
				
				$ddebut = gmdate('Y-m-d\TH:i:s\Z', strtotime($debut));
				$dfin = gmdate('Y-m-d\TH:i:s\Z', strtotime($fin));
				
				$request_date = gmdate('Y-m-d\TH:i:s\Z');

			  // Store XML Request object in Variable for passing to curl 
				$request_xml = '<?xml version="1.0" encoding="UTF-8"?>
				<user_request>
				  <author>'.$eppn.'</author>
				  <request_type>BOOKING</request_type>
				  <pickup_location>BU Richter</pickup_location>
				  <pickup_location_type>LIBRARY</pickup_location_type>
				  <pickup_location_library>'.$codebu.'</pickup_location_library>
				  <material_type>ROOM</material_type>
				  <comment>Réservé par application '.$gNomAppli.'</comment>
				  <item_id>'.$itemid.'</item_id>
				  <barcode>'.$cb.'</barcode>
				  <request_date>'.$request_date.'</request_date>
				  <booking_start_date>'.$ddebut.'</booking_start_date>
				  <booking_end_date>'.$dfin.'</booking_end_date>
				</user_request>';

				$ch = curl_init();
				$url = "$gAdrAlma/almaws/v1/users/$eppn/requests";
				$queryParams = '?' . urlencode('user_id_type') . '=' . urlencode('all_unique') . '&' . urlencode('mmsid') . '=' . $mmsid . '&' . urlencode('item_pid') . '=' . $itemid . '&' .  urlencode('lang'). '='.  urlencode('fr'). '&' . urlencode('apikey') . '=' . urlencode($gTokenAlma);
				curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_HEADER, FALSE);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request_xml);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
				$response = curl_exec($ch);
				curl_close($ch);
				
				if($response)
				{
					
					$result = get_reservation_alma_xml_from_string($response);
					
					$nomsalle = "";
					$descsalle = "";
					
					// on retrouve le nom de salle
					if (isset($bu[$codebu]['mmsid'][$mmsid]['holdingid'][$holdingid]['itemid'][$itemid]['nom']))
						$nomsalle = $bu[$codebu]['mmsid'][$mmsid]['holdingid'][$holdingid]['itemid'][$itemid]['nom'];
					
					if (isset($bu[$codebu]['mmsid'][$mmsid]['holdingid'][$holdingid]['itemid'][$itemid]['description']))
						$descsalle = $bu[$codebu]['mmsid'][$mmsid]['holdingid'][$holdingid]['itemid'][$itemid]['description'];

					
					if ($result['erreur']=="")
					{
						$message1 .= "<div style='font-weight:bold;'><i class='fa fa-calendar-check-o' aria-hidden='true'></i> Votre réservation a été validée !</div>";
						
						$message1 .= "<hr>";

						$message1 .= "<div>";
						$messagemail .= "<div>";
						
						// $message1 .= "Identifiant utilisé : $eppn<br/>";
						$message1     .= "Réservation à la ".$bu[$codebu]['nom'];
						$messagemail .= "<b>Votre réservation à la ".$bu[$codebu]['nom'];
						
						if ($nomsalle!="") 
						{
							$message1     .= " - $nomsalle - le ";
							$messagemail .= " - $nomsalle - le ";
						}
						else
						{
							$message1     .= " - le ";
							$messagemail .= " - le ";
						}
							
						$ddebuttxt = date('d/m/Y \d\e H:i', strtotime($result['debut']));
						$dfintxt = date('H:i', strtotime($result['fin']));
						
						$message1    .= "$ddebuttxt à $dfintxt";
						
						$messagemail .= "$ddebuttxt à $dfintxt  est confirmée</b>";

						if ($result['ajustdebut']==1 || $result['ajustfin']==1)
						{
							$message1     .= " (attention les horaires ont été modifiés par le système pour correspondre aux heures d'ouverture/fermeture de la BU)";
							$messagemail .= " (attention les horaires ont été modifiés par le système pour correspondre aux heures d'ouverture/fermeture de la BU)";
						}
						
						if ($descsalle!="") 
						{
							$messagemail .= "<div style='margin-top:5px;'>Description de la salle :</div>";
							$messagemail .= "<div  style='margin-left:5px;'>$descsalle</div>";
						}
						
						$messagemail .= "<div style='margin-top:5px;'>N° de réservation : ".$result['requestid']."</div>";
						
						$message1 .= "</div>";
						
						$message1 .= "<div>N° de réservation : ".$result['requestid']."</div>";
						
						// $message1 .= "<div> ".$result['message']."</div>";
						
						// $message2 .= "<div><i class='fa fa-question-circle' aria-hidden='true'></i> Veuillez recommencer votre réservation depuis la <a title=\"Retour à l'accueil\" href=\"$gURLs\">page d'accueil</a> en suivant les consignes indiquées si nécessaire.</div>";
						
						
						$requestidc = urlsafe_b64encode(cryptText($result['requestid'], $gKey));
						
						$reponse = "OK";
					}
					else
					{
						$message1 .= "<div style='font-weight:bold;'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> Malheureusement votre réservation n'a pas pu être validée !</div>";
						
						$message1 .= "<hr>";
						
						$message1 .= "<div style='font-weight:bold;'><i class='fa fa-info-circle' aria-hidden='true'></i> La réservation concernait :</div>";
						
						$message1 .= "<div>";
						// $message1 .= "Identifiant utilisé : $eppn<br/>";
						$message1 .= "La ".$bu[$codebu]['nom'];
						
						if ($nomsalle!="")
							$message1 .= " - $nomsalle - le ";
						
						$ddebuttxt = date('d/m/Y \d\e H:i', strtotime($debut));
						$dfintxt = date('H:i', strtotime($fin));
						
						$message1 .= "$ddebuttxt à $dfintxt";

						$message1 .= "</div>";
						
						$message1 .= "<hr>";
						
						$message1 .= "<div style='font-weight:bold;'><i class='fa fa-exclamation-circle' aria-hidden='true'></i> Erreur :</div>";
						$message1 .= "<div>".$result['erreur']."</div>";
						
						$message2 .= "<div><i class='fa fa-question-circle' aria-hidden='true'></i> Veuillez recommencer votre réservation depuis la <a title=\"Retour à l'accueil\" href=\"$gURLs\">page d'accueil</a> en suivant les consignes indiquées si nécessaire.</div>";
						
					}
					
					// on indique dans la BDD que la réservation est OK ou pas
					// on insere dans la table la demande
					try {
						$dbh = new PDO('mysql:host='.$gaSql['server'].';dbname='.$gaSql['db'], $gaSql['user'], $gaSql['password']);
						
						if ($reponse == "OK")
						{
							$sql = "INSERT INTO demande_reservation_salle (uid, date_demande, date_debut, date_fin, itemid,cb,requestid) VALUES (?,?,?,?,?,?,?)";
							$dbh->prepare($sql)->execute([$eppn, date('Y-m-d H:i:s'),date('Y-m-d H:i:s', strtotime($result['debut'])), date('Y-m-d H:i:s', strtotime($result['fin'])), $itemid, $cb, $result['requestid'] ]);
						}
						else
						{
							$sql = "INSERT INTO demande_reservation_salle (uid, date_demande, date_debut, date_fin, itemid,cb,requestid, erreur) VALUES (?,?,?,?,?,?,?,?)";
							$dbh->prepare($sql)->execute([$eppn, date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime($debut)), date('Y-m-d H:i:s', strtotime($fin)), $itemid, $cb, $result['requestid'], strip_tags(utf8_decode(html_entity_decode($result['erreur']))) ]);
						
						}

						$dbh = null;
					} catch (PDOException $e) {
						$debug .= $e->getMessage();
					}
				}
				else // on ne doit jamais passer ici !
					$message1 .= "<div><i class='fa fa-calendar-times-o' aria-hidden='true'></i> La réservation n'a pas pu être validée. Erreur inconnue</div>";	
			}
			else // on ne doit jamais passer ici !
				$message1 .= "<i class='fa fa-times-circle'></i> Impossible de lire les informations de la réservation, le paramètre resa est vide ou incorrect.";
		}
		else // on ne devrait jamais passer ici !
			$message1 .= "<i class='fa fa-times' aria-hidden='true'></i> La réservation n'a pas été transmise correctement. $resac / $nom";
	}
					
	$array['reponse'] = $reponse;
	$array['message1'] = $message1;
	$array['message2'] = $message2;
	$array['messagemail'] = $messagemail;
	$array['requestidc'] = $requestidc;
	$array['debug'] = $debug;
	
	echo json_encode($array);
	
?>
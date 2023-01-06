<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	session_start();

	include ("config.php");
	include ("fonction.php");
	
	$reponse = "KO";
	$message = "";
	$debug = "";
	
	if (isset($_POST["codebu"]))
		$codebu=$_POST["codebu"];
	else
		$codebu = "";
	
	if (isset($_POST["jour"]))
		$jour=$_POST["jour"];
	else
		$jour = "";
	
	if (isset($_POST["page"]))
		$page=$_POST["page"];
	else
		$page = 1;
	
	if (isset($_POST["filtre"]))
		$filtre=$_POST["filtre"];
	else
		$filtre = "";
	
	if (isset($_SESSION["initdate"]))
	{
		if ($_SESSION["initdate"]!="")
		{
			$initdate = date("Y-m-d H:i:s", strtotime($_SESSION['initdate']));
			$date_actuelle = date("Y-m-d H:i:s", strtotime($_SESSION['initdate']));
		}
		else
			$initdate = "";
	}
	else
		$initdate = "";
	
	if ($initdate == "")
	{	
		$date_actuelle = date("Y-m-d H:i:s");
	}
	
	if ($codebu != "" && $jour != "") 
	{
		$array['codebu'] = $codebu;
		$array['nombu'] = $bu[$codebu]['nom'];
		$array['jour'] = date("Y-n-j",strtotime($jour));
		
		$array['btn_critere'] = "<div id='dropdown_critere_salle' data-valeur='$filtre'></div>";

		$tfiltre= array();

		// on cherche l'ensemble des filtres pour la bu en cours
		foreach ($bu[$codebu]['mmsid'] as $keym => $valuem)
		{
			foreach ($valuem['holdingid'] as $keyh => $valueh)
			{
				foreach ($valueh['itemid'] as $keyi => $valuei)
				{
					array_push($tfiltre, $valuei["categorie"]);
				}
			}
		}

		$tfiltre = array_unique($tfiltre);
		sort($tfiltre);

		if ($filtre != "")
		{
			// on filtre le json des bu avec la catégorie
			foreach ($bu[$codebu]['mmsid'] as $keym => $valuem)
			{
				foreach ($valuem['holdingid'] as $keyh => $valueh)
				{
					foreach ($valueh['itemid'] as $keyi => $valuei)
					{
						if ($valuei["categorie"] != $filtre)
						{
							// suppression de l'item
							unset($bu[$codebu]['mmsid'][$keym]['holdingid'][$keyh]["itemid"][$keyi]);
						}

					}
					
					// suppression de la holding si plus d'item dans la holding
					if (count($bu[$codebu]['mmsid'][$keym]['holdingid'][$keyh]["itemid"]) < 1)
						unset($bu[$codebu]['mmsid'][$keym]['holdingid'][$keyh]);
				}
				
				// suppression du mms si plus de holging dans le mms
				if (count($bu[$codebu]['mmsid'][$keym]['holdingid']) < 1)
					unset($bu[$codebu]['mmsid'][$keym]);
			}
			
		}

		// on compte le nombre de mms...
		$nbmms = count($bu[$codebu]['mmsid']);

		// on compte le nombre de page...
		$nbpage = ceil($nbmms / $gNbMmsParPage);
		$pagesuivante = $page+1;
		$pageprecedente = $page-1;
		
		$creneau_min = $bu[$codebu]['creneau_min'];
		$creneau_max = $bu[$codebu]['creneau_max'];
		
		$jour_complement = "";
		
		$jour_complement = $joursemaine[date("N",strtotime($jour))];
		
		// détection du passage 23hxx->0h : on change de jour, très peu de chance que ça arrive !
		// il vaut mieux rafraichir la page même si en général a cette heure si la BU est ferméee et donc l'onglet aujourd'ui n'affiche aucun créneau...
		if (date("Y-m-d",strtotime($date_actuelle)) != date("Y-m-d",strtotime(date("Y-m-d H:i:s"))) && $initdate == "") // on affiche pas le message en mode simulation de calendrier
			$message .= "<div class='alert alert-danger' role='alert'>Changement de jour détecté : veuillez rafraichir la page pour mettre à jour les onglets !</div>";
		
		// règle : si c'est fermé en général, ça ne peut pas être ouvert ! (lol)
		if ($bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['ouvert'] == 1)
		{
			if ($bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['reservable'] == 1)
			{

				$buencoreouverte = true;
				
				$raison_fermeture = "";
				
				// on cherche si la BU n'est pas fermée exceptionnellement
				foreach ($bu[$codebu]['fermeture_specifique'] as $key => $value) 
				{
					$dd = new DateTime(date("Y-m-d",strtotime($value['debut'])));
					
					$df = new DateTime(date("Y-m-d",strtotime($value['fin'])));
					
					$da = new DateTime(date("Y-m-d",strtotime($jour)));
					
					if (($da>=$dd) && ($da<=$df)) {
						$buencoreouverte = false;
						$raison_fermeture = $value['raison'];
						break;
					}
				}
				
				if ($buencoreouverte)
				{
					$heure_fermeture = "";
					
					if (date("d/m/Y",strtotime($date_actuelle))==date("d/m/Y",strtotime($jour)))
					{   //onglet sélectionné = aujourd'hui
						$d1 = new DateTime($date_actuelle);
						
						// on cherche si la BU n'est pas dans un horaire spécifique
						foreach ($bu[$codebu]['horaire_specifique'] as $key => $value) 
						{
							$dd = new DateTime(date("Y-m-d",strtotime($value['debut'])));
							
							$df = new DateTime(date("Y-m-d",strtotime($value['fin'])));
							
							$da = new DateTime(date("Y-m-d",strtotime($jour)));
							
							if (($da>=$dd) && ($da<=$df)) {
								$heure_fermeture = $value['fermeture'];
								break;
							}
						}

						if ($heure_fermeture == "") //heure de fermeture normale
							$heure_fermeture = $bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['fermeture'];
						
						$d2 = new DateTime($d1->format('Y-m-d ').$heure_fermeture);
						
						if ($d2->format('i')<30) //on arrondie à 00 minutes
							$d2 = new DateTime($d2->format('Y-m-d H').":00:00");
						
						if ($d2->format('i')>30 && $d2->format('i')<=59) //on arrondie à 30 minutes
							$d2 = new DateTime($d2->format('Y-m-d H').":30:00");
							
						// on cherche l'heure de fermeture - (nbcreneau min+1 * creneaux de 30 min) *** +1 à enlever si on redonne la possibilité de réserver le dernier créneau avant fermeture
						$nbminfinresa = ($creneau_min+1) * 30;
						date_sub($d2, date_interval_create_from_date_string("$nbminfinresa minutes"));
						
						// on vérifie que la bu n'est pas dans la dernière intervalle de creneau min avant fermeture sinon ça affiche toutes les salles sans disponibilité
						if ($d1>=$d2)
							$buencoreouverte = false;
					}
					
					if ($buencoreouverte)
					{

						if (!isset($_SESSION["info-1"]))
						{
							$message .= "<div id='info1' class='alert alert-warning alert-dismissible fade show' role='alert' role='alert'><i class=\"fa fa-info-circle\" aria-hidden=\"true\"></i> Voici les disponibilités pour la ".$array['nombu']." le $jour_complement ".date("d/m/Y",strtotime($jour))."<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>";
							$message .= "<SCRIPT>";
							$message .= "$('#info1').on('closed.bs.alert', function () {";
							$message .= "  masque_info(1);";
							// $message .= "  alert('close1');";
							$message .= "  });";
							$message .= "</SCRIPT>";
							$message .= "<div style=\"overflow: hidden\">";
						}

						$bookings = array();

						// on recherche les prêts en cours pour chaque salle... Attention, des prêt non rendu peuvent bloqué la salle (heure de fin indéfinie) !
						// if (date("d/m/Y",strtotime($date_actuelle))==date("d/m/Y",strtotime($jour)))
						if (true)
						{
							$cptmms = 1;
							
							foreach ($bu[$codebu]['mmsid'] as $keym => $valuem)
							{
								// filtrage affichage par page
								$minmms = (($page-1)*$gNbMmsParPage)+1;
								$maxmms = $minmms+$gNbMmsParPage;
								if (($cptmms >= $minmms) && ($cptmms < $maxmms))
								{
									foreach ($valuem['holdingid'] as $keyh => $valueh)
									{ 
										foreach ($valueh['itemid'] as $keyi => $valuei)
										{
											
											$identifiant_affichage = $keyi; // on peut mettre $keym à condition qu'il n'y est que 1 salle par notice...
											
											// utilisation api request par itemid (il pourrait y avoir plusieurs salles par mms / holding)...
											$url = "$gAdrAlma/almaws/v1/bibs/$keym/holdings/$keyh/items/$keyi/booking-availability?period=$gNbJourReservation&period_type=days&user_id_type=all_unique&lang=fr&apikey=$gTokenAlma";
											
											// $debug .= "$url\n";
											
											$ch1 = curl_init();
											curl_setopt($ch1,CURLOPT_RETURNTRANSFER, true);

											curl_setopt($ch1,CURLOPT_URL, $url);
											$result1 = curl_exec($ch1);
											
											$xml_result_pret = simplexml_load_string($result1);
											$cptba=0;
											
											foreach($xml_result_pret as $booking_availabilities)
											{
												
												if ($booking_availabilities->reason == "Prêt" || $booking_availabilities->reason == "Réservé") // valeur possible : Prêt ou Réservé
												{

													// $debug =$date_actuelle;
													
													if (date("d/m/Y",strtotime($date_actuelle))==date("d/m/Y",strtotime($jour)))
													{   // pret pour la date du jour
														$date_ref = $date_actuelle; // contient l'heure
														// $affichage_pret = (strtotime($date_ref)<=strtotime($booking_availabilities->to_time));
													}
													else
													{   // pret pour une date suivante
														$date_ref = $jour. " 00:00:00";
														// $affichage_pret = (strtotime($date_ref) >= strtotime($booking_availabilities->from_time)) && (strtotime($date_ref)<=strtotime($booking_availabilities->to_time));
													}
													
													// on affiche que la prêt de la date_ref en cours
													$affichage_pret = (strtotime($date_ref) >= strtotime($booking_availabilities->from_time)) && (strtotime($date_ref)<=strtotime($booking_availabilities->to_time));
													
													if ($affichage_pret)
													{
														// $debug = date("Y-m-d H:i",strtotime($booking_availabilities->from_time));
														
														// par défaut on met les bornes d'ouverture/fermeture
														$ouverturepret = date("G",strtotime($bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['ouverture'])); // 8
														$fermeturepret = date("G",strtotime($bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['fermeture'])); // 21
														
														if (date("d/m/Y",strtotime($booking_availabilities->from_time)) == date("d/m/Y",strtotime($booking_availabilities->to_time)))
														{	
															$note = "Prêt en cours jusqu'au ".date("d/m/Y H:i",strtotime($booking_availabilities->to_time));
																
															// $note = $booking_availabilities->reason;
															
															if (date("d",strtotime($date_ref)) == date("d",strtotime($booking_availabilities->from_time)))
															{
																$ouverturepret = date("G",strtotime($booking_availabilities->from_time));
																if (date("i",strtotime($booking_availabilities->from_time))>=30 && date("i",strtotime($booking_availabilities->from_time))<=59)
																	$ouverturepret += 0.5;
															}
															
															if (date("d",strtotime($date_ref)) == date("d",strtotime($booking_availabilities->to_time)))	
															{
																$fermeturepret = date("G",strtotime($booking_availabilities->to_time));
																if (date("i",strtotime($booking_availabilities->to_time))==0)
																	$fermeturepret -= 0.5;
																if (date("i",strtotime($booking_availabilities->to_time))>30 && date("i",strtotime($booking_availabilities->to_time))<=59)
																	$fermeturepret += 0.5;
															}

															if ($ouverturepret<=$fermeturepret)
															{
																// créneau avant 1er creneau
																// $lp=$ouverturepret - 0.5;
																// $bookings[(string) $identifiant_affichage]["$lp"]=['indisponible' => 2, 'note' => $note];
																
																//1er creneau 
																if (date("d/m/Y",strtotime($booking_availabilities->from_time)) == date("d/m/Y",strtotime($booking_availabilities->to_time)))
																	$bookings[(string) $identifiant_affichage]["$ouverturepret"]=['indisponible' => 2, 'note' => $note];
																else
																	$bookings[(string) $identifiant_affichage]["$ouverturepret"]=['indisponible' => 1, 'note' => $note];
																
																for ($l=$ouverturepret+0.5; $l<$fermeturepret; $l+=0.5) 
																{
																	$bookings[(string) $identifiant_affichage]["$l"]=['indisponible' => 1, 'note' => $note];
																}
																
																//dernier creneau 
																$bookings[(string) $identifiant_affichage]["$fermeturepret"]=['indisponible' => 3, 'note' => $note];
																
																if ($ouverturepret==$fermeturepret) // cas particulier prêt de 30 minutes faite avec alma
																{
																	$bookings[(string) $identifiant_affichage]["$fermeturepret"]=['indisponible' => 4, 'note' => $note];
																}
																
																// créneau après dernier creneau
																// $bookings[(string) $identifiant_affichage]["$l"]=['indisponible' => 2, 'note' => $note];

																// $message .= min($bookings).'/'.max($bookings);
																// $debug =$bookings;
																// $debug ="$date_ref $ouverturepret<=$fermeturepret | " . date("Y-m-d H:i:s",strtotime($booking_availabilities->from_time)) . "->" .date("Y-m-d H:i:s",strtotime($booking_availabilities->to_time)) ;
																$debug .= "ItemID $identifiant_affichage - prêt en cours détecté : $ouverturepret -> $fermeturepret\n";
															}
														
														}
														else {
															// 	$note = "Prêt en cours - en retard - salle non réservable pour le moment";
															$debug .= "ItemID $identifiant_affichage : Prêt en retard detecté (non bloquant)\n";
														}
														$cptba++;
													}
												}
											}
										}
									}
								}
							}
						}
						
						// on recherche les réservations pour chaque mms (marche pour résa dont débutet fin dans lamême journée = faite par l'appli)
						// if (date("d/m/Y",strtotime($date_actuelle))!=date("d/m/Y",strtotime($jour)))
						if (true)
						{
							$cptmms = 1;
							foreach ($bu[$codebu]['mmsid'] as $key => $value)
							{
								// filtrage affichage par page
								$minmms = (($page-1)*$gNbMmsParPage)+1;
								$maxmms = $minmms+$gNbMmsParPage;

								if (($cptmms >= $minmms) && ($cptmms < $maxmms))
								{
									
									// utilisation api request par mms (il pourrait y avoir plusieurs salles par mms / holding)...
									$url = "$gAdrAlma/almaws/v1/bibs/$key/requests?request_type=BOOKING&status=active&lang=fr&apikey=$gTokenAlma";
									// $debug .= "$url\n";
									$ch = curl_init();
									curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

									curl_setopt($ch,CURLOPT_URL, $url);
									$result = curl_exec($ch);
									
									$xml_result = simplexml_load_string($result);
									
									$i=0;
									// debug
									// $message .= $url."<br/>" ;
									
									// PARSE RESULTS
									foreach($xml_result->user_request as $user_request)
									{
											
										// todo ne marche pas avec une résa sur plusieurs jours (faite via alma)
										if (date("d/m/Y",strtotime($date_actuelle))==date("d/m/Y",strtotime($jour)))
										{   // pret pour la date du jour
											$date_ref = $date_actuelle; // contient l'heure
											// $affichage_pret = (strtotime($date_ref)<=strtotime($booking_availabilities->to_time));
										}
										else
										{   // pret pour une date suivante
											$date_ref = $jour. " 00:00:00";
											// $affichage_pret = (strtotime($date_ref) >= strtotime($booking_availabilities->from_time)) && (strtotime($date_ref)<=strtotime($booking_availabilities->to_time));
										}
										
										// on affiche que la prêt de la date_ref en cours
										$affichage_resa = (strtotime($date_ref) >= strtotime($user_request->adjusted_booking_start_date)) && (strtotime($date_ref)<=strtotime($user_request->adjusted_booking_end_date));
										
										// if ($affichage_resa)
										// if (true)
										if (date("d/m/Y",strtotime($user_request->adjusted_booking_start_date))==date("d/m/Y", strtotime($jour)) && date("d/m/Y",strtotime($user_request->adjusted_booking_end_date))==date("d/m/Y", strtotime($jour)))
										{	// les bornes d'une résa sont forcément dans la même journée. Il n'est pas possible de créer une résa sur plusieurs jours
											// si la demande est pour le jour selectionné...
									
											$identifiant_affichage = $user_request->item_id; //on peut mettre $key à condition qu'il n'y est que 1 salle par notice...
											
											if ($user_request->item_id != "")
											{
												// debug
												// $message .= "<div style='margin-left:5px;margin-bottom:5px;'>##debug##<br/>Demande n°" . $user_request->request_id;
												// $message .= "<br/>Mmsid : " . $key;
												// $message .= "<br/>Utilisateur : " . $user_request->user_primary_id;
												// $message .= "<br/>Titre : " . $user_request->title;
												// $message .= "<br/>Description : " . $user_request->description;
												// $message .= "<br/>Code-barre : " . $user_request->barcode;
												// $message .= "<br/>Itemid : " . $user_request->item_id;
												// $message .= "<br/>Note : " . $user_request->comment;
												// $message .= "<br/>Début : " . date("Y-m-d H:i",strtotime($user_request->adjusted_booking_start_date));
												// $message .= "<br/>Fin : " . date("Y-m-d H:i",strtotime($user_request->adjusted_booking_end_date));
												// $message .= "</div>";
																		
												$note = "";
												
												if (strtolower(trim($user_request->comment))==strtolower("réservé par application ".$gNomAppli))
													$note = "Réservé du ".date("d/m/Y H:i",strtotime($user_request->adjusted_booking_start_date))." au ".date("d/m/Y H:i",strtotime($user_request->adjusted_booking_end_date));
												elseif  (trim($user_request->comment)!='')
													$note = trim($user_request->comment)." - Réservé du ".date("d/m/Y H:i",strtotime($user_request->adjusted_booking_start_date))." au ".date("d/m/Y H:i",strtotime($user_request->adjusted_booking_end_date))."";

												$ouvertureresa = date("G",strtotime($bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['ouverture'])); // ex : 8
												$fermetureresa = date("G",strtotime($bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['fermeture'])); // ex : 21
												
												if (date("d",strtotime($date_ref)) == date("d",strtotime($user_request->adjusted_booking_start_date)))
												{
													$ouvertureresa = date("G",strtotime($user_request->adjusted_booking_start_date));
													if (date("i",strtotime($user_request->adjusted_booking_start_date))>=30 && date("i",strtotime($user_request->adjusted_booking_start_date))<=59)
														$ouvertureresa += 0.5;
												}
												
												if (date("d",strtotime($date_ref)) == date("d",strtotime($user_request->adjusted_booking_end_date)))	
												{
													$fermetureresa = date("G",strtotime($user_request->adjusted_booking_end_date));
													if (date("i",strtotime($user_request->adjusted_booking_end_date))==0)
														$fermetureresa -= 0.5;
													if (date("i",strtotime($user_request->adjusted_booking_end_date))>30 && date("i",strtotime($user_request->adjusted_booking_end_date))<=59)
														$fermetureresa += 0.5;
												}
												
												if ($ouvertureresa<=$fermetureresa)
												{
													// créneau avant 1er creneau
													// $lp=$ouvertureresa - 0.5;
													// $bookings[(string) $identifiant_affichage]["$lp"]=['indisponible' => 2, 'note' => $note];
													
													//1er creneau 
													$bookings[(string) $identifiant_affichage]["$ouvertureresa"]=['indisponible' => 2, 'note' => $note];
													
													for ($l=$ouvertureresa+0.5; $l<$fermetureresa; $l+=0.5) 
													{
														$bookings[(string) $identifiant_affichage]["$l"]=['indisponible' => 1, 'note' => $note];
													}
													
													//dernier creneau 
													$bookings[(string) $identifiant_affichage]["$fermetureresa"]=['indisponible' => 3, 'note' => $note];
													
													if ($ouvertureresa==$fermetureresa) // cas particulier résa de 30 minutes faite avec alma
													{
														$bookings[(string) $identifiant_affichage]["$fermetureresa"]=['indisponible' => 4, 'note' => $note];
													}
													// créneau après dernier creneau
													// $bookings[(string) $identifiant_affichage]["$l"]=['indisponible' => 2, 'note' => $note];

													// $message .= min($bookings).'/'.max($bookings);
													// $debug =$bookings;
													$debug .= "ItemID $user_request->item_id - réservation détectée : $ouvertureresa -> $fermetureresa\n";

												}
											}
											else
												$debug .= "Demande $user_request->request_id malformée (ItemID/Barcode vide)\n";
												
										}	

									}
									
								}
								
								$cptmms++;
							}
						}
						
						if (!isset($_SESSION["info-2"]))
						{
							$message .= "<div id='info2' class='alert alert-warning alert-dismissible fade show' role='alert' style='margin-bottom:5px;'><i class=\"fa fa-exclamation-circle\" aria-hidden=\"true\"></i> Vous devez cliquer sur un créneau pour le sélectionner. Une réservation doit faire entre <b>$creneau_min</b> et <b>$creneau_max</b> créneaux.<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>";
							$message .= "<SCRIPT>";
							$message .= "$('#info2').on('closed.bs.alert', function () {";
							$message .= "  masque_info(2);";
							// $message .= "  alert('close2');";
							$message .= "  });";
							$message .= "</SCRIPT>";
						}
						
						// Affichage des salles et agenda
						$cptmms = 1;
						foreach ($bu[$codebu]['mmsid'] as $keym => $valuem)
						{
							// filtrage affichage par page
							$minmms = (($page-1)*$gNbMmsParPage)+1;
							$maxmms = $minmms+$gNbMmsParPage;
							if (($cptmms >= $minmms) && ($cptmms < $maxmms))
							{
								foreach ($valuem['holdingid'] as $keyh => $valueh)
								{ 
									foreach ($valueh['itemid'] as $keyi => $valuei)
									{ 
										// $ouverturetxt = date("H:i",strtotime($bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['ouverture']));
										// $fermeturetxt = date("H:i",strtotime($bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['fermeture']));	
										
										$description=$valuei["description"];
										$url_description=$valuei["url_description"];
										
										$icon_chevron = "fa-angle-double-up";
										$icon_chevron_ttt = "Masquer la description";
										$data_visible = 1;
										$visible = "visible";
										$style = "display:block;";
										
										if (isset($_SESSION["description-$keyi"]))
											if ($_SESSION["description-$keyi"] == 0)
											{
												$icon_chevron = "fa-angle-double-down";
												$data_visible = 0;
												$icon_chevron_ttt = "Montrer la description";
												$visible = "invisible";
												$style = "height:0px; display:block;";
											}
				
											
										$message .= "<div class=\"border rounded mb-2 p-1\"><a id='chevron-$keyi' data-toggle=\"tooltip\" data-placement=\"top\" data-id='$keyi' class='btn_description float-right' title='$icon_chevron_ttt'><i class=\"fa $icon_chevron fa-border\" aria-hidden=\"true\"></i></a>";
										
										// $message .= "<div class=\"d-flex flex-wrap\">";
										$message .= "<div class=\"d-flex\">";
											$message .= "<div style=\"margin-right:10px;\"><span style=\"font-size:20px;font-weight: bold;\">".$valuei['nom']."</span>&nbsp&nbsp;<a data-toggle=\"tooltip\" data-placement=\"top\" href='$url_description' title='Accéder à la page web de la salle' target='_blank'><i class=\"fa fa-link\" aria-hidden=\"true\"></i></a></div>";
											$message .= "<div style='margin-top:3px;padding-top:0px;padding-right:5px;' id=\"alert-".$keyi."\"></div>";
											
										$message .= "</div>";
										$message .= "<hr style='margin:0px;padding:0px;' />";
										$message .= "<div style='$style' class='$visible' data-visible='$data_visible' id='description-$keyi'>";
											$message .= "<div class='mr-10' id='description-int-$keyi'><i>".$description."</i></div>";
										$message .= "</div>";
						
										$message .= "<div style=\"padding-top:3px;\" class=\"d-flex flex-wrap\">";
										
										$heure_ouverture = "";
										
										// onglet sélectionné = aujourd'hui
										if (date("d/m/Y",strtotime($date_actuelle)) == date("d/m/Y",strtotime($jour))) 
										{
											// todo voir ouverture spécifique après heure actuelle (tester le matin avant ouverture !!!)

											// on cherche si la BU n'est pas dans un horaire spécifique
											foreach ($bu[$codebu]['horaire_specifique'] as $key => $value) 
											{
												$dd = new DateTime(date("Y-m-d",strtotime($value['debut'])));
												
												$df = new DateTime(date("Y-m-d",strtotime($value['fin'])));
												
												$da = new DateTime(date("Y-m-d",strtotime($jour)));
												
												if (($da>=$dd) && ($da<=$df)) {
													$heure_ouverture = date("Y-m-d",strtotime($jour)) . " " . $value['ouverture'];
													break;
												}
											}
											
											if ($heure_ouverture=="") // pas d'heure spécifique,  on met la date / heure d'ouverture normale
												$heure_ouverture = date("Y-m-d",strtotime($jour)) . " " . $bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['ouverture'];
											
											//heure spécifique
											$ouverture_calendrier = strtotime($heure_ouverture);
		
											if ($ouverture_calendrier > strtotime(date("Y-m-d H:i:s",strtotime($date_actuelle))))
											{
												// si l'horaire d'ouverture n'est dépassée, on met l'heure d'ouverture normale ou spécifique
												$ouverture = date("G", $ouverture_calendrier);
												
												if (date("i", $ouverture_calendrier )>=30)
													$ouverture += 0.5;
											}
											else
											{
												// si l'horaire d'ouverture est dépassée, on met l'heure actuelle
												//heure actuelle + 30min
												$ouverture = date("G", strtotime($date_actuelle . " +30 minutes"));
												
												// if (date("i", strtotime(date("Y-m-d H:i:s") . " +30 minutes"))>=30)
												if (date("i", strtotime($date_actuelle . " +30 minutes"))>=30)
													$ouverture += 0.5;
											}
											
										}
										else //onglet sélectionné = J++ (pas aujourd'hui)
										{
											
											// on cherche si la BU n'est pas dans un horaire spécifique
											foreach ($bu[$codebu]['horaire_specifique'] as $key => $value) 
											{
												$dd = new DateTime(date("Y-m-d",strtotime($value['debut'])));
												
												$df = new DateTime(date("Y-m-d",strtotime($value['fin'])));
												
												$da = new DateTime(date("Y-m-d",strtotime($jour)));
												
												if (($da>=$dd) && ($da<=$df)) {
													$heure_ouverture = date("Y-m-d",strtotime($jour)) . " " . $value['ouverture'];
													break;
												}
											}
											
											//heure de ouverture normale
											if ($heure_ouverture == "") //pas d'heure spécifique,  on met l'heure d'ouverture normale
												$heure_ouverture = date("Y-m-d",strtotime($jour)) . " " . $bu[$codebu]['horaire']["j".date("N",strtotime($jour))]['ouverture'];

											$ouverture = date("G",strtotime($heure_ouverture)); // probleme avec ouverture / minute > 00 ?

											if (date("i",strtotime($heure_ouverture))>=30)
												$ouverture += 0.5;
											
										}
										
										
										$heure_fermeture = "";
							
										// on cherche si la BU n'est pas dans un horaire spécifique
										foreach ($bu[$codebu]['horaire_specifique'] as $key => $value) 
										{
											$dd = new DateTime(date("Y-m-d",strtotime($value['debut'])));
											
											$df = new DateTime(date("Y-m-d",strtotime($value['fin'])));
											
											$da = new DateTime(date("Y-m-d",strtotime($jour)));
											
											if (($da>=$dd) && ($da<=$df)) {
												$heure_fermeture = $value['fermeture'];
												break;
											}
										}
										
										//heure de fermeture normale
										if ($heure_fermeture == "")
											$heure_fermeture = $bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['fermeture'];
										
										if ($heure_fermeture == "24:00:00") // cas particulier fermeture à minuit, note ça marche pas si fermeture après minuit
											$fermeture = 24;
										else
											$fermeture = date("G",strtotime($heure_fermeture));
										
										if (date("i",strtotime($heure_fermeture))>=30)
											$fermeture += 0.5;
										
										// $message .= $ouverture . "/".$fermeture;

										$c=0;
										
										if ($ouverture < $fermeture-(($creneau_min * 30)/60))
										{	
											for ($k=$ouverture; $k<$fermeture; $k+=0.5) 
											{
												
												$creneauclass = "creneaudispo text-white";
												$creneauttt = "Créneau disponible, cliquer pour sélectionner ce créneau";
												$dataselection = "0";

												$rounded = "";
												if ($k==$ouverture)		
													$rounded = "rounded-left";
												if ($k==$fermeture-0.5)	
													$rounded = "rounded-right";

												// si le créneau est pris

												if (isset($bookings[(string) $keyi]["$k"]))
												{
													if ($bookings[(string) $keyi]["$k"]['indisponible']=="1")
													{
														$creneauclass = "creneauindispo text-white";
														$creneauttt = "Créneau indisponible";
														if (trim($bookings[(string) $keyi]["$k"]['note'])!="")
															$creneauttt .= " (".trim($bookings[(string) $keyi]["$k"]['note']).")";
														$dataselection = "2";
													}

													elseif ($bookings[(string) $keyi]["$k"]['indisponible']=="2")
													{   //pas utilisé : 1er créneau de la réservation
														$creneauclass = "creneauindispoinf text-white";
														$creneauttt = "Créneau indisponible";
														if (trim($bookings[(string) $keyi]["$k"]['note'])!="")
															$creneauttt .= " (".trim($bookings[(string) $keyi]["$k"]['note']).")";
														$dataselection = "2";
													}
													elseif ($bookings[(string) $keyi]["$k"]['indisponible']=="3")
													{	//dernier créneau de la réservation
														$creneauclass = "creneauindisposup text-white";
														$creneauttt = "Créneau indisponible";
														if (trim($bookings[(string) $keyi]["$k"]['note'])!="")
															$creneauttt .= " (".trim($bookings[(string) $keyi]["$k"]['note']).")";
														$dataselection = "2";
													}
													elseif ($bookings[(string) $keyi]["$k"]['indisponible']=="4")
													{	//ca particulier 1 seul créneau ddns la réservation
														$creneauclass = "creneauindispoinfsup text-white";
														$creneauttt = "Créneau indisponible";
														if (trim($bookings[(string) $keyi]["$k"]['note'])!="")
															$creneauttt .= " (".trim($bookings[(string) $keyi]["$k"]['note']).")";
														$dataselection = "2";
													}
												}
												
												// si le creneau est le 1er de la journée
												// if ($k==$ouverture-0.5)
												// {
													// $creneauclass = "creneauindispofermeture text-white";
													// $creneauttt = "Créneau indisponible (BU fermée)";
													// $dataselection = "2";
												// }
												
												// si le creneau est le dernier de la journée
												if ($k==$fermeture-0.5)
												{
													$creneauclass = "creneauindispofermeture text-white";
													$creneauttt = "Créneau indisponible (la BU va bientôt fermer)";
													$dataselection = "2";
												}
												
												//formatage heure sur 2 chiffres
												if ($k<10)
													$ktxt = "0".intval($k);
												else
													$ktxt = intval($k);
												
												//formatage heure suivante sur 2 chiffres
												$ks = $k+0.5;
												if ($ks<10)
													$kstxt = "0".intval($ks);
												else
													$kstxt = intval($ks);						
												
												// affichage des minutes de fin du creneau
												if (intval($k)==$k)	
													$creneauminute = "00";
												else
													$creneauminute = "30";

												$datecreneaufin = strtotime(date("Y-m-d H:i", strtotime(date("Y-m-d $ktxt:$creneauminute:00",strtotime($jour)))) . " +29 minute");
													
												$message .= "<div style=\"padding:5px;margin-bottom:1px;margin-top:1px;\" 
																  id='c-".$keyi."-$k' 
																  class=\"salle2 $creneauclass $rounded\" 
																  data-toggle=\"tooltip\" data-placement=\"top\" 
																  title=\"$creneauttt\" 
																  data-nomsalle=\"".$valuei['nom']."\" 
																  data-mmsid='".$keym."' 
																  data-holdingid='".$keyh."' 
																  data-itemid='".$keyi."' 
																  data-cb='".$valuei['cb']."' 
																  data-selection=\"$dataselection\" 
																  data-num=\"$k\" 
																  data-creneautxt=\"".date("d/m/Y",strtotime($jour))." $ktxt:$creneauminute à ".date("H:i",$datecreneaufin)."\" 
																  data-horairefin=\"".date("Y-m-d H:i:s",$datecreneaufin)."\" 
																  data-horaire=\"".date("Y-m-d",strtotime($jour))." $ktxt:$creneauminute:00\">"
																  .$ktxt."h$creneauminute</div>";
												
												$c++;

											}
										}
										
										if ($c==0)
											$message .= "<div><i class='fa fa-calendar-times-o' aria-hidden='true'></i> Il n'y a plus assez de créneaux disponibles. La BU ferme à ".date("H\hi",strtotime($heure_fermeture)).".</div>";
							
										$message .= "</div>";
										$message .= "</div>";
									
									}
								}
							}

							$cptmms++;
							
						}
						
						$message .= "<SCRIPT>";
						$message .= "$('.btn_description').on('click', function(){";
						$message .= " masque_description($(this).data('id'));";
						$message .= "  });";
						$message .= "</SCRIPT>";

						$btn_critere  =  "<div class='dropdown'>";
							
							$btn_critere .=  "<button id='dropdown_critere_salle' data-valeur='$filtre' class='btn btn-secondary btn-sm dropdown-toggle' type='button' data-toggle='dropdown' aria-expanded='false'>";
							
							if ($filtre== "")
								$btn_critere .= "<i class='fa fa-filter' aria-hidden='true'></i> Filtre : Toutes les salles";
							else
								$btn_critere .= "<i class='fa fa-filter' aria-hidden='true'></i> Filtre : $filtre";
							
							$btn_critere .= "</button>";
							$btn_critere .= "<div class='dropdown-menu'>";
								$btn_critere .= "<span class='dropdown-item filtresel' data-id=''><i class='fa fa-filter' aria-hidden='true'></i> Toutes les salles</span>";
								foreach ($tfiltre as $keyf => $valuef)
								{
									$btn_critere .= "<span class='dropdown-item filtresel' data-id='$valuef'><i class='fa fa-filter' aria-hidden='true'></i> $valuef</span>";
								}
							$btn_critere .= "</div>";
						$btn_critere .= "</div>";
						
						$btn_critere .= "<SCRIPT>";
						$btn_critere .= "$('.dropdown .filtresel').on('click', function(){";
						$btn_critere .= "$('#dropdown_critere_salle').html('<i class=\'fa fa-filter\' aria-hidden=\'true\'></i> Filtre : ' + $(this).text());";
						$btn_critere .= "$('#dropdown_critere_salle').data('valeur',$(this).data('id'));";
						$btn_critere .= "disponibilite_bu('".$array['codebu']."', '".$array['nombu']."', '".$array['jour']."', 1, $(this).data('id'));";
						$btn_critere .= " });";
						$btn_critere .= "</SCRIPT>";
						
						$array['btn_critere'] = $btn_critere;
					}
					else
					{
						$d1 = new DateTime($date_actuelle); //date actuelle strtotime($date_actuelle)

						$d2 = new DateTime($d1->format('Y-m-d ').$heure_fermeture);
						
						if ($d1>=$d2)
							$message .= "<div class='alert alert-warning' role='alert'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> La BU est fermée depuis ".$d2->format('H:i').".</div>";
						else
							$message .= "<div class='alert alert-warning' role='alert'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> La BU ferme bientôt (".$d2->format('H:i')."). Il n'y a plus assez de créneau disponible pour créer une réservation.</div>";
					}
				}
				else
				{
					if ($raison_fermeture=="")
					{
						if (date("d/m/Y",strtotime($date_actuelle))==date("d/m/Y",strtotime($jour))) // onglet sélectionné = aujourd'hui
							$message .= "<div class='alert alert-warning' role='alert'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> La BU est fermée.</div>";
						else
							$message .= "<div class='alert alert-warning' role='alert'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> La BU sera fermée.</div>";
					}
					else
						$message .= "<div class='alert alert-warning' role='alert'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> $raison_fermeture</div>";
					
					// $array['btn_critere'] = "<div id='dropdown_critere_salle' data-valeur='$filtre'></div>";
				}
				
				$message .= "</div>";
			
				if ($buencoreouverte)
				{

					// affichage pagination
					
					if ($nbpage>1)
					{

						$btn_disabled = "";
						if ($page<=1)
							$btn_disabled ="disabled";
						$message .= "<div style='margin-bottom:10px;' class='user-select-none'>";
						$message .= "<nav aria-label='Page salle'>";
						$message .= "<ul class='pagination pagination-sm justify-content-center' style='margin:0px;padding:0px;'>";
						
						$message .= "<li class='page-item $btn_disabled' style='margin:0px;padding:0px;'><span id='btn_precedent' class='page-link' data-toggle='tooltip' data-placement='top' title='Salles précédentes'><span id='chevron_precedent'><i class='fa fa-chevron-left' aria-hidden='true'></span></i></span></li>";	
						$message .= "<li class='page-item disabled' style='margin:0px;padding:0px;'><span class='page-link'>page : $page / $nbpage&nbsp;</span></li>";
						$btn_disabled = "";
						if ($page==$nbpage)
							$btn_disabled ="disabled";
						
						$message .= "<li class='page-item $btn_disabled' style='margin:0px;padding:0px;'><span id='btn_suivant' class='page-link' data-toggle='tooltip' data-placement='top' title='Salles suivantes'><span id='chevron_suivant'><i class='fa fa-chevron-right' aria-hidden='true'></i></span></span></li>";
						
						$message .= "</ul>";
						$message .= "</nav>";
						$message .= "</div>";	

						$message .= "<SCRIPT>";
						$message .= "$( '#btn_precedent' ).click(function() {";
						$message .= "  $( '#chevron_precedent' ).html( '<i class=\'fa fa-cog fa-spin\' aria-hidden=\'true\'></i>' );";
						$message .= "  disponibilite_bu('".$array['codebu']."', '".$array['nombu']."', '".$array['jour']."', $pageprecedente,'$filtre');";
						$message .= "	});";
						$message .= "$( '#btn_suivant' ).click(function() {";
						$message .= "  $( '#chevron_suivant' ).html( '<i class=\'fa fa-cog fa-spin\' aria-hidden=\'true\'></i>' );";
						$message .= "  disponibilite_bu('".$array['codebu']."', '".$array['nombu']."', '".$array['jour']."', $pagesuivante,'$filtre');";
						$message .= "	});";
						
						$message .= "</SCRIPT>";
					}
					
					$message .= "<div class='border rounded mb-2 p-1' >";
						$message .= "<div style='font-size:20px;font-weight: bold;' class='border-bottom' >Créneaux choisis :</div>";
						$txtMinCreneauReservation = "un créneau libre";
						if ($creneau_min>1)
							$txtMinCreneauReservation = "$creneau_min créneaux libres";
						
						$message .= "<div id='reservation'>";
						$message .= "<ul></ul>";
						$message .= "</div>";
						$message .= "<div id='reservationvide' style='margin-left:7px;'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> Veuillez sélectionner <b>au moins $txtMinCreneauReservation</b> pour pouvoir valider votre réservation</div>";
						
						$message .= "<SCRIPT>";
						$message .= "$('.creneaudispo').on('click', function(){";
						$message .= " choix_creneau($(this), $creneau_max, $creneau_min);";
						$message .= "  });";
						$message .= "</SCRIPT>";
						
						$message .= "<div class=\"row\">";
						$message .= "<div class=\"col-sm\" style='margin-top:10px;'><button id=\"reserver\" type=\"button\" class=\"btn btn-primary d-none\"><i class='fa fa-calendar-check-o' aria-hidden='true'></i> Réserver les créneaux sélectionnés</button></div>";
						$message .= "<div class=\"col-sm\" style='margin-top:10px;'><button id=\"supprimer\" type=\"button\" class=\"btn btn-danger d-none\"><i class='fa fa-trash' aria-hidden='true'></i> Supprimer tous les créneaux sélectionnés</button></div>";
						$message .= "</div>";
					$message .= "</div>";
					
					$message .= "<SCRIPT>";
					$message .= "$('[data-toggle=\"tooltip\"]').tooltip();";
					$message .= "$('#supprimer').on('click', function(){";
					$message .= "	supprimer_creneaux();";
					$message .= "  });";
					$message .= "</SCRIPT>";
					$message .= "<div id='traitementdonnees'>";
					$message .= "</div>";
					$message .= '<!-- Modal -->';
					$message .= '<div class="modal fade" id="validationmodal" tabindex="-1" role="dialog" aria-labelledby="validationmodalLabel" aria-hidden="true">';
					$message .= '  <div class="modal-dialog modal-dialog-centered" role="document">';
					$message .= '	<div class="modal-content">';
					$message .= '	  <div class="modal-header">';
					$message .= '		<h5 style="font-weight:bold;" class="modal-title" id="exampleModalLabel">'.$gNomAppli.'</h5>';
					$message .= '		<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
					$message .= '		  <span aria-hidden="true">&times;</span>';
					$message .= '		</button>';
					$message .= '	  </div>';
					$message .= '	  <div class="modal-body">';
					$message .= "		<div style='font-weight:bold;'>Pour confirmer la réservation suivante :</div>";
					$message .= "			<div id=\"crenauxchoisis\">";
					$message .= '	   		</div>';
					
					$connexion = "";
					
					if (isset($_SESSION["provenance"]))
						$connexion = $_SESSION['provenance'];
					
					if ($connexion!="")
						$message .= "		<div style='margin-top:10px;margin-bottom:10px;font-weight:bold;'>Veuillez cliquer sur le bouton suivant :</div>";
					else					
						$message .= "		<div style='margin-top:10px;margin-bottom:10px;font-weight:bold;'>Veuillez vous identifier avec votre compte universitaire :</div>";
					
					$message .= "		<div = 'identification'>";

					$buttonconnexion = "";
					$cptidp=0;
					
					// affichage des boutons de connexions aux IDP
					foreach ($gIdp as $key => $value){
						if ($value['active'])
						{
							$buttonidp=$value['id'];
							$buttoncolor=$value['buttoncolor'];
							$buttontxt=$value['buttontext'];
							
							if ($connexion =="") {
								$buttonconnexion .= "<button id='submit-idp' style='margin-bottom:10px;min-height:40px;$buttoncolor' class='authentification btn-block btn btn-primary ttt' data-toggle='tooltip' data-container='body' data-placement='top' data-html='true' title=\"Réserver\" data-idp='$buttonidp'><i class='fa fa-sign-in fa-fw'></i> $buttontxt</button>\n";
								$cptidp++;
							}
							elseif ($connexion == $buttonidp) {
								$buttonconnexion .= "<button id='submit-idp' style='margin-bottom:10px;min-height:40px;$buttoncolor' class='authentification btn-block btn btn-primary ttt' data-toggle='tooltip' data-container='body' data-placement='top' data-html='true' title=\"Réserver\" data-idp='$buttonidp'><i class='fa fa-sign-in fa-fw'></i> $buttontxt</button>\n";
								$cptidp++;
							}
						}
					}
					
					if ($cptidp==0)
						$message .= "		<div class='alert alert-danger' role='alert' role='alert'><i class='fa fa-exclamation-triangle' aria-hidden='true'></i>Erreur : auncun serveur d'authentification n'est configuré dans l'application... impossible de continuer.</div>";
						
					$message .= $buttonconnexion;
					
					$message .= '	  </div>';
					
					$message .= '	  </div>';
					$message .= '	  <div class="modal-footer">';
					$message .= '		<button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>';
					$message .= '	  </div>';
					$message .= '	</div>';
					$message .= '  </div>';
					$message .= '</div>';
					
					$message .= "<SCRIPT>";
					$message .= "$('#reserver').on('click', function(){";
					
					if ($initdate=="")
						$message .= "	ouvrir_modal_reservation();";
					else
						$message .= "	alert('[SIMULATION CALENDRIER] Il n\'est pas possible de réserver en mode simulation du calendrier');";
					
					$message .= "  });";
					$message .= "$('.authentification').on('click', function(){";
					$message .= "	$('#idp').attr('value',$(this).data('idp'));";
					$message .= "	$('#formreservation').submit();";
					$message .= "  });";
					$message .= "</SCRIPT>";
				}
				
			}
			else 
			{
				$message .= "<div class='alert alert-warning' role='alert'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> ".$bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['raison'].".</div>";
				// $array['btn_critere'] = "<div id='dropdown_critere_salle' data-valeur='$filtre'></div>";
			}
			
			$reponse = "OK";
		}
		else
		{
			// date du jour sélectionné
			if (date("d/m/Y",strtotime($date_actuelle))==date("d/m/Y",strtotime($jour)))
				$message .= "<div class='alert alert-warning' role='alert'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> La BU est fermée.</div>";
			else // autre jour
				$message .= "<div class='alert alert-warning' role='alert'><i class='fa fa-calendar-times-o' aria-hidden='true'></i> ".$bu[$codebu]['horaire']['j'.date("N",strtotime($jour))]['raison']."</div>";
				
			// $array['btn_critere'] = "<div id='dropdown_critere_salle' data-valeur='$filtre'></div>";
			
			$reponse = "OK";
		}
	}
	else // on ne devrait jamais passer ici !
	{
		$message .= "<div class='alert alert-danger' role='alert'><i class='fa fa-exclamation-triangle' aria-hidden='true'></i> Le code de la BU n'a pas été transmis.</div>";
	}
	// $message .= "<div>".date("d/m/Y H:i:s",strtotime($date_actuelle))."</div>";
	$array['message'] = $message;
	$array['reponse'] = $reponse;
	$array['debug'] = $debug;
	// $array['debug'] = $bu[$codebu];
	
	echo json_encode($array);
	
?>
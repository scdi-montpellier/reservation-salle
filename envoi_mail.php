<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	include ("config.php");
	
	// Import PHPMailer classes into the global namespace
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	
	require ($gDossier.'PHPMailer/src/Exception.php');											   
	require ($gDossier.'PHPMailer/src/PHPMailer.php');
	require ($gDossier.'PHPMailer/src/SMTP.php');
	
	session_start();
	
	include ("config.php");
	include ("fonction.php");
	
	$reponse = "KO";
	$message1 = "";
	$message2 = "";
	$maildemande = "";
	$nomdemande = "";
	$resa = "";
	
	$eppnc = "";
	if (isset($_POST["eppnc"])) 
		$eppnc=$_POST["eppnc"];
	
	$mailc = "";
	if (isset($_POST["mailc"])) 
		$mailc=$_POST["mailc"];
	
	$nomc = "";
	if (isset($_POST["nomc"])) 
		$nomc=$_POST["nomc"];
	
	$resac = "";
	if (isset($_POST["resac"])) 
		$resac=$_POST["resac"];

	$requestidc = "";
	if (isset($_POST["requestidc"])) 
		$requestidc=$_POST["requestidc"];
	
	$message = "";
	if (isset($_POST["message"])) 
		$message=$_POST["message"];


	if ( $eppnc != "" && $mailc != "" && $nomc != "" && $requestidc != "" && $message != "") 
	{
		$maildtmp = urlsafe_b64decode($mailc);
					
		if ($maildtmp!="")
			$maildemande = decryptText($maildtmp,$gKey);
		
		$nomdtmp = urlsafe_b64decode($nomc);
					
		if ($nomdtmp!="")
			$nomdemande = decryptText($nomdtmp,$gKey);
		
		
		$resatmp = urlsafe_b64decode($resac);
									
		if ($resatmp!="")
			$resa = decryptText($resatmp,$gKey);
		
		//RESA $codebu."¤".$mmsid."¤".$holdingid."¤".$itemid."¤"$cb."¤".$debut."¤".$fin
		$tabresa = explode('¤',$resa);
		
		$codebu = $tabresa[0];
		$mmsid = $tabresa[1];
		$holdingid = $tabresa[2];
		$itemid = $tabresa[3];
		$cb = $tabresa[4];
		$debut = $tabresa[5];
		$fin = $tabresa[6];			
		
		if (strlen($maildemande) > 3 && (filter_var($maildemande, FILTER_VALIDATE_EMAIL)))
		{

			//!!!!!!!!!!! Pour test et envoi à un compte de test !!!!!!!!!!!!!!!!
			// $maildemande = "xxx.xxxx@nomdedomaine.tld";
			
			$provenance = $bu[$codebu]['campus'];
			
			// on génère le lien de suppression
			$lien = $gURLs."supprime_reservation.php?eppnc=$eppnc&requestidc=$requestidc&provenance=$provenance";
				
			$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
			try 
			{
				
				if ($gMailSMTP!="")
				{
					$mail->isSMTP();                 // Set mailer to use SMTP
					$mail->Host       = $gMailSMTP;  // Specify main and backup SMTP servers
					$mail->SMTPAuth   = false;       // Enable SMTP authentication
					$mail->Port       = 25;
				}	
				
				$mail->setLanguage('fr', '/PHPMailer/language/');
				
				$mail->CharSet = 'UTF-8';
				
				//Recipients
				$mail->setFrom($gMailAddFrom, $gMailNameFrom);
				$mail->addAddress($maildemande, $nomdemande);     // Add a recipient
				
				//Attachments
				// $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
				// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
				
				//Content
				$mail->isHTML(true);                                  // Set email format to HTML
				$mail->Subject  = 'Votre réservation de salle';
				
				$mail->Body     = "Bonjour $nomdemande,<br/><br/>";
				// $mail->Body    .= "Vous venez de faire une réservation de salle à ".$gLNominstitutionel.".<br/><br/>";
				// $mail->Body    .= "<b>Votre réservation est confirmée.</b>";
				$mail->Body    .= "$message<br/>";
				$mail->Body    .= "Pour annuler votre réservation, cliquer sur le lien suivant :<br/>";
				$mail->Body    .= "<a target='_blank' href='$lien'>$lien</a><br/><br/>";
				$mail->Body    .= "Si le lien ne s'ouvre pas, veuillez copier/coller le lien dans votre navigateur.<br/><br/>";
				
				$mail->Body    .= "Vous pouvez aussi gérer votre réservation sur votre compte lecteur BU (catalogue Primo).<br/><br/>";
				
				$mail->Body    .= "Cordialement,<br/>";
				$mail->Body    .= ucfirst($gLNominstitutionel)."<br/>";
				
				$mail->AltBody  = "Bonjour $nomdemande,\n\n";
				// $mail->AltBody .= "Vous venez de faire une réservation de salle à ".$gLNominstitutionel.".\n\n";
				// $mail->AltBody .= "Votre réservation est confirmée.\n";
				$mail->AltBody .= strip_tags($message)."\n\n";
				$mail->AltBody .= "Pour annuler votre réservation, cliquer sur le lien suivant :\n";
				$mail->AltBody .= "$lien\n\n";
				$mail->AltBody .= "Si le lien ne s'ouvre pas, veuillez copier/coller le lien dans votre navigateur.\n\n";
				
				$mail->AltBody .= "Vous pouvez aussi gérer votre réservation sur votre compte lecteur BU (catalogue Primo).\n\n";
				
				$mail->AltBody .= "Cordialement,\n";
				$mail->AltBody .= ucfirst($gLNominstitutionel);

				$mail->send();
				
				$reponse  = 'OK';
				$message1 .= "<div style='font-weight:bold;'><i class='fa fa-envelope-o' aria-hidden='true'></i> Un mail de confirmation a été envoyé à $maildemande</div>";
				$message1 .= "<hr>";
				$message1 .= "<div>Ce mail contient un lien pour annuler votre réservation si besoin.</div>";
				
				$message2 = "<i class='fa fa-home' aria-hidden='true'></i> Retour à la <a title=\"Retour à l'accueil\" href=\"$gURLs\">page d'accueil</a>.";
				
			} 
			catch (Exception $e) 
			{   // on ne doit pas passser ici
				$message1 .= "<div style='font-weight:bold;'><i class='fa fa-envelope-o' aria-hidden='true'></i> Le mail n'a pas pu être envoyé à $maildemande</div>";
				$message1 .= "<hr>";
				$message1 .= "<div>Erreur : ".$mail->ErrorInfo."</div>";
				
				$message2 = "<i class='fa fa-home' aria-hidden='true'></i> Retour à la <a title=\"Retour à l'accueil\" href=\"$gURLs\">page d'accueil</a>.";
			}
		}
		else // on ne doit pas passser ici
		{
			$message1 .= "<div style='font-weight:bold;'><i class='fa fa-envelope-o' aria-hidden='true'></i> Le mail n'a pas pu être envoyé</div>";
			$message1 .= "<hr>";
			$message1 .= "<div>Aucun mail lecteur n'a pu être trouvé.<br/>Veuillez contacter votre bibliothèque pour faire corriger cette anomalie.</div>";
			
			$message2 = "<i class='fa fa-home' aria-hidden='true'></i> Retour à la <a title=\"Retour à l'accueil\" href=\"$gURLs\">page d'accueil</a>.";
		}
	}
	else // on ne doit pas passser ici
		$message1 = "Le mail n'a pas pu être envoyé. Un paramète est vide ($eppnc / $mailc / $nomc / $requestidc / $message).";
	
	$array['reponse'] = $reponse;
	$array['message1'] = $message1;
	$array['message2'] = $message2;
	
	echo json_encode($array);
?>
<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);
	session_start();

	include ("config.php");
	include ("fonction.php");

	if (substr(strtolower(get_full_url()),0,5)!="https")
		 header("location: https://".substr(strtolower(get_full_url()),7));
	
	require_once($gCheminSSP.'lib/_autoload.php');
	
	// on force la deconnexion simplesamlphp
	$saml_auth = new SimpleSAML_Auth_Simple($gNomSP);
	if ($saml_auth->isAuthenticated())
	{
		// logout de la session Shibboleth simplesamlphp
		//$saml_auth->logout(); // ne fonctionne pas sur IDP Unimes et IDP ENSCM... erreur de sécurité
		// alors supprime la session manuellement
		setcookie (session_id(), "", time() - 3600);
		session_destroy();
		session_write_close();
	}
	
	echo "<!DOCTYPE html>\n";
	echo "<html lang=\"fr\">\n";
	echo "  <head>\n";
	echo "  <meta charset=\"UTF-8\">\n";
	echo "  <title>$gNomAppli</title>\n";
	echo "	<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
	echo "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
	echo "	<meta name=\"description\" content=\"$gNomAppli\">\n";
	echo "  <meta name=\"author\" content=\"SCDI de Montpellier\">\n";
	echo "  <meta name=\"author\" content=\"Sébastien Leyreloup\">\n";
	echo "  <link rel=\"shortcut icon\" href=\"$gURLfavicon\">\n";
		
	echo "	<!-- Bootstrap -->";
	echo '  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">';

	echo "	<!-- JQueryUI -->";
	echo "  <link rel='stylesheet' href='https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css'>\n";

	echo "  <!-- Font-awesome -->\n";
	echo "	<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css' />\n";
		
	echo "	<!-- styles perso -->\n";
	echo "  <link rel=\"stylesheet\" href=\"css/sticky-footer-navbar.css\"></link>\n";
	
	?>
	
	<SCRIPT>
	var eppnc = '';
	var resac = '';
	var nomc = '';
	var mailc = '';
	</SCRIPT>
	</head>
	
    <body>
    <!-- Begin page content -->
    <?php
	   echo "<main role='main' class='container'>";
      
		echo "<h1 style='margin-top:0px;margin-bottom:15px' class=\"mt-4\">Confirmation de votre réservation</h1>";
		
		$eppn = "";
		$eppnc = "";
		$dvc = "";
		$dv = 0;
		
		$resa = "";
		$resac = "";
		
		
		if (isset($_GET["dvc"]))
		{
			$dvc = $_GET["dvc"];
			
			if ($dvc!="" && strlen($dvc) > 3)
			{
				$dvctmp = urlsafe_b64decode($dvc);
				
				if ($dvctmp!="")
					$dv = decryptText($dvctmp,$gKey);
				
				if (strlen($dv)<5)
					$dv=0;
				elseif (!validateDate($dv,"U"))
					$dv=0;
			}
		}
		
		if (isset($_GET["eppnc"]))
		{
			$eppnc=trim($_GET["eppnc"]);
			
			if (intval(strtotime("now"))-30<=$dv)
			{
				if ($eppnc!="" && strlen($eppnc) > 3)
				{
					$eppntmp = urlsafe_b64decode($eppnc);
					
					if ($eppntmp!="")
						$eppn = decryptText($eppntmp,$gKey);

					// on verifie que l'eppn est bien sous la forme d'un mail
					if (filter_var($eppn, FILTER_VALIDATE_EMAIL) && strlen($eppn) > 3 )
					{					
						// Tout est OK niveau authentification shibboleth
						
						if (isset($_GET["resa"]))
						{
							$resac = trim($_GET["resa"]);
							
							if ($resac!="" && strlen($resac) > 3)
							{
								
								echo "<div id='attente'></div>";
								echo "<div  id='validation'></div>";
								echo "<div id='mail'></div>";
								echo "<div id='home'></div>";
									
								echo "<SCRIPT>";
								echo "eppnc = '$eppnc';";
								echo "resac = '$resac';";
								echo "</SCRIPT>";

							}
							else
							{   // on ne doit jamais passer ici
								echo "<div class=\"col-sm\" id=\"ent\">";
									echo "<div class='alert alert-danger' role='alert'>";
									echo "<i class='fa fa-times-circle'></i> Impossible de lire les informations de la réservation, le paramètre resac est vide ou incorrect.";
									echo "</div>";
								echo "</div>";
								$eppn="";
								$eppnc="null";
							}
						}
						else
						{   // on ne doit jamais passer ici
							echo "<div class=\"col-sm\" id=\"ent\">";
								echo "<div class='alert alert-danger' role='alert'>";
								echo "<i class='fa fa-times-circle'></i> Impossible de lire les informations de la réservation, le paramètre resa est vide ou incorrect.";
								echo "</div>";
							echo "</div>";
							$eppn="";
							$eppnc="null";
						}
						
					}
					else
					{   // on ne doit jamais passer ici
						echo "<div class=\"col-sm\" id=\"ent\">";
							echo "<div class='alert alert-danger' role='alert'>";
							echo "<i class='fa fa-times-circle'></i> Impossible de lire les informations du compte lecteur via votre identifiant ENT, le paramètre eppnc crypté n'est pas correct.";
							echo "</div>";
						echo "</div>";
						$eppn="";
						$eppnc="null";
					}
					
				}
				else
				{   // on ne doit jamais passer ici
					echo "<div class=\"col-sm\" id=\"ent\">";
						echo "<div class='alert alert-danger' role='alert'>";
						echo "<i class='fa fa-times-circle'></i> Impossible de lire les informations du compte lecteur via votre identifiant ENT, le paramètre eppnc est vide ou incorrect.";
						echo "</div>";
					echo "</div>";
					$eppn="";
					$eppnc="null";
				}
			}
			else
			{
				echo "<div class=\"col-sm\" id=\"ent\">";
					echo "<div class='alert alert-danger' role='alert'>";
					echo "<i class='fa fa-times-circle'></i> Impossible de se connecter : délai de connexion via ENT université dépassé.<hr>";
					echo "<i class='fa fa-info-circle'></i> Veuillez recommencer la procédure depuis la <a href='$gURLs' title='Accueil' alt='Accueil' >page d'accueil</a>.";
					echo "</div>";
				echo "</div>";
				$eppn="";
				$eppnc="null";
			}
			
		}
	  
	  include ('pied_page_aide.php');

	  echo "</main>";

	  include ('pied_page.php');
	?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
	
	<!-- jQuery dateformat-->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-dateFormat/1.0/jquery.dateFormat.min.js" integrity="sha512-YKERjYviLQ2Pog20KZaG/TXt9OO0Xm5HE1m/OkAEBaKMcIbTH1AwHB4//r58kaUDh5b1BWwOZlnIeo0vOl1SEA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	
	<!-- jQueryUI -->
	<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
	
	<!-- Popper -->
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>

	<!-- Bootstrap -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
	
	<!-- Fonctions perso -->
	<script type="text/javascript" src="js/fonctions_confirmation.js?1005"></script> 
	
	<?php
	echo $gScriptMatomo;
	?>
	
  </body>
</html>

<?php
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);
	session_start();

	include ("config.php");
	include ("fonction.php");

	if (substr(strtolower(get_full_url()),0,5)!="https")
		 header("location: https://".substr(strtolower(get_full_url()),7));
	
	// require_once($gCheminSSP.'lib/_autoload.php');
	
	// on force la deconnexion simplesamlphp
	// $saml_auth = new SimpleSAML_Auth_Simple($gNomSP);
	// if ($saml_auth->isAuthenticated())
		// $saml_auth->logout();
	
	if (isset($_GET['provenance']))
		$_SESSION["provenance"]=$_GET['provenance'];
	else
		$_SESSION["provenance"]="";
	
	if (isset($_GET['initdate']))
		$_SESSION["initdate"]=$_GET['initdate'];
	else
		$_SESSION["initdate"]="";
	
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
		
	echo "  <!-- Font-awesome -->\n";
	echo "	<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css' />\n";
	
	echo "	<!-- Bootstrap -->";
	echo '  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">';

	echo "	<!-- JQueryUI -->";
	echo "  <link rel='stylesheet' href='https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css'>\n";

	echo "	<!-- styles perso -->\n";
	echo "  <link rel=\"stylesheet\" href=\"css/sticky-footer-navbar.css\"></link>\n";
	
	$initdate = "";
	if (isset($_GET['initdate']))
	{
		$initdate = date("Y-m-d H:i:s", strtotime($_GET['initdate']));
		echo "<script>var initdate = '".date("Y-m-d H:i:s", strtotime($_GET['initdate']))."'</script>";
		
	}
	else
		echo "<script>var initdate = ''</script>";
	?>
	
	</head>
	
    <body>
    <!-- Begin page content -->
    <?php
	 echo "<main role='main' class='container'>";
        echo "<div id='div_session_write'> </div>";
		
		echo "<div id='div_header' class='border border rounded shadow-sm' style=\"padding:10px;background-image: url('images/fond.jpg');background-repeat: no-repeat;background-origin: border-box;background-position: right 50% top 40%;\">";
		
			echo "<div class='border rounded shadow-sm' style='padding:10px;background-color:rgba(255, 255, 255, 0.8);'>";
				echo "<div style='overflow: hidden;'><h1 class='display-6'>$gTitreAppli</h1></div>";
				echo "<p class='lead'>$gSousTitreAppli</p>";
				echo "<hr>";
				if ($initdate!="")
					echo "<span class='text-danger'>[SIMULATION CALENDRIER] Visualisation du calendrier comme si on était le ".date("d/m/Y \à H:i:s", strtotime($initdate))." [SIMULATION CALENDRIER]</span><br/><br/>";
			
				// echo $_GET['provenance'];
				echo '<div class="dropdown user-select-none">';
					echo '	  <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
					echo '	  Choisir une BU...</span>';
					echo '	  </button>';
					echo '	  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
								// on liste des BU
								foreach ($bu as $value){
									echo '	  <span class="dropdown-item busel" data-url="'.$value['url_description'].'" data-id="'.$value['id'].'"><i class="fa fa-building-o" aria-hidden="true"></i> '.$value['nom'].'</span>';
								}
					echo '	  </div>';
				
				echo '</div>';
				echo '<span id="url_bu">&nbsp;</span>';
			echo '</div>';
		
		echo "</div>";
		
	  echo "<div id='jour_ouverture' style=''></div>";
	  
	  
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
	<script type="text/javascript" src="js/fonctions.js?2023-01-06-13-39"></script> 
	
	<?php
	echo $gScriptMatomo;
	?>

  </body>
</html>

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
	
	$aidetexte = "";
	
	if (isset($_POST["codebu"]))
		$codebu=$_POST["codebu"];
	else
		$codebu = "";
	
	if (isset($_POST["jour"]))
		$jour=$_POST["jour"];
	else
		$jour = "";
	
	$_SESSION["dateactuelle"]=date("Y-m-d H:i:s");
	
	if (isset($_SESSION["initdate"]))
	{	
		if ($_SESSION["initdate"]!="")
		{
			$initdate = date("Y-m-d H:i:s", strtotime($_SESSION['initdate']));
			$_SESSION["dateactuelle"]=$initdate;
		}
		else
			$initdate = "";
	}
	else
		$initdate = "";
	
	if (isset($_SESSION["info-1"]))
		unset($_SESSION["info-1"]);
	
	if (isset($_SESSION["info-2"]))
		unset($_SESSION["info-2"]);
	
	if ($codebu != "" && $jour !="") 
	{
		// mise à jour de lien aide en fonction de la BU choisie
		$aideid = "default";
		$campus = $bu[$codebu]['campus'];
		
		if (isset($gAide[$campus]))
			$aideid = $campus;
		
		$aidetexte = "<a class='text-decoration-none' alt='Accéder à ".$gAide[$aideid]['nom']."' title='Accéder à ".$gAide[$aideid]['nom']."' target='_blank' href='".$gAide[$aideid]['url']."'>".$gAide[$aideid]['nom']."</a>";
		
		// $message .= "<div>$initdate</div>";
		$message .= "<ul class=\"nav nav-tabs\">";
		
		// $message .= "<div class=\"input-group mb-3\">";
		// $message .= "  <div class=\"input-group-prepend\" id=\"button-addon3\">";
		$j=0;
		$date_dispo = array();
		
		for ($j=0; $j<$gNbJourReservation; $j++) 
		{
			if ($initdate=="")
				$date_dispo[$j] = strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +$j day");
			else
				$date_dispo[$j] = strtotime(date("Y-m-d", strtotime($initdate)) . " +$j day");
			
			$active = "";
			$jour_complement = "";
			$disabled = "";
			$ttt = "";
			$ouvert = 0;

			if ($j==0)
			{
				$jour_complement = "Aujourd'hui<br/>";
			}
			
			if ($j==1)
			{
				$jour_complement = "Demain<br/>";
			}

			
			//jour selectionné
			if (date("Y-m-d",$date_dispo[$j])==date("Y-m-d",strtotime($jour)))
			{
				$active = "active";
			}	
			
			// jour sans ouverture
			// règle : si c'est fermé en général, ça ne peut pas être ouvert
			if ($bu[$codebu]['horaire']['j'.date("N",$date_dispo[$j])]['ouvert'] == 0)
			{
				$ttt = $bu[$codebu]['horaire']['j'.date("N",$date_dispo[$j])]['raison'];
				$disabled = "text-secondary";
			}
			else
			{
				foreach ($bu[$codebu]['fermeture_specifique'] as $key => $value) 
				{					
					$d1 = new DateTime(date("Y-m-d",strtotime($value['debut'])));
					
					$d2 = new DateTime(date("Y-m-d",strtotime($value['fin'])));
					
					$d3 = new DateTime(date("Y-m-d",$date_dispo[$j]));
					
					if (($d3>=$d1) && ($d3<=$d2))
					{
						$ttt = $value['raison'];
						$disabled = "text-secondary";
						break;
					}
				}
				
				if ($ttt == "")
				{
					$ttt = 'BU ouverte';
					$ouvert = 1;
				}
			}
			
			if ($j>1)
				$jour_complement = "<span class=\"d-block d-md-none\">".ucfirst ($joursemainecourt[date("N",$date_dispo[$j])])."</span><span class=\"d-none d-md-block\">".ucfirst ($joursemaine[date("N",$date_dispo[$j])])."</span>";
			
			$message .= "<li  class=\"nav-item\">";
			$message .= "<a class=\"nav-link $active $disabled\" id=\"tab$j\" data-ouvert=$ouvert data-toggle=\"tooltip\" data-placement=\"top\" data-date='".date("Y-m-d",$date_dispo[$j])."' title=\"$ttt\" href=\"#disponibilite\"><span style=\"font-weight: bold;\">$jour_complement</span><span style=\"\">".date("d/m",$date_dispo[$j])."</span></a>";
			$message .= "</li>";
			$message .= "<li class=\"nav-item\">";

		}
		
		$message .= "</ul>";
		
		$message .=  "<div style='padding-top:5px;margin-right:0px;' class='row'>";
		$message .=  "	<div id ='btn_critere_salle' class='col-sm user-select-none'></div>";
		$message .=  "	<div style='overflow:hidden;'><div id ='disponibilite_attente_message' class='col-sm user-select-none'></div></div>";
		$message .=  "</div>";

		$message .=  "<div id='disponibilite'></div>";
		
		$message .= "<SCRIPT>";
		$message .= "$('[data-toggle=\"tooltip\"]').tooltip();";
		$message .= "$('a.nav-link').on('click', function(){";
		$message .= "	$('#disponibilite_attente_message').show();";
		$message .= "     var filtre;";
		$message .= "     if ($('#dropdown_critere_salle').length == 0)";
		$message .= "     		filtre='';";
		$message .= "     	else";
		$message .= "     		filtre=$('#dropdown_critere_salle').data('valeur');";
		$message .= "     disponibilite_bu('$codebu', '".$bu[$codebu]['nom']."', $(this).data('date'), 1, filtre);";
		$message .= "	$(this).tab('show');";
		$message .= "	$(this).tooltip('hide');";
		$message .= "	return false;";
		$message .= "  });";
		$message .= "</SCRIPT>";
		
		$reponse = "OK";
	}
	else // on ne devrait jamais passer ici !
		$message .= "<i class='fa fa-exclamation-triangle' aria-hidden='true'></i> Le code de la BU n'a pas été transmis.";
	
	$array['codebu'] = $codebu;
	$array['nombu'] = $bu[$codebu]['nom'];
	$array['jour'] = $jour;
	$array['aidetexte'] = $aidetexte;
	
	$array['message'] = $message;
	$array['reponse'] = $reponse;
	
	$array['debug'] = $debug;
	
	echo json_encode($array);
	
?>
<?php

	echo "<footer style='overflow: hidden;' class='footer'>";
    echo "<div class='container'>";
     
	$connexion = "";
	if (isset($_SESSION["provenance"]))
			$connexion = $_SESSION['provenance'];
		
	// affichage des logos
	foreach ($gLogo as $key => $value)
	{
		$urllogo=$value['urllogo'];
		$urlsiteinstitutionel=$value['urlsiteinstitutionel'];
		$textelien=$value['textelien'];
		$visible= $value['logovisible'];
		
		// affichage du logo en fonction des logo actif dans config.php
		if($visible && $connexion == "")
			echo "<span class='text-muted'><a class='text-decoration-none' target='_blank' alt=\"$textelien\" title=\"$textelien\" href='$urlsiteinstitutionel'><img src='$urllogo' style:'max-height:64px; height:64px;' alt='Logo UM' title=\"$textelien\"></a></span>";
		
		// affichage du logo en fonction du forçage provenance
		if ($connexion !="" && $key == $connexion)
			echo "<span class='text-muted'><a class='text-decoration-none' target='_blank' alt=\"$textelien\" title=\"$textelien\" href='$urlsiteinstitutionel'><img src='$urllogo' style:'max-height:64px; height:64px;' alt='Logo UM' title=\"$textelien\"></a></span>";
		}			
	
	// affigafe institution principal et nom appli / version
	echo "&nbsp;<span style='white-space: normal;' class='text-muted'><a class='text-decoration-none' target='_blank' alt='Site du $gNominstitutionel' title='Site du $gNominstitutionel' href='$gURLsiteinstitutionel'>$gNominstitutionel</a></span> <span style='white-space: nowrap;'>| $gNomAppliCourt - v$gVersionAppli</span>";
   
	echo "</div>";
    echo "</footer>";

?>
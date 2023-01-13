<?php
	// site en maintenance
	//die("Site en maintenance, merci de revenir plus tard !");
	
	$gNomAppli = "Réservation Salle BU";
	$gTitreAppli = "Réservation de salle dans une Bibliothèque Universitaire";
	$gSousTitreAppli = "Réserver une salle de travail en groupe avec votre compte lecteur BU de Montpellier";
	$gNomAppliCourt = 'Réservation-salle';
	$gVersionAppli = '1.2.4';
	
	// Adresse serveur API
	$gAdrAlma = "https://api-eu.hosted.exlibrisgroup.com";
	
	// Token api exlibris alma
	$gTokenAlma = urlencode("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"); //prod ou sandbox : droit Users/Bibs/Configurations read/write
	
	$gURLs = "https://reservation-salle.scdi-montpellier.fr/";
	$gDossier = "/var/www/html/reservation-salle-scdi/";
	
	$gNominstitutionelcourt = "SCDI";
	$gNominstitutionel = "SCDI de Montpellier";
	$gNominstitutionelcomplet = "Service de Coopération Documentaire Interuniversitaire de Montpellier";
	$gLNominstitutionel = "le SCDI de Montpellier";
	$gLNominstitutionelcomplet = "le Service de Coopération Documentaire Interuniversitaire de Montpellier";
	$gURLsiteinstitutionel = "https://www.scdi-montpellier.fr/";
	
	// Service d'aide par défaut : ne pas supprimer cette entrée 'default'
	$gAide['default']['nom'] = "Une question ?"; // nom site aide par défaut
	$gAide['default']['url'] = "https://www.scdi-montpellier.fr/boomerang"; // URL d'aide par défaut
	
	// Service d'aide par ID IDP (utiliser le même ID que lors de la définition des serveurs IDP plus bas)
	$gAide['upv']['nom'] = "Une question ? Un.e bibliothécaire vous répond"; // nom site aide dès qu'un serveur IDP est utilisé
	$gAide['upv']['url'] = "https://bibliotheques.univ-montp3.fr/une-question/"; // URL d'aide dès qu'un serveur IDP est utilisé
	
	$gAide['um']['nom'] = "UBIB, un.e bibliothécaire répond à vos questions";
	$gAide['um']['url'] = "https://ubib.libanswers.com/contactez-nous/";
	
	$gAide['enscm']['nom'] = "UBIB, un.e bibliothécaire répond à vos questions";
	$gAide['enscm']['url'] = "https://ubib.libanswers.com/contactez-nous/";
	
	// Favicon
	$gURLfavicon = "images/favicon.ico";
	
	// Affichage logos bas de page
	// logo Universités
	$gLogo['upv']['urllogo'] = "images/logo-upvm.png";
	$gLogo['upv']['urlsiteinstitutionel'] = "https://www.univ-montp3.fr/";
	$gLogo['upv']['textelien'] = "Site de l'Université Paul-Valéry Montpellier 3";
	$gLogo['upv']['logovisible'] = 1; // activer ou désactiver l'affichage du logo					 
	
	$gLogo['um']['urllogo'] = "images/logo-um.png";
	$gLogo['um']['urlsiteinstitutionel'] = "https://www.umontpellier.fr/";
	$gLogo['um']['textelien'] = "Site de l'Université Montpellier";
	$gLogo['um']['logovisible'] = 1;
	
	$gLogo['enscm']['urllogo'] = "images/logo-um.png";
	$gLogo['enscm']['urlsiteinstitutionel'] = "https://www.umontpellier.fr/";
	$gLogo['enscm']['textelien'] = "Site de l'Université Montpellier";
	$gLogo['enscm']['logovisible'] = 0;
	
	// Nom du SP dans simplesamlphp
	$gNomSP = "reservation-salle-scdi";
	$gCheminSSP = "/var/simplesamlphp/";
	
	// Config des serveurs IDP SAML
	// serveur idp UPV
	$gIdp['upv']['id']="upv";
	$gIdp['upv']['server']="urn:mace:cru.fr:federation:univ-montp3.fr"; // Identifiant du serveur IDP
	$gIdp['upv']['buttontext']="Université Paul-Valéry Montpellier 3"; // Texte du bouton de connexion au serveur IDP
	$gIdp['upv']['buttoncolor']="background-color:#1f73ba;border:1px solid #eeeeee;"; // couleurs du bouton de connexion au serveur IDP
	$gIdp['upv']['active']=1;  // activer ou désactiver la connexion possible via ce serveur IDP
	
	// serveurs IDP supplémentaire :
	// serveur idp UM
	$gIdp['um']['id']="um";
	$gIdp['um']['server']="https://federation.umontpellier.fr/idp/shibboleth";
	$gIdp['um']['buttontext']="Université de Montpellier";
	$gIdp['um']['buttoncolor']="background-color:#ff545d;border:1px solid #eeeeee;";
	$gIdp['um']['active']=1;
	
	// serveur idp ENSCM
	$gIdp['enscm']['id']="enscm";
	$gIdp['enscm']['server']="https://idp.enscm.fr/idp/shibboleth";
	$gIdp['enscm']['buttontext']="Ecole Nationale Supérieure de Chimie";
	$gIdp['enscm']['buttoncolor']="background-color:#8bc039;border:1px solid #db9d3c;";
	$gIdp['enscm']['active']=1;

	// clé opérations générales nécessaire au chiffrement notamment des liens à usage unique (chaine aléatoire de 32 caractères)
	$gKey = "1234567891234567891234567891234";
	
	$gMailAddFrom = "no-reply@nomdedomaine.tld";
	$gMailNameFrom = "Réservation Salle BU";
	$gMailSMTP = "smtp.nomdedomaine.tld";				   
	
	// nombre de jour réservable + 1
	$gNbJourReservation = 9;
	
	// nombre salle par page
	$gNbSalleParPage = 5;
	
	// info connexion mysql
	$gaSql['user']       = "reservation-salle";
	$gaSql['password']   = "xxxxxxxxxxxxxxxx";
	$gaSql['db']         = "reservation-salle";
	$gaSql['server']     = "localhost";

	date_default_timezone_set("Europe/Paris");
	
	$joursemainecourt = [1 => 'lun.',
						 2 => 'mar.',
						 3 => 'mer.',
						 4 => 'jeu.',
						 5 => 'ven.',
						 6 => 'sam.',
						 7 => 'dim.',
					];
	$joursemaine = [1 => 'lundi',
					2 => 'mardi',
					3 => 'mercredi',
					4 => 'jeudi',
					5 => 'vendredi',
					6 => 'samedi',
					7 => 'dimanche',
				];
				
	// script matomo (pour serveur de statistiques) - laisser la variable vide pour désactiver la fonctionnalité
	$gScriptMatomo =
	"
	<!-- Matomo -->
	<script>
	  var _paq = window._paq = window._paq || [];
	  /* tracker methods like \"setCustomDimension\" should be called before \"trackPageView\" */
	  _paq.push(['trackPageView']);
	  _paq.push(['enableLinkTracking']);
	  (function() {
		var u=\"https://stats.nomdedomaine.tld/\";
		_paq.push(['setTrackerUrl', u+'matomo.php']);
		_paq.push(['setSiteId', 'xxx']);
		var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
		g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
	  })();
	</script>
	<!-- End Matomo Code -->
	";
	
	// lecture du fichier de configuration des salles
	$string = file_get_contents("config_bu.json");
	$bu = json_decode( $string, true );

?>
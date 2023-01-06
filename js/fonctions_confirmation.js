$(document).ready(function() 
{
  $('[data-toggle="tooltip"]').tooltip();
	
  $('.chargement').hide();
  $('.chargement').css("visibility","hidden");
  
  retourne_nom_mail(eppnc,resac);

});

function retourne_nom_mail(eppnc,resac) {
	
	$('#attente').html("<div class='alert alert-secondary' role='alert'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Vérification de votre compte lecteur en cours...</div>");
	
	// appel Ajax
	$.ajax({
		url: "retourne_nom_mail.php", 
		data: "eppnc="+eppnc,
		type: "POST", 
		dataType: 'json',
		success: function(json) {
			
			$('#attente').html("<div class='alert alert-secondary' role='alert'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Vérification de votre compte lecteur en cours...</div>");
			
			switch (json.reponse)
			{
				case "OK":
				$('#attente').html("<div class='alert alert-secondary' role='alert'><i class='fa fa-check-circle' aria-hidden='true'></i> "+json.nom+", la vérification de votre compte lecteur est terminée.</div>");
				
				nomc = json.nomc;
				mailc = json.mailc;
				
				validation_reservation(eppnc, resac, mailc);

				break;
				
				case "KO":
				$('#attente').html("<div class='alert alert-danger' role='alert'>" + json.message + "</div>");
				break;

				default:
				$('#attente').html("<div class='alert alert-danger' role='alert'>Erreur : " + json.message + "</div>");

			}
		},
		beforeSend: function(){
			// debut animation pendant envoi
			$('#attente').html("<div class='alert alert-secondary' role='alert'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Vérification de votre compte lecteur en cours...</div>");
				},
		complete: function(){
			// fin animation pendant envoi
			
		},
		error: function(){
			// fin animation pendant envoi
			$('#attente').html("<div class='alert alert-danger' role='alert'><i class='fa fa-times' aria-hidden='true'></i> Erreur inconnue, veuillez contacter le SI du SCDI...</div>");
		}
		
	});

	return false; // j'empêche le navigateur de soumettre lui-même le formulaire
	
}

function validation_reservation(eppnc, resac) {
	
	$('#validation').html("<div><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Validation de la réservation en cours...</div>");
	
	// appel Ajax
	$.ajax({
		url: "validation_reservation.php", 
		data: "eppnc="+eppnc+"&resac="+resac,
		type: "POST", 
		dataType: 'json',
		success: function(json) {
			
			switch (json.reponse)
			{
				case "OK":

				$('#validation').html("<div class='alert alert-success' role='alert'>" + json.message1 + "</div>");

				// envoi du mail
				envoi_mail(eppnc, mailc, nomc, resac, json.requestidc, json.messagemail);
				
				break;
								
				case "KO":
				$('#validation').html("<div class='alert alert-danger' role='alert'>" + json.message1 + "</div>");
				$('#home').html("<div class='alert alert-secondary' role='alert'>" + json.message2 + "</div>");
				break;

				default:
				$('#validation').html("<div class='alert alert-danger' role='alert'>Erreur : " + json.message1 + "</div>");

			}
		},
		beforeSend: function(){
			// debut animation pendant envoi
			$('#validation').html("<div><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Validation de la réservation en cours...</div>");
				},
		complete: function(){
			// fin animation pendant envoi
			
		},
		error: function(){
			// fin animation pendant envoi
			$('#validation').html("<div class='alert alert-danger' role='alert'><i class='fa fa-times' aria-hidden='true'></i> Erreur inconnue...</div>");
		}
		
	});

	return false; // j'empêche le navigateur de soumettre lui-même le formulaire
	
}

function envoi_mail(eppnc, mailc, nomc, resac, requestidc, message) {
	
	$('#mail').html("<div><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Envoi du mail en cours...</div>");
	
	// appel Ajax
	$.ajax({
		url: "envoi_mail.php", 
		data: "eppnc="+eppnc+"&mailc="+mailc+"&nomc="+nomc+"&resac="+resac+"&requestidc="+requestidc+"&message="+message,
		type: "POST", 
		dataType: 'json',
		success: function(json) {
			
			switch (json.reponse)
			{
				case "OK":
				$('#mail').html("<div class='alert alert-success' role='alert'>" + json.message1 + "</div>");
				$('#home').html("<div class='alert alert-secondary' role='alert'>" + json.message2 + "</div>");
				break;
				
				case "KO":
				$('#mail').html("<div class='alert alert-danger' role='alert'>" + json.message1 + "</div>");
				$('#home').html("<div class='alert alert-secondary' role='alert'>" + json.message2 + "</div>");
				break;

				default:
				$('#mail').html("<div class='alert alert-danger' role='alert'>Erreur : " + json.message1 + "</div>");

			}
		},
		beforeSend: function(){
			// debut animation pendant envoi
			$('#mail').html("<div><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Envoi du mail en cours...</div>");
				},
		complete: function(){
			// fin animation pendant envoi
			
		},
		error: function(){
			// fin animation pendant envoi
			$('#mail').html("<div class='alert alert-danger' role='alert'><i class='fa fa-times' aria-hidden='true'></i> impossible d'envoyer le mail : erreur inconnue...</div>");
		}
		
	});

	return false; // j'empêche le navigateur de soumettre lui-même le formulaire
	
}




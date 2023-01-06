$(document).ready(function() 
{
  $('[data-toggle="tooltip"]').tooltip();
	
  $('.chargement').hide();
  $('.chargement').css("visibility","hidden");
  // alert(eppnc+'/'+requestidc);
  suppression_reservation(eppnc, requestidc);

});

function suppression_reservation(eppnc, requestidc) {
	
	$('#attente').html("<div class='alert alert-secondary' role='alert'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Suppression de la réservation en cours...</div>");
	
	// appel Ajax
	$.ajax({
		url: "supprime_reservation_exec.php", 
		data: "eppnc="+eppnc+"&requestidc="+requestidc,
		type: "POST", 
		dataType: 'json',
		success: function(json) {
			
			$('#attente').html("<div class='alert alert-secondary' role='alert'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Suppression de la réservation en cours...</div>");
			
			switch (json.reponse)
			{
				case "OK":
				$('#attente').html("<div class='alert alert-success' role='alert'>" + json.message1 + "</div>");
				$('#home').html("<div class='alert alert-secondary' role='alert'>" + json.message2 + "</div>");

				break;
				
				case "KO":
				$('#attente').html("<div class='alert alert-danger' role='alert'>" + json.message1 + "<br/></div>");
				$('#home').html("<div class='alert alert-secondary' role='alert'>" + json.message2 + "</div>");
				
				break;

				default:
				$('#attente').html("<div class='alert alert-danger' role='alert'>Erreur : " + json.message1 + "</div>");

			}
		},
		beforeSend: function(){
			// debut animation pendant envoi
			$('#attente').html("<div class='alert alert-secondary' role='alert'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Suppression de la réservation en cours...</div>");
				},
		complete: function(){
			// fin animation pendant envoi
			
		},
		error: function(){
			// fin animation pendant envoi
			$('#attente').html("<div class='alert alert-danger' role='alert'><i class='fa fa-times' aria-hidden='true'></i> Erreur inconnue...</div>");
		}
		
	});

	return false; // j'empêche le navigateur de soumettre lui-même le formulaire
	
}




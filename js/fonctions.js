var xhr;

$(document).ready(function() 
{
  $('[data-toggle="tooltip"]').tooltip();
	
  $('.dropdown .busel').on('click', function(){
		$('#dropdownMenuButton').html("<i class='fa fa-building-o' aria-hidden='true'></i> " + $(this).text());
		$('#dropdownMenuButton').data("id",$(this).data("id"));
		$('#div_header').css('background-image', 'url(' + 'images/'+$(this).data("id")+'.jpg' + ')');
		
		$('#url_bu').html("<i class='fa fa-link' aria-hidden='true'></i> <a title='Accéder la page web de la bibliothèque' target='_blank' href='" + $(this).data("url") + "'>Page web de la bibliothèque " + $(this).text() + "</a>");
		
		if (initdate=="")
			var datejour=new Date();//date du jour
		else
			var datejour=new Date(initdate);
		
		// on rempli les disponibilités pour aujourd'hui Y-m-d
		jour_ouverture_bu($(this).data("id"),$(this).text(), $.format.date(datejour,"yyyy-MM-dd"));
	
  });
  $('.chargement').hide();
  $('.chargement').css("visibility","hidden");
  // Todo : mise en cache plus propre ?
  // var img1=new Image();
  // img1.src="images/MBUD.jpg";
  // var img2=new Image();
  // img2.src="images/MBUS.jpg";

});

function jour_ouverture_bu(codebu, nombu, jour) {
	
	var jour_ouverture = "<div class='badge badge-light'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Récupération des horaires d'ouverture de la "+nombu+" en cours...<div>";

	$('#jour_ouverture').html(jour_ouverture);

	if(xhr && xhr.readyState != 4){
            xhr.abort();
        }
		
	// appel Ajax
	$.ajax({
		url: "jour_ouverture.php", 
		data: "codebu="+codebu+"&jour="+jour,
		type: "POST", 
		dataType: 'json',
		success: function(json) {
			
			switch (json.reponse)
			{
				case "OK":
					
					$('#jour_ouverture').html("<div class='card' style='padding:10px;'>" + json.message + "</div>");
					disponibilite_bu(json.codebu, json.nombu, json.jour, 1, "");

				break;
								
				case "KO":
					$('#jour_ouverture').html("<div class='alert alert-danger' role='alert'>" + json.message + "</div>");
				break;

				default:
					$('#jour_ouverture').html("<div class='alert alert-danger' role='alert'>Erreur : " + json.message + "</div>");

			}
			
			if (json.aidetexte != "")
				$('#aide_texte').html(json.aidetexte);
		},
		beforeSend: function(){
			// debut animation pendant envoi
			$('#information').html("<div class='badge badge-light'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Récupération des horaires d'ouverture de la "+nombu+" en cours...</div>");
		},
		complete: function(){
			// fin animation pendant envoi
		},
		error: function(){
			// fin animation pendant envoi
			$('#information').html("<div class='alert alert-danger' role='alert'><i class='fa fa-exclamation-triangle' aria-hidden='true'></i> Erreur inconnue, impossible de continuer.</div>");
		}
	
	});
}

function disponibilite_bu(codebu, nombu, jour, page, filtre) {
	
	$('#disponibilite_attente_message').show();
	$('#disponibilite_attente_message').html("<div class='badge badge-light'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Récupération des disponibilités pour la "+nombu+" en cours...</div>");
	$('.tooltip').remove();
	
	if(xhr && xhr.readyState != 4){ // on annule l'éventuel chargement d'une autre page
            xhr.abort();
			$('#disponibilite_attente_message').show();
        }
		
	// appel Ajax
	xhr = $.ajax({
		url: "disponibilite.php", 
		data: "codebu="+codebu+"&jour="+jour+"&page="+page+"&filtre="+filtre,
		type: "POST", 
		dataType: 'json',
		success: function(json) {
			
			var disponibilite_attente_message = "<div class='badge badge-light'><i class='fa fa-check text-success' aria-hidden='true'></i> Récupération terminée</div>";
			
			$('#disponibilite_attente_message').html(disponibilite_attente_message);
			
			// pose problème si on reclique sur un autre jour avant fin animation...
			// $('#disponibilite_attente_message').delay(3000).fadeOut(500, function() {
				// $('#disponibilite_attente_message').hide();
			// });
			
			$('.tooltip').remove();
			
			$('#btn_critere_salle').html(json.btn_critere);

			switch (json.reponse)
			{
				case "OK":
					$('#disponibilite').html("<div>" + json.message + "</div>");
					break;
								
				case "KO":
					$('#disponibilite').html("<div class='alert alert-danger' role='alert'>" + json.message + "</div>");
					break;

				default:
					$('#disponibilite').html("<div class='alert alert-danger' role='alert'>Erreur : " + json.message + "</div>");

			}
		},
		beforeSend: function(){
			// debut animation pendant envoi
			$('#disponibilite_attente_message').show();
			// $('#disponibilite_attente_message').html("<div class='badge badge-light'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Récupération des disponibilités <span class='d-none d-sm-block'>pour la "+nombu+" le "+$.format.date(jour + " 12:00:00","dd/MM")+" </span>en cours...</div>");
			$('#disponibilite_attente_message').html("<div class='badge badge-light'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Récupération des disponibilités pour la "+nombu+" le "+$.format.date(jour + " 12:00:00","dd/MM")+" en cours...</div>");
			
		},
		complete: function(){
			// fin animation pendant envoi
		},
		error: function(){
			// fin animation pendant envoi
			$('#disponibilite_attente_message').show();
			$('#disponibilite_attente_message').html("<div class='alert alert-danger' role='alert'><i class='fa fa-exclamation-triangle' aria-hidden='true'></i> Erreur inconnue, impossible de continuer.</div>");
		}
	
	});
	
	return false;
}

function ouvrir_modal_reservation()
{
	if ($( "#reservation ul li").length	> 0 )
	{
		var codebu = $('#dropdownMenuButton').data('id');
		var mmsid = $( "#reservation ul li" ).first().data("mmsid");
		var holdingid = $( "#reservation ul li" ).first().data("holdingid");
		var itemid = $( "#reservation ul li" ).first().data("itemid");
		var cb = $( "#reservation ul li" ).first().data("cb");
		var debut = $( "#reservation ul li" ).first().data("horaire");
		var fin = $( "#reservation ul li" ).last().data("horairefin");		
		
		$('#crenauxchoisis').html("");
		
		$('#crenauxchoisis').append("<form action='connexion.php' method='POST' name='formreservation' id='formreservation'>"+
								"<input type='hidden' name='idp' id='idp' value=''>"+		
								"<input type='hidden' name='codebu' id='codebu' value='"+codebu+"'>"+
								"<input type='hidden' name='mmsid' id='mmsid' value='"+mmsid+"'>"+
								"<input type='hidden' name='holdingid' id='holdingid' value='"+holdingid+"'>"+
								"<input type='hidden' name='itemid' id='itemid' value='"+itemid+"'>"+
								"<input type='hidden' name='cb' id='cb' value='"+cb+"'>"+
								"<input type='hidden' name='debut' id='debut' value='"+debut+"'>"+
								"<input type='hidden' name='fin' id='fin' value='"+fin+"'>"+
								"</form>");

		$('#crenauxchoisis').append($('#dropdownMenuButton').html() + " - ");
		
		$('#crenauxchoisis').append($("#reservation ul li" ).first().data("nomsalle") + "<br/>");
		
		$('#crenauxchoisis').append("<i class='fa fa-calendar-check-o' aria-hidden='true'></i> le "+$.format.date($( "#reservation ul li" ).first().data("horaire"),"dd/MM/yyyy")+' de '+$.format.date($( "#reservation ul li" ).first().data("horaire"),"HH:mm"));	

		$('#crenauxchoisis').append(" à " + $.format.date($( "#reservation ul li" ).last().data("horairefin"),"HH:mm"));
		
		$('#validationmodal').modal('toggle');
		
	}
	
}

function supprimer_creneaux(){
	
	
	$( ".creneauchoisi" ).each(function( index ) {
		
		// alert($(this).attr("id"));
		
	    var rounded = "";
		if ($(this).hasClass("rounded-left"))
			rounded = " rounded-left";
		if ($(this).hasClass("rounded-right"))
			rounded = " rounded-right";
		
		$(this).data("selection","0");
		// $(this).prop('title', "Créneau disponible, cliquer pour sélectionner ce créneau");
		$(this).removeClass();
		$(this).addClass("salle2 creneaudispo text-white"+rounded);
		
		$(this).attr('data-original-title', 'Créneau disponible, cliquer pour sélectionner ce créneau').tooltip('hide');
	});
	
	$( "#reservation ul li" ).each(function( index ) {
		
		$(this).remove();

	});

	$("#supprimer").addClass("d-none");
	$("#supprimer").removeClass("d-block");
	
	$('#reserver').addClass("d-none");
	$('#reserver').removeClass("d-block");
	
	$('#reservation').html("<ul></ul>");
	$("#reservationvide").show();
	$('#reservationvide').css("visibility","visible");
			
}

function choix_creneau(creneau,max,min){
	
	// creneau.tooltip('hide');
	$('.tooltip').remove();
	
	var creneautxt = "";
	var rounded = "";
	if (creneau.hasClass("rounded-left"))
		rounded = " rounded-left";
	if (creneau.hasClass("rounded-right"))
		rounded = " rounded-right";
	
	if (creneau.data("selection") == "0") 
	{	
				
		// vérifie si aucun creneau est sélectionné
		if ($( "#reservation ul li").length==0) 
		{
			creneautxt = creneau.data("creneautxt");
			
			// ajout du créneau initial
			$("#reservation ul").append('<li style="list-style: none;" id="'+creneau.data("itemid")+'-'+creneau.data("num")+'" data-mmsid="'+creneau.data("mmsid")+'" data-holdingid="'+creneau.data("holdingid")+'" data-itemid="'+creneau.data("itemid")+'" data-cb="'+creneau.data("cb")+'" data-num="'+creneau.data("num")+'" data-horaire="'+creneau.data("horaire")+'" data-horairefin="'+creneau.data("horairefin")+'" data-nomsalle="'+creneau.data("nomsalle")+'"><span style="font-style: oblique;" class="tab"><i class="fa fa-calendar-plus-o\ aria-hidden="true"></i> '+creneau.data("nomsalle")+' le '+creneautxt+'</span></a></li>');
			
			// on peut changer le creneau
			creneau.removeClass();
			// creneau.prop('title', 'Créneau sélectionné pour réservation');
			
			creneau.data("selection","1");
			creneau.addClass("salle2 creneauchoisi text-white"+rounded);
			
			// creneau.tooltip('hide')
			creneau.attr('data-original-title', 'Créneau sélectionné pour réservation').tooltip('show');
		}
		else if ($( "#reservation ul li").length>0 && $( "#reservation ul li").length<max)
		{
			
			// on vérifie si le creneau est dans la même salle
			if ($( "#reservation ul li" ).first().data("itemid") == creneau.data("itemid"))
			{
				// on vérifie si on ajoute le creneau à la fin
				if (creneau.data("num")-0.5==$( "#reservation ul li" ).last().data("num"))
				{
					//on modifie les horaires du créneau qui devient l'avant dernier
					
					// ajout du créneau après
					$("#reservation ul").append('<li style="list-style: none;" id="'+creneau.data("itemid")+'-'+creneau.data("num")+'" data-mmsid="'+creneau.data("mmsid")+'" data-holdingid="'+creneau.data("holdingid")+'" data-itemid="'+creneau.data("itemid")+'" data-cb="'+creneau.data("cb")+'" data-num="'+creneau.data("num")+'" data-horaire="'+creneau.data("horaire")+'" data-horairefin="'+creneau.data("horairefin")+'" data-nomsalle="'+creneau.data("nomsalle")+'"><span style="font-style: oblique;" class="tab"><i class="fa fa-calendar-plus-o\ aria-hidden="true"></i> '+creneau.data("nomsalle")+' le '+creneau.data("creneautxt")+'</span></a></li>');
					
					// on peut changer le creneau
					creneau.data("selection","1");
					// creneau.prop('title', 'Créneau sélectionné pour réservation');
					
					creneau.removeClass();
					creneau.addClass("salle2 creneauchoisi text-white"+rounded);
					
					// creneau.tooltip('hide')
					creneau.attr('data-original-title', 'Créneau sélectionné pour réservation').tooltip('show');
			  
				}
				// on vérifie si on ajoute le creneau au debut
				else if (creneau.data("num")+0.5==$( "#reservation ul li" ).first().data("num"))
				{
					//on modifie les horaires du créneau qui devient le 2ème
					
					// ajout du créneau avant
					$("#reservation ul").prepend('<li style="list-style: none;" id="'+creneau.data("itemid")+'-'+creneau.data("num")+'" data-num="'+creneau.data("num")+'" data-mmsid="'+creneau.data("mmsid")+'" data-holdingid="'+creneau.data("holdingid")+'" data-cb="'+creneau.data("cb")+'" data-itemid="'+creneau.data("itemid")+'" data-horaire="'+creneau.data("horaire")+'" data-horairefin="'+creneau.data("horairefin")+'" data-nomsalle="'+creneau.data("nomsalle")+'"><span style="font-style: oblique;" class="tab"><i class="fa fa-calendar-plus-o\ aria-hidden="true"></i> '+creneau.data("nomsalle")+' le '+creneau.data("creneautxt")+'</span></a></li>');

					// on peut changer le creneau
					creneau.removeClass();
					// creneau.prop('title', 'Créneau sélectionné pour réservation');
					
					creneau.data("selection","1");
					creneau.addClass("salle2 creneauchoisi text-white"+rounded);
					
					// creneau.tooltip('hide')
					creneau.attr('data-original-title', 'Créneau sélectionné pour réservation').tooltip('show');
				}
				else
					alertpopup(creneau.data("itemid"),"danger","Vous ne pouvez réserver que des créneaux contigus");	
			}
			else
				alertpopup(creneau.data("itemid"),"danger","Vous ne pouvez réserver que des créneaux dans la même salle");	
		}
		else {
			var msgalerte = "Vous ne pouvez pas réserver plus de "+max+" créneaux de 30 minutes";
			
			if ($( "#reservation ul li" ).first().data("itemid") != creneau.data("itemid"))
				msgalerte = msgalerte + " (et seulement dans la même salle)";
			
			alertpopup(creneau.data("itemid"),"danger",msgalerte);
		
		}
		
		$("#supprimer").removeClass("d-none");
		$("#supprimer").addClass("d-block");
		
		if ($( "#reservation ul li").length>=min)
		{
			$('#reserver').removeClass("d-none");
			$('#reserver').addClass("d-block");
			$("#reservationvide").hide();
			$('#reservationvide').css("visibility","hidden");
		}
		
	}
	else if (creneau.data("selection") == "1") 
	{
		// on vérifie qu'on enlève pasun créneau dans la sélection
		if (creneau.data("num") == $( "#reservation ul li" ).first().data("num") || creneau.data("num") == $( "#reservation ul li" ).last().data("num"))
		{
			$( "#reservation ul li" ).each(function( index ) {
		
				if ($(this).attr("id") == creneau.data("itemid")+'-'+creneau.data("num"))
					$(this).remove();

			});
			
			creneau.removeClass();
			creneau.addClass("salle2 creneaudispo text-white"+rounded);
			creneau.data("selection","0");
			// creneau.prop('title', "Créneau disponible, cliquer pour sélectionner ce créneau");
			// creneau.tooltip('hide')
			creneau.attr('data-original-title', 'Créneau disponible, cliquer pour sélectionner ce créneau').tooltip('show');
		}
		else
			alertpopup(creneau.data("itemid"),"danger","Vous ne pouvez pas enlever un créneau à l'intérieur de la selection");

		if ($( "#reservation ul li").length<min)
		{			
			$('#reserver').addClass("d-none");
			$('#reserver').removeClass("d-block");
			
			$("#reservationvide").show();
			$('#reservationvide').css("visibility","visible");
		}
		
		// s'il n'y a plus de creneau sélectionné
		if ($( "#reservation ul li").length==0)
		{
			$("#supprimer").addClass("d-none");
			$("#supprimer").removeClass("d-block");

			$('#reservation').html("<ul></ul>");
		}	
	}
	else if (creneau.data("selection") == "2") 
	{
		alertpopup(creneau.data("itemid"),"danger","Vous ne pouvez pas réserver ce créneau");
	}

}

function alertpopup(id, couleur, message)
{	
	$("#alert-"+id).html('<span id="alert-action-'+id+'" title="Fermer" style="cursor: pointer;" class="badge badge-'+couleur+' text-wrap text-left">'+message+' <i class="fa fa-times" aria-hidden="true"></i></span>');
				 
	$("#alert-action-"+id).fadeIn(500).delay(3000).fadeOut(500, function() {
		$( "#alert-action-"+id ).remove();
	});
	
	$("#alert-action-"+id).on('click', function(){
		$( "#alert-action-"+id ).remove();
	});
}

function session_write_variable(id,valeur)
{
	$('#disponibilite_attente_message').html("<div class='badge badge-light'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Modification</div>");
		
		$.ajax({
		url: "session_write.php", 
		data: "variable=" + id +"&valeur="+valeur,
		type: "GET", 
		dataType: 'json',
		success: function(json) {
			
			switch (json.reponse)
			{
				case "OK":
					$('#disponibilite_attente_message').html("<div class='badge badge-light'><i class='fa fa-check text-success' aria-hidden='true'></i> Modification terminée</div>");
				break;
								
				case "KO":
					$('#disponibilite_attente_message').html("<div class='badge badge-light'><i class='fa fa-exclamation-triangle text-danger' aria-hidden='true'></i> Erreur lors de la modification</div>");
				break;

				default:
					

			}
			
		},
		beforeSend: function(){
			// debut animation pendant envoi
			$('#disponibilite_attente_message').html("<div class='badge badge-light'><i class='fa fa-cog fa-spin' aria-hidden='true'></i> Modification</div>");
		},
		complete: function(){
			// fin animation pendant envoi
		},
		error: function(){
			// fin animation pendant envoi
			$('#disponibilite_attente_message').html("<div class='badge badge-light'><i class='fa fa-exclamation-triangle text-danger' aria-hidden='true'></i> Erreur inconnue lors de la modification</div>");
		}
	
		});
}

function masque_info(id)
{
	// $( '#div_session_write').load('session_write.php?variable=info-' + id +'&valeur=1');
	session_write_variable('info-'+id,1);
	
}

function masque_description(id)
{
	$('.tooltip').remove();
	if ($('#description-' + id ).attr("data-visible") == 1)
	{
		// on cache
		$( '#description-' + id ).attr('data-visible', '0');
		
		// $( '#description-' + id ).slideUp("slow");
		
		$( '#description-' + id ).addClass("invisible").removeClass("visible");
		// $( '#description-' + id ).removeClass("visible");
		
		$( '#description-' + id ).hide();

		$( '#chevron-' + id ).html("<i class=\"fa fa-angle-double-down fa-border\" aria-hidden=\"true\"></i>");
		$( '#chevron-' + id ).attr('data-original-title', 'Montrer la description').tooltip('show');
		// $( '#div_session_write').load('session_write.php?variable=description-' + id +'&valeur=0');
		session_write_variable('description-'+id,0);
	}
	else
	{
		// on montre
		$( '#description-' + id ).attr('data-visible', '1');
		
		// $( '#description-' + id ).slideDown("slow");
		
		$( '#description-' + id ).addClass("visible").removeClass("invisible");
		// $( '#description-' + id ).removeClass("invisible");
		
		$( '#description-' + id ).css("display","inline-block")
		$( '#description-' + id ).show();
		
		$( '#chevron-' + id ).html("<i class=\"fa fa-angle-double-up fa-border\" aria-hidden=\"true\"></i>");
		$( '#chevron-' + id ).attr('data-original-title', 'Masquer la description').tooltip('show');
		// $( '#div_session_write').load('session_write.php?variable=description-' + id +'&valeur=1');
		session_write_variable('description-'+id,1);
	}
}


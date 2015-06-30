function checkCalendar(reservation, recurring){
		   var heure_debut = $('#heure_debut').val();
		   var heure_fin = $('#heure_fin').val();
		   var lieu = $('#lieu').val();
		   var date_debut = $('#date_debut').val();
		   if(!recurring){
			   $.post("functions/check_calendar.php",{date_debut, heure_debut, heure_fin, lieu, recurring}).done(function(data){
				   if(data != 0){
					   $('#error_message').empty();
					   $('#error_message').append('Cette plage horaire est déjà utilisée pour un cours ou une réservation.');
					   $('.confirm-add').prop('disabled', true);
				   } else {
					   $('#error_message').empty();
					   $('.confirm-add').prop('disabled', false);
					   // Calculer le tarif d'une réservation
					   if(reservation){
						   var prestation = $('#prestation').val();
						   $.post("functions/resa_calcul_prix.php", {prestation, date_debut, heure_debut, heure_fin, lieu}).done(function(data){
							   $('input#prix_calcul').empty();
							   $('input#prix_calcul').val(data);
						   });
					   }
				   }
			   });
		   } else {
			   var date_fin = $('#date_fin').val();
			   var frequence_repetition = $('input[name=frequence_repetition]:checked').val();
			   $.post("functions/check_calendar.php", {date_debut, frequence_repetition, date_fin, heure_debut, heure_fin, lieu, recurring}).done(function(data){
				  if(data != 0){
					  $('#error_message').empty();
					  $('#error_message').append('Une des plages est déjà utilisée pour un cours ou une réservation.');
					  $('.confirm-add').prop('disabled', true);
				  } 
			   });
		   }
	   }
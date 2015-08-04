/*
Le fichier tools.js contient toutes les fonctions javascript qui peuvent être utilisés par plusieurs fichiers, 
qu'elles soient les fonctions de notification, ou des fonctions plus utilitaires.
Dès que le document est prêt, tous les modaux et les fonctions qui doivent tourner de façon constantes sont lancées ici.
*/

$(document).ready(function(){
	var firstCount = 0; // Pour éviter la notification dès le rafraîchissement de la page.
	notifPassages(firstCount);
	notifCoursParticipants(firstCount);
	setInterval(notifPassages, 5000);
	$('[data-toggle="tooltip"]').tooltip();
    moment.locale("fra");
});

// FONCTIONS NOTIFICATIONS //
// Fonction de surveillance des passages enregistrés. Avertit l'utilisateur et met à jour le badge de notification en cas de nouveaux enregistrements.
function notifPassages(firstCount){
	$.post("functions/watch_records.php").done(function(data){
		if(data == 0){
			$("#badge-passages").hide();
		} else {
			if(data > $("#badge-passages").html() && firstCount!=0){$.notify("Nouveaux passages enregistrés", {globalPosition: "bottom right", className:"info"});}
			$("#badge-passages").show();
			$("#badge-passages").html(data);
		}
		firstCount = 1;
	})
}

// Surveille les participations à un cours non associés à un produit (abonnement, vente spontanée, invitation...)
function notifCoursParticipants(firstCount){
	$.post("functions/watch_cours_participants.php").done(function(data){
		if(data == 0){
			$("#badge-participants").hide();
		} else {
			if(data > $("#badge-participants").html() && firstCount != 0){
				$.notify("Nouvelles participations non associées.", {globalPosition: "bottom right", className:"info"});
			}
			$("#badge-participants").show();
			$("#badge-participants").html(data);
		}
	})
}

function showSuccessNotif(data){
	$.notify(data, {globalPosition:"right bottom", className:"success"});
}

// FONCTIONS UTILITAIRES //
// Insert la date d'aujourd'hui dans un input de type date supportant la fonctionnalité 
$("*[date-today='true']").click(function(){
    var today = new moment().format("YYYY-MM-DD");
    $(this).parent().prev().val(today);
});

// Convertit une date en temps relatif. (ex: "il y a un jour")
$(".relative-start").each(function(){
   $(this).html(moment($(this).html(), "YYYY-MM-DD HH:ii:ss", 'fr').fromNow());
});

// Vérifie si un adhérent existe dans la base de données
function ifAdherentExists(){
	var identite_prenom = $('#identite_prenom').val();
	var identite_nom = $('#identite_nom').val();
	$.post("functions/check_adherent.php", {identite_prenom, identite_nom}).done(function(data){
		if(data == 0){
			$('#err_adherent').empty();
			$('#err_adherent').append("Cet adhérent n'existe pas. Voulez-vous le créer ?");
			$('#create-user').show();
		} else {
			$('#err_adherent').empty();
			$('#create-user').hide();
		}
	});
}

// Effectue une inscription rapide dans le cas d'un adhérent inexistant à la réservation d'une salle ou l'achat d'un forfait
function addAdherent(){
	var identite_prenom = $('#identite_prenom').val();
	var identite_nom = $('#identite_nom').val();
	var rfid = $("[name='rfid']").val();
	var rue = $('#rue').val();
	var code_postal = $('#code_postal').val();
	var ville = $('#ville').val();
	var mail = $('#mail').val();
	var telephone = $('#telephone').val();
	var date_naissance = $('#date_naissance').val();
	$.post("functions/add_adherent.php", {identite_prenom, identite_nom, rfid, rue, code_postal, ville, mail, telephone, date_naissance}).done(function(data){
		$('#create-user').click();
		showSuccessNotif(data);
		ifAdherentExists();
	});
}

// Vérifie l'existence de jours chômés à l'ajout d'un évènement
function checkHoliday(){
   var date_debut = $('#date_debut').val();
   $.post("functions/check_holiday.php", {date_debut}).done(function(data){
       console.log(data);
       if(data != "0"){
           $("#holiday-alert").empty();
           $("#holiday-alert").append("Ce jour est chômé. Impossible d'ajouter une réservation à cette date.");
           $('.confirm-add').prop('disabled', true);
       } else {
           $('#holiday-alert').empty();
           $('.confirm-add').prop('disabled', false);
           checkCalendar(true, false);
       }
   });
}

// Vérifie que les champs obligatoires sont renseignés.
function checkMandatory(){
  if($(".mandatory").val() != '' || $(".mandatory").html() != ''){
      $("#submit-button").prop('disabled', false);
   } else {
       $(this).next().children('p').html("Ce champ est requis");
       $("#submit-button").prop('disabled', true);
   }
}

$(".draggable").draggable({
	snap: ".list-group",
	axis: "y"
});
$(".droppable").droppable({
	drag: function(event, ui){
		//$(this).height($(this).height + ui.draggable.height());
	},
	drop: function(event, ui){
		ui.draggable.detach().appendTo($(this));
	}
});
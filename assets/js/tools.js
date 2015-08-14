/*
Le fichier tools.js contient toutes les fonctions javascript qui peuvent être utilisés par plusieurs fichiers, 
qu'elles soient les fonctions de notification, ou des fonctions plus utilitaires.
Dès que le document est prêt, tous les modaux et les fonctions qui doivent tourner de façon constantes sont lancées ici.
*/

$(document).ready(function(){
	
	jQuery.expr[':'].regex = function(elem, index, match) {
		var matchParams = match[3].split(','),
			validLabels = /^(data|css):/,
			attr = {
				method: matchParams[0].match(validLabels) ? 
							matchParams[0].split(':')[0] : 'attr',
				property: matchParams.shift().replace(validLabels,'')
			},
			regexFlags = 'ig',
			regex = new RegExp(matchParams.join('').replace(/^s+|s+$/g,''), regexFlags);
		return regex.test(jQuery(elem)[attr.method](attr.property));
	}
	
	var firstCount = 0; // Pour éviter la notification dès le rafraîchissement de la page.
	notifPassages(firstCount);
	notifCoursParticipants(firstCount);
	notifEcheancesDues(firstCount);
	setInterval(notifPassages, 5000);
	setInterval(notifCoursParticipants, 5000);
	setInterval(notifEcheancesDues, 5000);
	$('[data-toggle="tooltip"]').tooltip();
    moment.locale("fra");
	
	// Démarre l'horloge
	tickClock();
	setInterval(tickClock, 1000);
	
	// Construit le tableau d'inputs obligatoires par formulaire
	var mandatories = [];
	$(".mandatory").each(function(){
		var inputName = $(this).attr('name');
		mandatories.push(inputName);
	}).blur(function(){
		var j = 0;
		for(var i = 0; i < mandatories.length; i++){
			if($("[name="+mandatories[i]+"]").val() != '' || $("[name="+mandatories[i]+"]").html() != ''){
				j++; // Incrémente le compteur d'input remplis et vide les éventuels messages d'erreurs indiquant que le champ est obligatoire
				$(this).next().children('p').empty();
			} else {
				// Affiche un message indiquant que le champ est obligatoire
				$(this).next().children('p').html("Ce champ est requis");
			}
		}
		// Si tous les inputs sont remplis, alors on autorise la soumission du formulaire
		if(j == mandatories.length){
			$("#submit-button").prop('disabled', false);
		} else {
			$("#submit-button").prop('disabled', true);
		}
	});
	
	// Filtre dynamique
	var $rows = $('#filter-enabled tr');
	$('#search').keyup(function(){
		var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
		$rows.show().filter(function(){
		   var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			return !~text.indexOf(val);
		}).hide();
	});
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

// Surveille le nombre d'échéances qui ne sont pas réglées après leur date
function notifEcheancesDues(firstCount){
	$.post("functions/watch_maturities.php").done(function(data){
		if(data == 0){
			$("#badge-echeances").hide();
		} else {
			if(data > $("#badge-echeances").html() && firstCount != 0){
				$.notify("De nouvelles échéances ont dépassé leur date.", {globalPosition: "bottom right", className:"info"});
			}
			$("#badge-echeances").show();
			$("#badge-echeances").html(data);
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
	var identite = $("#identite_nom").val().split(" ");
	if(identite == ''){
		$('#err_adherent').empty();
		$('#unpaid').hide();
		$('#create-user').hide();
		$("#maturities-checked").show();
	} else {
		var identite_prenom = identite[0];
		var identite_nom = identite[1];
		$.post("functions/check_adherent.php", {identite_prenom, identite_nom}).done(function(data){
			if(data == 0){
				$('#err_adherent').empty();
				$('#unpaid').hide();
				$('#err_adherent').append("Cet adhérent n'existe pas. Voulez-vous le créer ?");
				$('#create-user').show();
				$("#maturities-checked").show();
			} else {
				$('#err_adherent').empty();
				$('#unpaid').hide();
				$('#create-user').hide();
				$("#maturities-checked").show();
				checkMaturities(data);
			}
		});
	}
}

// Vérifie si un adhérent a des échéances impayées lors de la vente d'un forfait
function checkMaturities(data){
   var search_id = data;
   $.post('functions/check_unpaid.php', {search_id}).done(function(maturities){
	   if(maturities != 0){
			$('#err_adherent').empty();
			$('#unpaid').show();
		   $("#maturities-checked").hide();
		} else {
			$('#err_adherent').empty();
			$('#unpaid').hide();
			$("#maturities-checked").show();
		}
   })
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
		$("#identite_nom").val(identite_prenom+" "+identite_nom);
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

// Afficher et met à jour l'horloge
function tickClock(){
   var now = moment().locale('fr').format("DD MMMM YYYY HH:mm:ss");
   $("#current-time").html(now);
   $(".panel").each(function(){
	   $(this).find(".cours-count").html($(this).find(".list-group-item").length);
	   $(this).find(".cours-count-checked").html($(this).find(".list-group-item-success").length);
   })
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
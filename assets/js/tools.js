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
	window.numberProduits = 1; // Articles dans le panier
	fillShoppingCart();
	notifPassages(firstCount);
	notifCoursParticipants(firstCount);
	notifEcheancesDues(firstCount);
	notifPanier();
	setInterval(notifPassages, 15000);
	setInterval(notifCoursParticipants, 30000);
	setInterval(notifEcheancesDues, 30000);
	$('[data-toggle="tooltip"]').tooltip();
	moment.locale("fra");

	// Démarre l'horloge
	tickClock();
	setInterval(tickClock, 1000);

	// Construit le tableau d'inputs obligatoires par formulaire
	var mandatories = [];
	$(".mandatory").each(function(){
		$(this).prev("label").append(" <span class='span-mandatory' title='Ce champ est obligatoire'>*</span>");
		var inputName = $(this).attr('name');
		mandatories.push(inputName);
		$(this).parent().addClass('has-feedback');
		$(this).parent().append("<span class='glyphicon form-control-feedback'></span>");
		if($(this).html() != '' || $(this).val() != ''){
			$(this).parent().addClass('has-success');
			$(this).next("span").addClass('glyphicon-ok');
		}
	}).on('focus keyup change blur', function(){
		if($(this).html() != '' || $(this).val() != ''){
			$(this).parent().removeClass('has-error');
			$(this).parent().addClass('has-success');
			$(this).next("span").removeClass('glyphicon-remove');
			$(this).next("span").addClass('glyphicon-ok');
		} else {
			$(this).parent().removeClass('has-success');
			$(this).parent().addClass('has-error');
			$(this).next("span").removeClass('glyphicon-ok');
			$(this).next("span").addClass('glyphicon-remove');
		}
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
			$(".submit-button").prop('disabled', false);
		} else {
			$("#submit-button").prop('disabled', true);
			$(".submit-button").prop('disabled', false);
		}
	});

	// Editables
	$(".editable").each(function(){
		var editIcon = "<span class='glyphicon glyphicon-edit' style='display:none; float:right;'></span>";
		$(this).after(editIcon);
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

	$(".has-check").on('blur keyup focus',function(){
		var field = $(this);
		var identite = $(this).val();
		var token = $(this).attr('name').substr(12);
		$.post("functions/check_adherent.php", {identite}).done(function(data){
			if(data == 0){
				if($(":regex(id,^unknown-user)").length == 0){
					var addOptions = "<div id='unknown-user"+token+"'>";
					addOptions += "<p>Aucun résultat. Voulez vous inscrire cet adhérent ?</p>";
					addOptions += "<a href='#user-details"+token+"' role='button' class='btn btn-info btn-block' value='create-user' id='create-user"+token+"' data-toggle='collapse' aria-expanded='false' aria-controls='userDetails'>Ouvrir le formulaire de création</a>";
					addOptions += "<div id='user-details"+token+"' class='collapse'><div class='well'>";
					addOptions += "<div class='form-group'><label class='control-label'>Prénom</label><input type='text' name='identite_prenom' id='identite_prenom' class='form-control input-lg' placeholder='Prénom'></div>";
					addOptions += "<div class='form-group'><label class='control-label'>Nom</label><input type='text' name='identite_nom' id='identite_nom' class='form-control input-lg' placeholder='Nom'></div>";
					addOptions += "<div class='form-group'><label class='control-label'>Adresse postale</label><input type='text' name='rue' id='rue' placeholder='Adresse' class='form-control input-lg'></div>";
					addOptions += "<div class='form-group'><input type='text' name='code_postal' id='code_postal' placeholder='Code Postal' class='form-control input-lg'></div>";
					addOptions += "<div class='form-group'><input type='text' name='ville' id='ville' placeholder='Ville' class='form-control input-lg'></div>";
					addOptions += "<div class='form-group'><label for='text' class='control-label'>Adresse mail</label><input type='mail' name='mail' id='mail' placeholder='Adresse mail' class='form-control input-lg'></div>";
					addOptions += "<div class='form-group'><label for='telephone' class='control-label'>Numéro de téléphone</label><input type='text' name='telephone' id='telephone' placeholder='Numéro de téléphone' class='form-control input-lg'></div>";
					addOptions += "<div class='form-group'><label for='date_naissance' class='control-label'>Date de naissance</label><input type='date' name='date_naissance' id='date_naissance' class='form-control input-lg'></div>";
					addOptions += "<a class='btn btn-primary' onClick='addAdherent()'>AJOUTER</a>";
					addOptions += "</div></div></div>";
					field.after(addOptions);
				}
			} else {
				$(":regex(id,^unknown-user)").remove();
				$(".has-name-completion").val(identite);
			}
		})
	})

	$(".statut-banque").click(function(){
		var echeance_id = $(this).parents("td").children("input[name^='echeance']").val();
		var container = $(this).parents("td");
		$.post("functions/encaisser_echeance.php", {echeance_id}).done(function(data){
			showSuccessNotif(data);
			container.empty();
			var date = moment().format('DD/MM/YYYY');
			container.html("<span class='label label-success'>Encaissée le "+date+"</span>");
		})
	})
}).on('click', '.editable', function(){
	var methods = [
		"Carte bancaire",
		"Chèque n°",
		"Espèces",
		"Virement compte à compte",
		"Chèques vacances",
		"En attente"
	];
	// Dès le clic, on récupère la valeur initiale du champ (peu importe le type de champ)
	var initialValue = $(this).val();
	if(initialValue == ""){initialValue = $(this).html();}
	console.log(initialValue);

	// On récupère ensuite l'id du champ modifié
	var token = $(this).attr('id');

	// Si la valeur correspond à une date, alors l'action de modification sera différente
	if(initialValue.indexOf('/') != -1){
		var initialDay = initialValue.substr(0,2);
		var initialMonth = initialValue.substr(3,2);
		var initialYear = initialValue.substr(6,4);
		var initialDate = moment(new Date(initialYear+'-'+initialMonth+'-'+initialDay)).format("YYYY-MM-DD");
		$(this).replaceWith("<input type='date' class='form-control editing' id='"+token+"' value="+initialDate+">");
	} else {
		$(this).replaceWith("<input type='text' class='form-control editing' id='"+token+"' value="+initialValue+">");
	}
	$(".editing").focus();
	$(":regex(id,^methode_paiement)").autocomplete({
		source: methods
	})
	$(".editing").blur(function(){
		var editedValue = $(this).val();
		if(editedValue != "" && editedValue != initialValue){
			if(editedValue.indexOf('-') != -1){
				var editedDate = moment(new Date(editedValue)).format("DD/MM/YYYY");
				$(this).replaceWith("<span class='editable' id='"+token+"'>"+editedDate+"</span>");
			} else {
				$(this).replaceWith("<span class='editable' id='"+token+"'>"+editedValue+"</span>");
			}
			uploadChanges(token, editedValue);
		} else {
			$(this).replaceWith("<span class='editable' id='"+token+"'>"+initialValue+"</span>");
		}
	});
}).on('mouseenter', '.editable', function(){
	$(this).next().show();
}).on('mouseleave', '.editable', function(){
	$(this).next().hide();
}).on('click', '.statut-salsabor', function(){
	var echeance_id = $(this).parent("td").children("input[name='echeance-id']").val();
	console.log($(this).parent("td").children("input[name='echeance-id']").val());
	var label = $(this);
	$.post("functions/validate_echeance.php", {echeance_id}).done(function(data){
		var answerLabel = "<span class='label label-";
		var date = moment().format('DD/MM/YYYY');
		switch(data){
			case '0':
				answerLabel += "info'><span class='glyphicon glyphicon-option-horizontal'></span> Dépôt à venir</span><span class='statut-salsabor span-btn glyphicon glyphicon-download-alt'>";
				break;

			case '1':
				answerLabel += "success'><span class='glyphicon glyphicon-ok'></span> ("+date+")</span><span class='statut-salsabor span-btn glyphicon glyphicon-remove'>";
				break;

			case '2':
				answerLabel += "danger'><span class='glyphicon glyphicon-fire'></span> En retard</span><span class='statut-salsabor span-btn glyphicon glyphicon-download-alt'>";
				break;
		}
		label.prev().remove();
		answerLabel += "</span>";
		label.replaceWith(answerLabel);
	})
})

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

// Affiche en direct le nombre d'éléments dans le panier
function notifPanier(){
	window.numberProduits = 1;
	window.lowestSpot = 20;
	for(i = 1; i <= 20; i++){
		if(sessionStorage.getItem('produit_id-'+i) != null){
			window.numberProduits++;
		} else {
			if(i <= window.lowestSpot){
				window.lowestSpot = i;
			}
		}
		var actualNumber = window.numberProduits - 1;
		if(actualNumber == 0){
			$("#badge-panier").hide();
		} else {
			$("#badge-panier").show();
			$("#badge-panier").html(actualNumber);
			fillShoppingCart();
		}
	}
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
		showSuccessNotif(data);
		ifAdherentExists();
		$(".has-name-completion").val(identite_prenom+" "+identite_nom);
		$(":regex(id,^unknown-user)").hide('500');
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

// Remplit le popover de l'icône panier dans la navigation
function fillShoppingCart(){
	var panierElement = "";
	var i = 1;
	for(i; i <= 20; i++){
		if(sessionStorage.getItem('produit_id-'+i) != null){
			panierElement += "<tr>";
			panierElement += "<td class='col-lg-10'>"+sessionStorage.getItem('produit-demo-'+i)+"</td>";
			panierElement += "<td class='col-lg-2'><span class='glyphicon glyphicon-trash' onClick=removeCartElement("+i+")></span></td>";
			panierElement += "</tr>";
		}
	}
	$(".table-panier").html(panierElement);
	composeURL();
}

function removeCartElement(key){
	sessionStorage.removeItem('produit_id-'+key);
	sessionStorage.removeItem('produit-demo-'+key);
	sessionStorage.removeItem('produit-'+key);
	sessionStorage.removeItem('beneficiaire-'+key);
	sessionStorage.removeItem('activation-'+key);
	sessionStorage.removeItem('prixIndividuel-'+key);
	notifPanier();
}

// Compose les URL lors de l'achat
function composeURL(){
	var url = "personnalisation.php?element=";
	var i = 1;
	for(i; i <= 20; i++){
		if(sessionStorage.getItem('produit_id-'+i) != null){
			url += sessionStorage.getItem('produit_id-'+i)+"-";
		}
	}
	url = url.slice(0,-1);
	$("[name='next']").attr('href', url);
	$("[name='previous']").attr('href', url);
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

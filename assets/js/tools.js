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
	$('[data-toggle="tooltip"]').tooltip();
	if(top.location.pathname !== "/Salsabor/my/profile" && top.location.pathname !== "/Salsabor/notifications/settings"){
		$.cssHooks.backgroundColor = {
			get: function(elem) {
				if (elem.currentStyle)
					var bg = elem.currentStyle["backgroundColor"];
				else if (window.getComputedStyle)
					var bg = document.defaultView.getComputedStyle(elem, null).getPropertyValue("background-color");
				if (bg.search("rgb") == -1)
					return bg;
				else {
					bg = bg.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
					function hex(x) {
						return ("0" + parseInt(x).toString(16)).slice(-2);
					}
					return "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3]);
				}
			}
		}
	}

	var firstCount = 0; // Pour éviter la notification dès le rafraîchissement de la page.
	window.numberProduits = 1; // Articles dans le panier
	notifCoursParticipants(firstCount);
	notifEcheancesDues(firstCount);
	notifPanier();
	setInterval(notifCoursParticipants, 30000);
	setInterval(notifEcheancesDues, 30000);
	badgeNotifications();
	badgeTasks();
	moment.locale("fra");

	// If we're on one of the user pages, then we have to fetch and refresh details of the user banner.
	var re = /user\//i;
	if(re.exec(top.location.pathname) != null){
		re = /([0-9]+)/;
		var user_id = re.exec(top.location.pathname);
		refreshUserBanner(user_id[0]);
	}

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

	// Filtre dynamique
	var $rows = $('#filter-enabled tr');
	$('#search').keyup(function(){
		var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
		$rows.show().filter(function(){
			var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			return !~text.indexOf(val);
		}).hide();
	});

	// Vérification de l'existence d'un utilisateur dans la base
	$(".has-check").on('blur keyup focus',function(){
		var field = $(this);
		var identite = $(this).val();
		var token = $(this).attr('name').substr(12);
		$.post("functions/check_adherent.php", {identite : identite}).done(function(data){
			console.log(data);
			if(data == 0){
				if($(":regex(id,^unknown-user)").length == 0){
					var addOptions = "<div id='unknown-user"+token+"'>";
					addOptions += "<p>Aucun résultat. Voulez vous inscrire cet adhérent ?</p>";
					addOptions += "<a href='#user-details"+token+"' role='button' class='btn btn-info btn-block' value='create-user' id='create-user"+token+"' data-toggle='collapse' aria-expanded='false' aria-controls='userDetails'>Ouvrir le formulaire de création</a>";
					addOptions += "<div id='user-details"+token+"' class='collapse'>";
					addOptions += "<div class='well'>";
					addOptions += "<div class='row'>";
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Prénom</label><input type='text' name='identite_prenom' id='identite_prenom' class='form-control input-lg' placeholder='Prénom'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Nom</label><input type='text' name='identite_nom' id='identite_nom' class='form-control input-lg' placeholder='Nom'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "</div>"; /*row*/
					addOptions += "<div class='row'>";
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Adresse postale</label><input type='text' name='rue' id='rue' placeholder='Adresse' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>" /*col-lg-6*/
					addOptions += "<div class='col-lg-3'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Code postal</label><input type='number' name='code_postal' id='code_postal' placeholder='Code Postal' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-3*/
					addOptions += "<div class='col-lg-3'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label class='control-label'>Ville</label><input type='text' name='ville' id='ville' placeholder='Ville' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "</div>"; /*row*/
					addOptions += "<div class='row'>";
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label for='text' class='control-label'>Adresse mail</label><input type='email' name='mail' id='mail' placeholder='Adresse mail' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "<div class='col-lg-6'>";
					addOptions += "<div class='form-group'>";
					addOptions += "<label for='telephone' class='control-label'>Numéro de téléphone</label><input type='tel' name='telephone' id='telephone' placeholder='Numéro de téléphone' class='form-control input-lg'>";
					addOptions += "</div>"; /*form-group*/
					addOptions += "</div>"; /*col-lg-6*/
					addOptions += "</div>"; /*row*/
					addOptions += "<a class='btn btn-primary btn-block' onClick='addAdherent()'>Inscrire l'adhérent</a>";
					addOptions += "</div>"; /*well*/
					addOptions += "</div>"; /*collapse*/
					addOptions += "</div>"; /*unknown-user*/
					field.after(addOptions);
				}
			} else {
				$(":regex(id,^unknown-user)").remove();
				$(".has-name-completion:not(.completed)").val(identite);
			}
		})
	})

	$('.separate-scroll').on('DOMMouseScroll mousewheel', function(ev) {
		var $this = $(this),
			scrollTop = this.scrollTop,
			scrollHeight = this.scrollHeight,
			height = $this.height(),
			delta = (ev.type == 'DOMMouseScroll' ?
					 ev.originalEvent.detail * -40 :
					 ev.originalEvent.wheelDelta),
			up = delta > 0;

		var prevent = function() {
			ev.stopPropagation();
			ev.preventDefault();
			ev.returnValue = false;
			return false;
		}

		if (!up && -delta > scrollHeight - height - scrollTop) {
			// Scrolling down, but this will take us past the bottom.
			$this.scrollTop(scrollHeight);
			return prevent();
		} else if (up && delta > scrollTop) {
			// Scrolling up, but this will take us past the top.
			$this.scrollTop(0);
			return prevent();
		}
	});
}).on('click', '.editable', function(e){
	e.stopPropagation();
	// we get the initial value
	var initialValue = $(this).val();
	if(initialValue == ""){initialValue = $(this).html();}
	var class_list = document.getElementById($(this).attr("id")).className.split(/\s+/);
	//classes = classes.replace(/,/g, " ");
	var classes = "";
	for(var i = 0; i < class_list.length; i++){
		classes += class_list[i];
		if(i != class_list.length - 1){
			classes += " ";
		}
	}
	console.log(classes);

	// We get the data details for the upload
	var table = document.getElementById($(this).attr("id")).dataset.table;
	var column = document.getElementById($(this).attr("id")).dataset.column;
	var target = document.getElementById($(this).attr("id")).dataset.target;
	var value = document.getElementById($(this).attr("id")).dataset.value;

	// And the ID.
	var token = $(this).attr('id');

	// If the value's a date, we have to take it a different way
	if(initialValue.indexOf('/') != -1){
		var initialDay = initialValue.substr(0,2);
		var initialMonth = initialValue.substr(3,2);
		var initialYear = initialValue.substr(6,4);
		var initialDate = moment(new Date(initialYear+'-'+initialMonth+'-'+initialDay)).format("YYYY-MM-DD");
		$(this).replaceWith("<input type='date' class='form-control editing' id='"+token+"' value="+initialDate+">");
	} else {
		// Switch depending on the input type
		var input_type = document.getElementById($(this).attr("id")).dataset.input;
		if(column == "task_recipient"){
			var additional_classes = "name-input";
		}
		switch(input_type){
			case "text":
				initialValue = initialValue.replace(/(['"])/g, "\\$1");
				if(value != "no-value"){
					var replacement = "<input type='text' class='form-control editing "+additional_classes+"' id='"+token+"' value='"+initialValue+"'>";
				} else {
					var replacement = "<input type='text' class='form-control editing "+additional_classes+"' id='"+token+"' placeholder='"+initialValue+"'>";
				}
				$(this).replaceWith(replacement);
				break;

			case "textarea":
				$(this).replaceWith("<textarea class='form-control editing "+additional_classes+"' id='"+token+"' data-table='"+table+"' data-column='"+column+"' data-target='"+target+"'>"+initialValue+"</textarea>");
				break;

			default:
				initialValue = initialValue.replace(/(['"])/g, "\\$1");
				if(value != "no-value"){
					var replacement = "<input type='"+input_type+"' class='form-control editing "+additional_classes+"' id='"+token+"' value='"+initialValue+"'>";
				} else {
					var replacement = "<input type='"+input_type+"' class='form-control editing "+additional_classes+"' id='"+token+"' placeholder='"+initialValue+"'>";
				}
				$(this).replaceWith(replacement);
				break;
		}
	}
	$(".editing").focus();
	$(".editing").keypress(function(e){
		if(e.keyCode === 13)
			$(".editing").blur();
	})
	$(".editing").blur(function(e){
		e.stopPropagation();
		var editedValue = $(this).val(), replacementValue = "";
		if(editedValue == ""){
			switch(column){
				case "task_recipient":
					replacementValue = "Affecter un membre";
					break;

				case "task_description":
					replacementValue = "Ajouter une description";
					break;

				case "room_reader":
					replacementValue = "Pas de lecteur couplé";
					break;
			}
			var replacement = "<p class='"+classes.replace(/,/, '')+"' id='"+token+"' data-input='"+input_type+"' data-table='"+table+"' data-column='"+column+"' data-target='"+target+"' data-value='no-value'>"+replacementValue+"</p>";
			if(value == "value"){
				$.when(updateColumn(table, column, editedValue, target)).done(function(data){
					$("#"+token).replaceWith(replacement);
				})
			} else {
				$("#"+token).replaceWith(replacement);
			}
		} else {
			if(column == "task_recipient"){
				// Create notification for the recipient
				postNotification("TAS-A", target, editedValue);
			}
			var replacement = "<p class='"+classes+"' id='"+token+"' data-input='"+input_type+"' data-table='"+table+"' data-column='"+column+"' data-target='"+target+"' data-value='value'>"+editedValue+"</p>";
			$.when(updateColumn(table, column, editedValue, target)).done(function(data){
				$("#"+token).replaceWith(replacement);
			})
		}
		/*if(editedValue != "" && editedValue != initialValue){
			if(editedValue.indexOf('-') != -1){
				var editedDate = moment(new Date(editedValue)).format("DD/MM/YYYY");
				$(this).replaceWith("<span class='editable' id='"+token+"'>"+editedDate+"</span>");
			} else {
				$(this).replaceWith("<span class='editable' id='"+token+"'>"+editedValue+"</span>");
			}
		} else {
			$(this).replaceWith("<span class='editable' id='"+token+"'>"+initialValue+"</span>");
		}*/
	});
}).delegate('*[data-toggle="lightbox"]', 'click', function(event) {
	event.preventDefault();
	return $(this).ekkoLightbox({
		onNavigate: false
	});
}).on('click', '.submit-relay', function(){
	$(".submit-relay-target").click();
}).on('click', '.sub-modal-close', function(){
	$(".sub-modal").toggle();
}).on('click', '.trigger-sub', function(e){
	e.stopPropagation();
	$(".sub-modal").hide(0);
	$(".sub-modal-body").empty();
	var target = document.getElementById($(this).attr("id"));
	var tpos = $(this).position(), type = target.dataset.subtype, toffset = $(this).offset();
	/*console.log(product_id, type);*/

	console.log(document.getElementById($(this).attr("id")));
	var title, body = "", footer = "";
	switch(type){
		case 'AREP':
			var product_id = target.dataset.argument;
			title = "Prolonger";
			body += "<input type='text' class='form-control datepicker'/>";
			footer += "<button class='btn btn-success extend-product' data-argument='"+product_id+"' id='btn-sm-extend'>Prolonger</button>";
			if(moment(target.dataset.arep).isValid()){
				footer += "<button class='btn btn-danger float-right btn-arep' data-argument='"+product_id+"' id='btn-sm-unextend'>Annuler AREP</button>";
				var options = {
					format: "DD/MM/YYYY",
					inline: true,
					locale: "fr",
					defaultDate: moment(target.dataset.arep)
				};
			} else {
				var options = {
					format: "DD/MM/YYYY",
					inline: true,
					locale: "fr"
				};
			}
			$(".sub-modal").css({
				top : tpos.top+136+'px',
				left: toffset.left+'px'});
			$(".sub-modal-body").html(body);
			break;

		case 'activate':
			var product_id = target.dataset.argument;
			title = "Activer";
			body += "<input type='text' class='form-control datepicker'/>";
			footer += "<button class='btn btn-success activate-product' data-argument='"+product_id+"' id='btn-sm-activate'>Activer</button>";
			$(".sub-modal").css({top : tpos.top+51+'px'});
			$(".sub-modal-body").html(body);
			var options = {
				format: "DD/MM/YYYY",
				inline: true,
				locale: "fr"
			};
			break;

		case 'deadline':
			var task_id = target.dataset.task;
			title = "Date limite";
			body += "<input type='text' class='form-control datepicker'/>";
			footer += "<button class='btn btn-success task-deadline' data-task='"+task_id+"' id='btn-set-deadline'>Définir</button>";
			$(".sub-modal").css({top : toffset.top+25+'px', left : toffset.left+15+'px'});
			$(".sub-modal-body").html(body);
			var options = {
				format: "DD/MM/YYYY HH:mm",
				inline: true,
				locale: "fr",
				stepping: 15
			};
			break;

		case 'set-participation-product':
			title = "Changer le produit à utiliser";
			var participation_id = target.dataset.participation;
			console.log(participation_id);
			$.when(fetchEligibleProducts(participation_id)).done(function(data){
				var construct = displayEligibleProducts(data);
				$(".sub-modal-body").html(construct);
			})
			footer += "<button class='btn btn-success set-participation-product' id='btn-set-participation-product' data-participation='"+participation_id+"'>Reporter</button>";
			footer += " <button class='btn btn-default btn-modal set-participation-product' id='btn-product-null-record' data-participation='"+participation_id+"'><span class='glyphicon glyphicon-link'></span> Retirer</button>";
			$(".sub-modal").css({top : toffset.top+'px'});
			if(toffset.left > 1000){
				$(".sub-modal").css({left : toffset.left-350+'px'});
			} else {
				$(".sub-modal").css({left : toffset.left+20+'px'});
			}
			break;

		case 'change-participation':
			title = "Changer le cours associé";
			var participation_id = target.dataset.argument;
			$.when(fetchEligibleSessions(participation_id)).done(function(data){
				console.log(data);
				var construct = displayTargetSessions(data);
				$(".sub-modal-body").html(construct);
			})
			footer += "<button class='btn btn-success report-participation' id='btn-session-changer-record' data-participation='"+participation_id+"'>Changer</button>";
			$(".sub-modal").css({top : toffset.top+'px'});
			if(toffset.left > 1000){
				$(".sub-modal").css({left : toffset.left-350+'px'});
			} else {
				$(".sub-modal").css({left : toffset.left+20+'px'});
			}
			break;

		case 'delete':
			title = "Supprimer une participation";
			var participation_id = target.dataset.argument;
			body += "Êtes-vous sûr de vouloir supprimer cette participation ?";
			$(".sub-modal-body").html(body);
			footer += "<button class='btn btn-danger delete-participation col-lg-6' id='btn-product-delete' data-session='"+participation_id+"'><span class='glyphicon glyphicon-trash'></span> Supprimer</button><button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : tpos.top-45+'px'});
			break;

		case 'delete-record':
			title = "Supprimer un passage";
			var participation_id = target.dataset.argument;
			body += "Êtes-vous sûr de vouloir supprimer ce passage ?";
			$(".sub-modal-body").html(body);
			footer += "<button class='btn btn-danger delete-record col-lg-6' id='btn-record-delete' data-participation='"+participation_id+"'><span class='glyphicon glyphicon-trash'></span> Supprimer</button><button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : toffset.top+'px'});
			if(toffset.left > 1000){
				$(".sub-modal").css({left : toffset.left-350+'px'});
			} else {
				$(".sub-modal").css({left : toffset.left+20+'px'});
			}
			break;

		case 'delete-product':
			title = "Supprimer un produit";
			var product_id = target.dataset.product;
			body += "ATTENTION : Si ce produit est seul dans une transaction, la transaction sera supprimée avec ce produit. Une fois validée, cette opération destructrice est irréversible. Êtes-vous sûr de vouloir supprimer ce produit ?";
			footer += "<button class='btn btn-danger delete-product col-lg-6' id='btn-product-delete' data-product='"+product_id+"' data-dismiss='modal'><span class='glyphicon glyphicon-trash'></span> Supprimer</button><button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : tpos.top+51+'px'});
			$(".sub-modal-body").html(body);
			break;

		case 'delete-task':
			title = "Supprimer une tâche";
			var task_id = target.dataset.target;
			body += "ATTENTION : Cette opération est irréversible. Êtes-vous sûr(e) de vouloir continuer ?";
			footer += "<button class='btn btn-danger delete-task col-lg-6' id='btn-task-delete' data-task='"+task_id+"' data-dismiss='modal'><span class='glyphicon glyphicon-trash'></span> Supprimer</button><button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : toffset.top+20+'px', left : toffset.left-321+'px'});
			$(".sub-modal-body").html(body);
			break;

		case 'add-record':
			title = "Ajouter un passage manuellement";
			var session_id = target.dataset.session;
			body += "<input type='text' class='form-control name-input'>";
			$(".sub-modal-body").html(body);
			footer += "<button class='btn btn-success add-record col-lg-6' id='btn-add-record' data-session='"+session_id+"'><span class='glyphicon glyphicon-plus'></span> Ajouter </button><button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : toffset.top+'px'});
			if(toffset.left > 1000){
				$(".sub-modal").css({left : toffset.left-350+'px'});
			} else {
				$(".sub-modal").css({left : toffset.left+20+'px'});
			}
			break;

		case 'unlink':
			title = "Délier une participation";
			var participation_id = target.dataset.argument;
			body += "Êtes vous sûr de vouloir délier cette participation ? Vous la retrouverez dans les passages non régularisés";
			$(".sub-modal-body").html(body);
			footer += "<button class='btn btn-default unlink-session col-lg-6' id='btn-product-unlink' data-session='"+participation_id+"'><span class='glyphicon glyphicon-link'></span> Délier</button> <button class='btn btn-default col-lg-6'>Annuler</button>";
			$(".sub-modal").css({top : tpos.top-45+'px'});
			break;

		case 'receive-maturity':
			var maturity_id = target.dataset.maturity;
			var method = $("#maturity-"+maturity_id+"-method>span").first().text();
			title = "Réception de l'échéance";
			body += "<input type='text' class='form-control datepicker reception-date'/>";
			body += "<label class='control-label'>Méthode de paiement</label>";
			body += "<input type='text' class='form-control reception-method' value='"+method+"'></input>";
			footer += "<button class='btn btn-success receive-maturity' data-maturity='"+maturity_id+"' id='btn-sm-receive'>Recevoir</button>";
			$(".sub-modal").css({top : toffset.top+'px'});
			$(".sub-modal").css({left : toffset.left-200+'px'});
			$(".sub-modal-body").html(body);
			var options = {
				format: "DD/MM/YYYY",
				inline: true,
				locale: "fr"
			};
			break;

		case 'bank-maturity':
			var maturity_id = target.dataset.maturity;
			title = "Encaissement de l'échéance";
			body += "<input type='text' class='form-control datepicker'/>";
			footer += "<button class='btn btn-success bank-maturity' data-maturity='"+maturity_id+"' id='btn-sm-receive'>Recevoir</button>";
			$(".sub-modal").css({top : tpos.top+51+'px'});
			$(".sub-modal-body").html(body);
			var options = {
				format: "DD/MM/YYYY",
				locale: "fr"
			};
			break;

		case 'user-tags':
		case 'session-tags':
			var target_type = document.getElementById($(this).attr("id")).dataset.targettype;
			var tag_type = /^([a-z]+)/i.exec(type);
			window.target = $(this).attr("id");
			title = "Ajouter une étiquette";
			$(".sub-modal").removeClass("col-lg-7");
			$(".sub-modal").addClass("col-lg-3");
			if(top.location.pathname === "/Salsabor/dashboard"){
				$(".sub-modal").css({top : toffset.top+25+'px', left: toffset.left-25+'px'});
			} else {
				$(".sub-modal").css({top : toffset.top+25+'px', left: toffset.left+25+'px'});
			}
			$.when(fetchTags(tag_type[0])).done(function(data){
				var construct = displayTargetTags(data, target_type, tag_type[0]);
				$(".sub-modal-body").html(construct);
			})
			break;

		case 'edit-tag':
			var target = document.getElementById($(this).attr("id")).dataset.target;
			var tag_type = document.getElementById($(this).attr("id")).dataset.tagtype;
			var initialValue = $("#tag-"+target).text();
			title = "Modifier une étiquette";
			$(".sub-modal").removeClass("col-lg-7");
			$(".sub-modal").addClass("col-lg-3");
			$(".sub-modal").css({top : toffset.top+'px', left: toffset.left+45+'px'});
			body += "<div class='input-group'>";
			body += "<input type='text' class='form-control' id='edit-tag-name' data-target='"+target+"' data-tagtype='"+tag_type+"' placeholder='Nom de l&apos;étiquette' value='"+initialValue+"'>";
			body += "<span class='input-group-btn'><button class='btn btn-success btn-tag-name' type='button'>Valider</button></span>";
			body += "</div>";
			$.when(fetchColors()).done(function(data){
				body += "<div class='container-fluid' id='colors'>";
				var colors = JSON.parse(data);
				for(var i = 0; i < colors.length; i++){
					body += "<div class='color-cube col-xs-3 col-md-3 col-lg-2' id='color-"+colors[i].color_id+"' style='background-color:"+colors[i].color_value+"' data-target='"+target+"'  data-tagtype='"+tag_type+"'>";
					if("#"+colors[i].color_value == $("#tag-"+target).css("backgroundColor")){
						body += "<span class='glyphicon glyphicon-ok color-selected'></span>";
					}
					body += "</div>";
				}
				body += "</div>";
				if(tag_type == "session"){
					var is_mandatory;
					if($("#tag-"+target).find(".glyphicon-star").length > 0){
						is_mandatory = 1;
					} else {
						is_mandatory = 0;
					}
					body += "<input name='is_mandatory' class='mandatory-tag-check' id='is_mandatory-"+target+"' data-target='"+target+"' value='"+is_mandatory+"'> Obligatoire <span class='glyphicon glyphicon-question-sign' id='mandatory-tooltip' data-toggle='tooltip' title='Une étiquette obligatoire devra impérativement figurer sur le produit pour qu&apos;il soit compatible'></span>";
				}
				$(".sub-modal-body").html(body);
				$("#is_mandatory-"+target).checkboxX({
					threeState: false,
					size:'lg'
				});
				$("#mandatory-tooltip").tooltip();
			});
			footer += "<button class='btn btn-danger btn-block delete-tag' id='delete-tag' data-target='"+target+"' data-tagtype='"+tag_type+"'><span class='glyphicon glyphicon-trash'></span> Supprimer l'étiquette</button>";
			break;

		case 'room-color':
			var target = document.getElementById($(this).attr("id")).dataset.target;
			title = "Modifier la colueur de la salle";
			$(".sub-modal").removeClass("col-lg-7");
			$(".sub-modal").addClass("col-lg-3");
			$(".sub-modal").css({top : toffset.top+'px', left: toffset.left+45+'px'});
			$.when(fetchColors()).done(function(data){
				body += "<div class='row' id='colors'>";
				var colors = JSON.parse(data);
				for(var i = 0; i < colors.length; i++){
					body += "<div class='color-cube col-xs-4 col-md-3 col-lg-2' id='color-"+colors[i].color_id+"' style='background-color:"+colors[i].color_value+"' data-target='"+target+"' data-color='"+colors[i].color_id+"'>";
					if("#"+colors[i].color_value == $("#room-color-cube-"+target).css("backgroundColor")){
						body += "<span class='glyphicon glyphicon-ok color-selected'></span>";
					}
					body += "</div>";
				}
				body += "</div>";
				$(".sub-modal-body").html(body);
			});
			break;

		default:
			title = "Sub modal";
			break;
	}
	$(".sub-modal-title").text(title);
	$(".sub-modal-footer").html(footer);
	$(".datepicker").datetimepicker(options);
	var re = /historique/i;
	if(re.exec(top.location.pathname) != null){
		console.log("Historique");
		$(".sub-modal").css({left: 74+'%'});
	}
	$(".sub-modal").show(0);
}).on('change', '.mandatory-tag-check', function(){
	var target = document.getElementById($(this).attr("id")).dataset.target;
	var value = $(this).val();
	console.log("checkbox of tag "+target+" changed to "+value);
	$.when(updateColumn("tags_session", "is_mandatory", value, target)).done(function(){
		if(value == 1)
			$("#tag-"+target).prepend("<span class='glyphicon glyphicon-star'></span> ");
		else
			$("#tag-"+target).remove($(".glyphicon-star"));
	})
}).on('click', '.sub-menu-toggle', function(){
	console.log("toggling");
	$(".small-sidebar-container").toggle();
})

$(".has-name-completion").on('click blur keyup', function(){
	if($(this).val() != ""){
		$(this).addClass("completed");
	} else {
		$(this).removeClass("completed");
	}
})

// Surveille les participations à un cours non associés à un produit (abonnement, vente spontanée, invitation...)
function notifCoursParticipants(firstCount){
	$.post("functions/watch_participations.php").done(function(data){
		if(data == 0){
			$("#badge-participants").hide();
		} else {
			if(data > $("#badge-participations").html() && firstCount != 0){
				$.notify("Nouvelles participations non associées.", {globalPosition: "bottom right", className:"info"});
			}
			$("#badge-participations").show();
			$("#badge-participations").html(data);
			$(".irregular-participations-title>span").text(data);
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

function badgeTasks(){
	$.post("functions/watch_tasks.php").done(function(data){
		if(data == 0){
			$("#badge-tasks").hide();
		} else {
			$("#badge-tasks").show();
			$("#badge-tasks").html(data);
		}
		setTimeout(badgeTasks, 10000);
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
	$(this).parent().prev().blur();
});

// Vérifie si un adhérent a des échéances impayées lors de la vente d'un forfait
function checkMaturities(data){
	$.post('functions/check_unpaid.php', {search_id : data}).done(function(maturities){
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
	$.post("functions/add_adherent.php", {identite_prenom : identite_prenom, identite_nom : identite_nom, rfid : rfid, rue : rue, code_postal : code_postal, ville : ville, mail : mail, telephone : telephone, date_naissance : date_naissance}).done(function(data){
		console.log(data);
		var parse = JSON.parse(data);
		$(".has-name-completion:not(.completed)").val(identite_prenom+" "+identite_nom);
		if(window.miniCart != ""){
			window.miniCart["id_beneficiaire"] = parse["id"];
			window.miniCart["nom_beneficiaire"] = identite_prenom+" "+identite_nom;
		}
		showSuccessNotif(parse["success"]);
		$(":regex(id,^unknown-user)").hide('500');
	});
}

// Checks if holiday exists when attempting to create events on calendar
function checkHoliday(){
	var date_debut = $('#date_debut').val();
	$.post("functions/check_holiday.php", {date_debut : date_debut}).done(function(data){
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

function notifPanier(){
	if(sessionStorage.getItem("panier") != null){
		var cartSize = JSON.parse(sessionStorage.getItem("panier"));
		if(cartSize.length == 0){
			$("#badge-panier").hide();
			$(".table-panier").empty();
		} else {
			$("#badge-panier").show();
			$("#badge-panier").html(cartSize.length);
			fillShoppingCart();
		}
	}
}

function fillShoppingCart(){
	$(".table-panier").empty();
	if(sessionStorage.getItem("panier") != null){
		var cart = JSON.parse(sessionStorage.getItem("panier"));
		var cartSize = JSON.parse(sessionStorage.getItem("panier-noms"));
		var line = "";
		if(cart.length != 0){
			for(var i = 0; i < cartSize.length; i++){
				line += "<tr>"
				line += "<td class='col-lg-11'>"+cartSize[i]+"</td>";
				line += "<td class='col-lg-1'><span class='glyphicon glyphicon-trash glyphicon-button glyphicon-button-alt' onclick='removeCartElement("+i+")'></span></td>";
				line += "<tr>";
			}
			$(".table-panier").append(line);
			composeURL(cart[0]);
		}
	}
}

function removeCartElement(key){
	var cart = JSON.parse(sessionStorage.getItem("panier"));
	var cartNames = JSON.parse(sessionStorage.getItem("panier-noms"));
	cart.splice(key, 1);
	cartNames.splice(key, 1);
	sessionStorage.setItem("panier", JSON.stringify(cart));
	sessionStorage.setItem("panier-noms", JSON.stringify(cartNames));
	notifPanier();
}

// Prepares the URL when purchasing items
function composeURL(token){
	var url = "personnalisation.php?element=";
	url += token;
	url += "&order=0";
	$("[name='next']").attr('href', url);
	$("[name='previous']").attr('href', url);
}

// Adds a row
function addEntry(table, values){
	return $.post("functions/add_entry.php", {table : table, values : values});
}

// Updates a single column in a row of a table
function updateColumn(table, column, value, target){
	return $.post("functions/update_column.php", {table : table, column : column, value : value, target_id : target});
}

// Updates a whole row
function updateEntry(table, values, target){
	return $.post("functions/update_entry.php", {table : table, target_id : target, values : values});
}

function refreshUserBanner(user_id){
	$.get("functions/fetch_user_banner_details.php", {user_id : user_id}).done(function(data){
		var user_details = JSON.parse(data);
		$("#user_prenom:not(.editing)").text(user_details.user_prenom);
		$("#user_nom:not(.editing)").text(user_details.user_nom);
		$("#refresh-mail:not(.editing)").text(user_details.mail);
		$("#refresh-rfid:not(.editing)").html("<span class='glyphicon glyphicon-barcode'></span> "+user_details.user_rfid);
		//$("#refresh-tasks").append(user_details.tasks);
		$("#refresh-phone:not(.editing)").html(user_details.telephone);
		$("#refresh-address:not(.editing)").html("<span class='glyphicon glyphicon-home'></span> "+user_details.address);
	})
	setTimeout(refreshUserBanner, 10000, user_id);
}

// Deletes an entry in a table of the database
function deleteEntry(table, entry_id){
	return $.post("functions/delete_entry.php", {table : table, entry_id : entry_id});
}

// Deletes tasks by the target, not the ID (used to eliminate orphan tasks)
function deleteTasksByTarget(token, target_id){
	return $.post("functions/delete_tasks_by_target.php", {token : token, target_id : target_id});
}

function postNotification(token, target, recipient){
	return $.post("functions/post_notifications.php", {token : token, target : target, recipient : recipient});
}

function fetchColors(){
	return $.get("functions/fetch_colors.php");
}

// http://stackoverflow.com/questions/19491336/get-url-parameter-jquery-or-how-to-get-query-string-values-in-js
function getUrlParameter(sParam) {
	var sPageURL = decodeURIComponent(window.location.search.substring(1)),
		sURLVariables = sPageURL.split('&'),
		sParameterName,
		i;

	for (i = 0; i < sURLVariables.length; i++) {
		sParameterName = sURLVariables[i].split('=');

		if (sParameterName[0] === sParam) {
			return sParameterName[1] === undefined ? true : sParameterName[1];
		}
	}
};

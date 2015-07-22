// Insert la date d'aujourd'hui dans un input de type date supportant la fonctionnalité 
$("*[date-today='true']").click(function(){
    var today = new moment().format("YYYY-MM-DD");
    $(this).parent().prev().val(today);
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
	var rue = $('#rue').val();
	var code_postal = $('#code_postal').val();
	var ville = $('#ville').val();
	var mail = $('#mail').val();
	var telephone = $('#telephone').val();
	var date_naissance = $('#date_naissance').val();
	$.post("functions/add_adherent.php", {identite_prenom, identite_nom, rue, code_postal, ville, mail, telephone, date_naissance}).done(function(data){
		$('#create-user').click();
		$('#user-added').show('500').delay(3000).hide('3000');
		ifAdherentExists();
	}).fail(function(data){
		$('#user-error').show('500').delay(3000).hide('3000');
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
  if($("[mandatory='true']").val() != '' || $("[mandatory='true']").html() != ''){
      $("#submit-button").prop('disabled', false);
   } else {
       $(this).next().children('p').html("Ce champ est requis");
       $("#submit-button").prop('disabled', true);
   }
}
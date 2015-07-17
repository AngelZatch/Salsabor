// Insert la date d'aujourd'hui dans un input de type date supportant la fonctionnalité 
$("*[date-today='true']").click(function(){
    var today = new moment().format("YYYY-MM-DD");
    $(this).parent().prev().val(today);
});

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
$(".statut-salsabor").click(function(){
	var echeance_id = $(this).parents("td").children("input[name^='echeance']").val();
	var container = $(this).parents("td");
	$.post("functions/validate_echeance.php", {echeance_id}).done(function(data){
		showSuccessNotif(data);
		container.empty();
		container.html("<span class='label label-success'>Réceptionnée</span>");
	})
})

$(".statut-banque").click(function(){
	var echeance_id = $(this).parents("td").children("input[name^='echeance']").val();
	var container = $(this).parents("td");
	$.post("functions/encaisser_echeance.php", {echeance_id}).done(function(data){
		showSuccessNotif(data);
		container.empty();
		container.html("<span class='label label-success'>Encaissée</span>");
	})
})

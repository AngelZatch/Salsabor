$(document).on('click', '.statut-salsabor', function(){
	var echeance_id = $(this).parent("td").children("input[name='echeance-id']").val();
	var label = $(this);
	$.post("functions/validate_echeance.php", {echeance_id : echeance_id}).done(function(data){
		var answerLabel = "<span class='label label-";
		var date = moment().format('DD/MM/YYYY');
		switch(data){
			case '0':
				answerLabel += "info'><span class='glyphicon glyphicon-option-horizontal'></span> En attente</span><span class='statut-salsabor span-btn glyphicon glyphicon-download-alt'>";
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
}).on('click', '.statut-banque', function(){
	var echeance_id = $(this).parent("td").children("input[name='echeance-id']").val();
	var label = $(this);
	$.post("functions/encaisser_echeance.php", {echeance_id : echeance_id}).done(function(data){
		var answerLabel = "<span class='label label-";
		var date = moment().format('DD/MM/YYYY');
		switch(data){
			case '0':
				answerLabel += "info'><span class='glyphicon glyphicon-option-horizontal'></span> Dépôt à venir</span><span class='statut-banque span-btn glyphicon glyphicon-download-alt'>";
				break;

			case '1':
				answerLabel += "success'><span class='glyphicon glyphicon-ok'></span> ("+date+")</span><span class='statut-banque span-btn glyphicon glyphicon-remove'>";
				break;
		}
		label.prev().remove();
		answerLabel += "</span>";
		label.replaceWith(answerLabel);
	})
})

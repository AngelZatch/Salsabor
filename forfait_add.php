<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

if(isset($_POST["add"])){
	$volume_horaire = 0;
    if($_POST["volume_horaire"] != 0){
        $tarif_horaire = $_POST["tarif_global"]/$_POST["volume_horaire"];
		$volume_horaire = $_POST["volume_horaire"];
    } else {
        $tarif_horaire = 0;
    }
    $validite = 7 * $_POST["validite"];
    $actif = 1;
    if(isset($_POST["arep"])){
        $arep = $_POST["arep"];
    } else {
        $arep = 0;
    }
    
    try{
        $db->beginTransaction();
        $new = $db->prepare("INSERT INTO produits(produit_nom, description, volume_horaire, validite_initiale, tarif_horaire, tarif_global, date_activation ,date_desactivation, actif, echeances_paiement, autorisation_report)
        VALUES(:intitule, :description, :volume_horaire, :validite, :tarif_horaire, :tarif_global, :date_activation, :date_limite_achat, :actif, :echeances, :autorisation_report)");
        $new->bindParam(':intitule', $_POST["intitule"]);
        $new->bindParam(':description', $_POST["description"]);
        $new->bindParam(':volume_horaire', $_POST["volume_horaire"]);
        $new->bindParam(':validite', $validite);
        $new->bindParam(':tarif_horaire', $tarif_horaire);
        $new->bindParam(':tarif_global', $_POST["tarif_global"]);
        $new->bindParam(':date_activation', $_POST["date_activation"]);
        $new->bindParam(':date_limite_achat', $_POST["date_limite_achat"]);
        $new->bindParam(':actif', $actif);
        $new->bindParam(':echeances', $_POST["echeances"]);
        $new->bindParam(':autorisation_report', $arep);
        $new->execute();
        $db->commit();
        header("Location: forfaits.php");
    }catch (PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ajouter un forfait | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-plus"></span> Ajouter un forfait</h1>
                <div class="col-sm-9" id="solo-form">
                    <form action="forfait_add.php" method="post">
                      <div class="btn-toolbar">
                          <a href="forfaits.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour aux forfaits</a>
                          <input type="submit" name="add" role="button" class="btn btn-primary" value="ENREGISTRER">
                      </div><!-- btn-toolbar -->
                      <div class="form-group">
                         <label for="intitule">Intitulé</label>
                          <input type="text" class="form-control" name="intitule" placeholder="Nom du produit">
                      </div>
                      <div class="form-group">
                          <label for="description">Description</label>
                          <textarea rows="5" class="form-control" name="description" placeholder="Facultatif. Tentez d'être succinct !"></textarea>
                      </div>
                      <div class="form-group">
                      	<label for="offre_illimitee">Offre Illimitée ?</label>
                      	<input name="offre_illimitee" id="offre_illimitee" data-toggle="checkbox-x" data-size="lg" data-three-state="false" value="0">
                      </div>
                      <div class="form-group" id="volume_horaire">
                          <label for="volume_horaire">Volume de cours (en heures)</label>
                          <input type="text" class="form-control" name="volume_horaire" placeholder="Exemple : 10">
                      </div>
                      <div class="form-group">
                          <label for="validite">Durée de validité (à partir de l'achat, en semaines)</label>
                          <input type="text" class="form-control" name="validite" placeholder="Exemple : 48">
                      </div>
                      <div class="form-group">
                          <label for="tarif_global">Prix d'achat</label>
                          <input type="text" class="form-control" name="tarif_global">
                      </div>
                      <div class="form-group">
                          <label for="date_activation">Date de mise à disposition à l'achat</label>
                          <div class="input-group">
                              <input type="date" class="form-control" name="date_activation">
                              <span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" date-today="true">Insérer aujourd'hui</a></span>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="date_limite_achat">Date limite d'achat possible</label>
                          <div class="input-group">
                              <input type="date" class="form-control" name="date_limite_achat">
                              <span role="buttton" class="input-group-btn"><a class="btn btn-info" role="button" date-today="true">Insérer aujourd'hui</a></span>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="echeances">Nombre d'échéances de paiement autorisées</label>
                          <input type="text" class="form-control" name="echeances">
                      </div>
                      <div class="form-group">
                          <label for="arep">Autoriser l'extension de validité ? (AREP)</label>
                          <input type="checkbox" value="1" name="arep">
                      </div>
                  </form>
              </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>
   <script>
	  $("#offre_illimitee").change(function(){
		  if($(this).val() == '1'){
			  $("#volume_horaire").hide('600');
		  } else {
			  $("#volume_horaire").show('600');
		  }
	  });
	</script>
</body>
</html>
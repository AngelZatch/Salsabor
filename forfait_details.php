<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$data = $_GET["id"];

// Détails du forfait
$queryProduit = $db->prepare("SELECT * FROM produits WHERE produit_id=?");
$queryProduit->bindParam(1, $data);
$queryProduit->execute();
$produit = $queryProduit->fetch(PDO::FETCH_ASSOC);

if(isset($_POST["edit"])){
    if(isset($_POST["volume_horaire"])){
        $tarif_horaire = $_POST["tarif_global"]/$_POST["volume_horaire"];
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
        $edit = $db->prepare("UPDATE produits SET produit_nom = :intitule,
                                                description = :description,
                                                volume_horaire = :volume_horaire,
                                                validite_initiale = :validite,
                                                tarif_horaire = :tarif_horaire,
                                                tarif_global = :tarif_global,
                                                date_activation = :date_activation,
                                                date_desactivation = :date_limite_achat,
                                                actif = :actif,
                                                echeances_paiement = :echeances,
                                                autorisation_report = :autorisation_report
                                                WHERE produit_id = :id");
        $edit->bindParam(':intitule', $_POST["intitule"]);
        $edit->bindParam(':description', $_POST["description"]);
        $edit->bindParam(':volume_horaire', $_POST["volume_horaire"]);
        $edit->bindParam(':validite', $validite);
        $edit->bindParam(':tarif_horaire', $tarif_horaire);
        $edit->bindParam(':tarif_global', $_POST["tarif_global"]);
        $edit->bindParam(':date_activation', $_POST["date_activation"]);
        $edit->bindParam(':date_limite_achat', $_POST["date_limite_achat"]);
        $edit->bindParam(':actif', $actif);
        $edit->bindParam(':echeances', $_POST["echeances"]);
        $edit->bindParam(':autorisation_report', $arep);
        $edit->bindParam(':id', $data);
        $edit->execute();
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
    <title>Détails du forfait <?php echo $produit["produit_nom"];?> | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
               <h1 class="page-title"><span class="glyphicon glyphicon-credit-card"></span> Forfait <?php echo $produit["produit_nom"];?></h1>
                <div class="col-sm-9" id="solo-form">
                    <form action="forfait_details.php?id=<?php echo $data;?>" method="post">
                      <div class="btn-toolbar">
                          <a href="forfaits.php" role="button" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Retour aux forfaits</a>
                          <input type="submit" name="edit" role="button" class="btn btn-primary" value="ENREGISTRER">
                      </div><!-- btn-toolbar -->
                      <div class="form-group">
                         <label for="intitule">Intitulé</label>
                          <input type="text" class="form-control" name="intitule" value="<?php echo $produit["produit_nom"];?>" placeholder="Nom du produit">
                      </div>
                      <div class="form-group">
                          <label for="description">Description</label>
                          <textarea rows="5" class="form-control" name="description" value="<?php echo $produit["description"];?>" placeholder="Facultatif. Tentez d'être succinct !"></textarea>
                      </div>
                      <div class="form-group">
                          <label for="volume_horaire">Volume de cours (en heures)</label>
                          <input type="text" class="form-control" name="volume_horaire" value="<?php echo $produit["volume_horaire"];?>" placeholder="Exemple : 10">
                      </div>
                      <div class="form-group">
                          <label for="validite">Durée de validité (à partir de l'achat, en semaines)</label>
                          <input type="text" class="form-control" name="validite" value="<?php echo $produit["validite_initiale"] / 7;?>" placeholder="Exemple : 48">
                      </div>
                      <div class="form-group">
                          <label for="tarif_global">Prix d'achat</label>
                          <input type="text" class="form-control" name="tarif_global" value="<?php echo $produit["tarif_global"];?>">
                      </div>
                      <div class="form-group">
                          <label for="date_activation">Date de mise à disposition à l'achat (laissez vide pour une activation dès la validation)</label>
                          <input type="date" class="form-control" name="date_activation" value="<?php echo $produit["date_activation"];?>">
                      </div>
                      <div class="form-group">
                          <label for="date_limite_achat">Date limite d'achat possible (laissez vide pour une activation pendant une durée indéfinie)</label>
                          <input type="date" class="form-control" name="date_limite_achat" value="<?php echo $produit["date_desactivation"];?>">
                      </div>
                      <div class="form-group">
                          <label for="echeances">Nombre d'échéances de paiement autorisées</label>
                          <input type="text" class="form-control" name="echeances" value="<?php echo $produit["echeances_paiement"];?>">
                      </div>
                      <div class="form-group">
                          <label for="arep">Autoriser l'extension de validité ?</label>
                          <input type="checkbox" value="1" name="arep">
                      </div>
                  </form>
              </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>    
</body>
</html>
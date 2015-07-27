<?php
require_once "db_connect.php";
include "librairies/fpdf/fpdf.php";
include "librairies/fpdi/fpdi.php";
require_once "tools.php";

function vente(){
    $db = PDOFactory::getConnection();
    $prenom = $_POST["identite_prenom"];
    $nom = $_POST["identite_nom"];
    $adherent = getAdherent($prenom, $nom);
	
	$transaction = generateReference();
    
    $date_achat = date_create("now")->format('Y-m-d H:i:s');
    
    $actif = ($_POST["date_activation"]>$date_achat)?0:1;
    
    $queryHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee >= ? AND date_chomee <= ?");
    $queryHoliday->bindParam(1, $_POST["date_activation"]);
    $queryHoliday->bindParam(2, $_POST["date_expiration"]);
    $queryHoliday->execute();
    
    $j = 0;
    
    for($i = 1; $i <= $queryHoliday->rowCount(); $i++){
        $exp_date = date("Y-m-d 00:00:00",strtotime($_POST["date_expiration"].'+'.$i.'DAYS'));
        $checkHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee=?");
        $checkHoliday->bindParam(1, $exp_date);
        $checkHoliday->execute();
        if($checkHoliday->rowCount() != 0){
            $j++;
        }
        $totalOffset = $i + $j;
        $new_exp_date = date("Y-m-d 00:00:00",strtotime($_POST["date_expiration"].'+'.$totalOffset.'DAYS'));
    }
	
	$echeances = $_POST["echeances"];
	$montant_echeance = 0;
    
    try{
        $db->beginTransaction();
        $new = $db->prepare("INSERT INTO produits_adherents(id_transaction, id_adherent, id_produit, date_achat, date_activation, date_expiration, volume_cours, prix_achat, actif, arep)
        VALUES(:transaction, :adherent, :produit_id, :date_achat, :date_activation, :date_expiration, :volume_horaire, :prix_achat, :actif, :arep)");
		$new->bindParam(':transaction', $transaction);
        $new->bindParam(':adherent', $adherent["eleve_id"]);
        $new->bindParam(':produit_id', $_POST["produit"]);
        $new->bindParam(':date_achat', $date_achat);
        $new->bindParam(':date_activation', $_POST["date_activation"]);
        $new->bindParam(':date_expiration', $new_exp_date);
        $new->bindParam(':volume_horaire', $_POST["volume_horaire"]);
        $new->bindParam(':prix_achat', $_POST["prix_achat"]);
        $new->bindParam(':actif', $actif);
        $new->bindParam(':arep', $_POST["autorisation_report"]);
        $new->execute();
		for($k = 0; $k < $echeances; $k++){
			$new_echeance = $db->prepare("INSERT INTO produits_echeances(id_produit_adherent, date_echeance, montant)
			VALUES(:transaction, :date_echeance, :prix)");
			$new_echeance->bindParam(':transaction', $transaction);
			$date_echeance = date("Y-m-d", strtotime($date_achat.'+'.(30*$k).'DAYS'));
			$new_echeance->bindParam(':date_echeance', $date_echeance);
			$prix_restant = $_POST["prix_achat"] - $montant_echeance;
			$montant_echeance = $_POST["prix_achat"]/$echeances;
			if($prix_restant <= $montant_echeance){
				$montant = $prix_restant;
			}
			$new_echeance->bindParam(':prix', $montant_echeance);
			$new_echeance->execute();
		}
        $db->commit();
		header('Location: dashboard.php');
    }catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}

?>
<?php
require_once "db_connect.php";
include "librairies/fpdf/fpdf.php";
include "librairies/fpdi/fpdi.php";
require_once "tools.php";

function vente(){
    $db = PDOFactory::getConnection();
	$data = explode(' ', $_POST["identite_nom"]);
	$prenom = $data[0];
	$nom = $data[1];
    $adherent = getAdherent($prenom, $nom);
	
	$transaction = generateReference();
    
    $date_achat = date_create("now")->format('Y-m-d H:i:s');
    
	if($_POST["date_activation"] != ""){
		$actif = 1;
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
	} else {
		$actif = 0;
	}
	$echeances = $_POST["echeances"];
	$prix_restant = $_POST["prix_achat"];
    
	$queryProduit = $db->prepare("SELECT * FROM produits WHERE produit_id=?");
	$queryProduit->bindParam(1, $_POST["produit"]);
	$queryProduit->execute();
	$produit = $queryProduit->fetch(PDO::FETCH_ASSOC);
	
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
		
		/**** PDF ****/
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->SetSourceFile("librairies/Salsabor-vente-facture.pdf");
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, 0, 0, 210);
		// Référence
		$pdf->SetFont('Arial', 'B', 18);
		$pdf->setXY(120, 38.5);
		$pdf->Write(0, $transaction);
		// Phrase de début
		$pdf->SetFont('Arial', '', 11);
		$pdf->setXY(21, 49);
		$infos = $prenom." ".$nom;
		$infos = iconv('UTF-8', 'windows-1252', $infos);
		$pdf->Write(0, $infos);
		//Informations
		$pdf->setXY(10, 74);
		$infos = $adherent["eleve_prenom"]." ".$adherent["eleve_nom"]."\n".$adherent["rue"]." - ".$adherent["code_postal"]." ".$adherent["ville"]."\n".$adherent["mail"]."\nTél : ".$adherent["telephone"];
		$infos = iconv('UTF-8', 'windows-1252', $infos);
		$pdf->MultiCell(0, 7, $infos);
		// Vente
		$pdf->setXY(10, 117);
		$infos = "Forfait ".$produit["produit_nom"]."\nValide du ".date_create($_POST["date_activation"])->format("d/m/Y")." au ".date_create($new_exp_date)->format("d/m/Y");
		$infos = iconv('UTF-8', 'windows-1252', $infos);
		$pdf->MultiCell(0, 7, $infos);
		// Prix
		$pdf->setXY(10, 135);
		$pdf->setFont('Arial', 'B', 18);
		$pdf->SetTextColor(169, 2, 58);
		$infos = $_POST["prix_achat"]. "€ TTC";
		$infos = iconv('UTF-8', 'windows-1252', $infos);
		$pdf->Write(0, $infos);
		// Echeances - En-tête
		$pdf->SetTextColor(0, 0, 0);
		$pdf->setFont('Arial', '', 10);
		$pdf->Rect(10, 139, 35, 10);
		$pdf->setXY(10, 144);
		$infos = "Numéro d'écheance";
		$infos = iconv('UTF-8', 'windows-1252', $infos);
		$pdf->Write(0, $infos);
		$pdf->Rect(45, 139, 50, 10);
		$pdf->setXY(45, 144);
		$infos = "Date d'encaissement";
		$infos = iconv('UTF-8', 'windows-1252', $infos);
		$pdf->Write(0, $infos);
		$pdf->Rect(95, 139, 20, 10);
		$pdf->setXY(95, 144);
		$infos = "Montant";
		$infos = iconv('UTF-8', 'windows-1252', $infos);
		$pdf->Write(0, $infos);
		$pdf->Rect(115, 139, 85, 10);
		$pdf->setXY(115, 144);
		$infos = "Méthode de paiement";
		$infos = iconv('UTF-8', 'windows-1252', $infos);
		$pdf->Write(0, $infos);

		for($k = 0; $k < $echeances; $k++){
			$new_echeance = $db->prepare("INSERT INTO produits_echeances(id_produit_adherent, date_echeance, montant)
			VALUES(:transaction, :date_echeance, :prix)");
			$new_echeance->bindParam(':transaction', $transaction);
			$new_echeance->bindParam(':date_echeance', $_POST["date-echeance-".$k]);
			$new_echeance->bindParam(':prix', $_POST["montant-echeance-".$k]);
			$new_echeance->execute();
			
			//Echeances - Contenu du tableau
			$pdf->Rect(10, 149 + (8*$k), 35, 8);
			$pdf->setXY(10, 152 + (8*$k));
			$infos = "Echéance ".($k+1);
			$infos = iconv('UTF-8', 'windows-1252', $infos);
			$pdf->Write(0, $infos);
			$pdf->Rect(45, 149 + (8*$k), 50, 8);
			$pdf->setXY(45, 152 + (8*$k));
			$infos = $_POST["date-echeance-".$k];
			$infos = iconv('UTF-8', 'windows-1252', $infos);
			$pdf->Write(0, $infos);
			$pdf->Rect(95, 149 + (8*$k), 20, 8);
			$pdf->setXY(95, 152 + (8*$k));
			$infos = $_POST["montant-echeance-".$k];
			$infos = iconv('UTF-8', 'windows-1252', $infos);
			$pdf->Write(0, $infos);
			$pdf->Rect(115, 149 + (8*$k), 85, 8);
			$pdf->setXY(115, 152 + (8*$k));	
		}
        $db->commit();
		
		$pdf->Output();
		/**** /PDF ****/
		header('Location: dashboard.php');
    }catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}

function invitation(){
    $db = PDOFactory::getConnection();
	$data = explode(' ', $_POST["identite_nom"]);
	$prenom = $data[0];
	$nom = $data[1];
    $adherent = getAdherent($prenom, $nom);
	
	$transaction = generateReference();
    
    $date_achat = date_create("now")->format('Y-m-d H:i:s');
    
	$actif = 1;
	$volume_horaire = 0;
	$prix_achat = 0;
	$echeances = 0;
	$montant_echeance = 0;
	$arep = 0;
	
    try{
        $db->beginTransaction();
        $new = $db->prepare("INSERT INTO produits_adherents(id_transaction, id_adherent, id_produit, date_achat, date_activation, date_expiration, volume_cours, prix_achat, actif, arep)
        VALUES(:transaction, :adherent, :produit_id, :date_achat, :date_activation, :date_expiration, :volume_horaire, :prix_achat, :actif, :arep)");
		$new->bindParam(':transaction', $transaction);
        $new->bindParam(':adherent', $adherent["eleve_id"]);
        $new->bindParam(':produit_id', $_POST["produit"]);
        $new->bindParam(':date_achat', $date_achat);
        $new->bindParam(':date_activation', $_POST["date_activation"]);
        $new->bindParam(':date_expiration', $_POST["date_expiration"]);
        $new->bindParam(':volume_horaire', $volume_horaire);
        $new->bindParam(':prix_achat', $prix_achat);
        $new->bindParam(':actif', $actif);
        $new->bindParam(':arep', $arep);
        $new->execute();

        $db->commit();
		
		header('Location: dashboard.php');
    }catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
}
?>
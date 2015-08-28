<?php
require_once "db_connect.php";
include "librairies/fpdf/fpdf.php";
include "librairies/fpdi/fpdi.php";
require_once "tools.php";

function vente(){
	$db = PDOFactory::getConnection();

	/** La fonction vente réalise plusieurs actions. Elle :
	- Identifie le payeur
	- Crée une transaction en base
	- Crée tous les produits adhérents
	- Les associe à cette transaction
	- Crée toutes les échéances de la transaction
	**/

	// Obtention de l'identité du payeur
	$data = explode(' ', $_POST["payeur"]);
	$prenom = $data[0];
	$nom = '';
	for($i = 1; $i < count($data); $i++){
		$nom .= $data[$i];
		if($i != count($data)){
			$nom .= " ";
		}
	}
	$payeur = getAdherent($prenom, $nom);

	// Génération d'un identifiant unique désignant la transaction
	$transaction = generateReference();

	// Date de l'achat
	$date_achat = date_create("now")->format('Y-m-d H:i:s');

	// Obtention du nombre d'échéances pour la transaction
	$echeances = $_POST["echeances"];

	// Prix total à payer
	$prix_restant = $_POST["prix_total"];

	try{
		$db->beginTransaction();
		// Création de la transaction
		$new_transaction = $db->prepare("INSERT INTO transactions(id_transaction, payeur_transaction, date_achat, prix_total) VALUES(:transaction, :payeur, :date_achat, :prix_total)");
		$new_transaction->bindParam(':transaction', $transaction);
		$new_transaction->bindParam(':payeur', $payeur["user_id"]);
		$new_transaction->bindParam(':date_achat', $date_achat);
		$new_transaction->bindParam(':prix_total', $prix_restant);
		$new_transaction->execute();

		// Création de tous les produits associés à la transaction
		// LES ETAPES SUIVANTES SONT REPETEES POUR CHAQUE PRODUIT
		$l = 1;
		for($l; $l <= $_POST["nombre_produits"]; $l++){
			// Retrouver le produit à partir de son nom
			$queryProduit = $db->prepare("SELECT * FROM produits WHERE produit_nom=?");
			$nomProduit = $_POST["nom-produit-".$l];
			$queryProduit->bindParam(1, $nomProduit);
			$queryProduit->execute();
			$produit = $queryProduit->fetch(PDO::FETCH_ASSOC);

			// Si le forfait a une date d'activation
			if($_POST["activation-".$l] != "0"){
				$actif = 1;
				$date_expiration = date("Y-m-d 00:00:00",strtotime($_POST["activation-".$l].'+'.$produit["validite_initiale"].'DAYS'));
				$queryHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee >= ? AND date_chomee <= ?");
				$queryHoliday->bindParam(1, $_POST["activation-".$l]);
				$queryHoliday->bindParam(2, $date_expiration);
				$queryHoliday->execute();

				$j = 0;

				for($i = 1; $i <= $queryHoliday->rowCount(); $i++){
					$exp_date = date("Y-m-d 00:00:00",strtotime($date_expiration.'+'.$i.'DAYS'));
					$checkHoliday = $db->prepare("SELECT * FROM jours_chomes WHERE date_chomee=?");
					$checkHoliday->bindParam(1, $exp_date);
					$checkHoliday->execute();
					if($checkHoliday->rowCount() != 0){
						$j++;
					}
					$totalOffset = $i + $j;
					$new_exp_date = date("Y-m-d 00:00:00",strtotime($date_expiration.'+'.$totalOffset.'DAYS'));
				}
			} else {
				$actif = 0;
			}

			// Retrouver l'adhérent à partir de son nom
			$dataBeneficiaire = explode(' ', $_POST["beneficiaire-".$l]);
			$prenomBeneficiaire = $dataBeneficiaire[0];
			$nomBeneficiaire = '';
			for($m = 1; $m < count($dataBeneficiaire); $m++){
				$nomBeneficiaire .= $dataBeneficiaire[$m];
				if($m != count($dataBeneficiaire)){
					$nomBeneficiaire .= " ";
				}
			}
			$beneficiaire = getAdherent($prenomBeneficiaire, $nomBeneficiaire);

			$new = $db->prepare("INSERT INTO produits_adherents(id_transaction_foreign, id_user_foreign, id_produit_foreign, date_activation, date_expiration, volume_cours, prix_achat, actif, arep)
		VALUES(:transaction, :adherent, :produit, :date_activation, :date_expiration, :volume_horaire, :prix_achat, :actif, :arep)");
			$new->bindParam(':transaction', $transaction);
			$new->bindParam(':adherent', $beneficiaire["user_id"]);
			$new->bindParam(':produit', $produit["produit_id"]);
			$new->bindParam(':date_activation', $_POST["activation-".$l]);
			$new->bindParam(':date_expiration', $new_exp_date);
			$new->bindParam(':volume_horaire', $produit["volume_horaire"]);
			$new->bindParam(':prix_achat', $_POST["prix-produit-".$l]);
			$new->bindParam(':actif', $actif);
			$new->bindParam(':arep', $produit["autorisation_report"]);
			$new->execute();

			// Promotion de l'adhérent en tant que membre Salsabor si achat d'une adhésion annuelle
			if(stristr($_POST["nom-produit-".$l],"adhésion")){
				$upgrade = $db->prepare("UPDATE users SET est_membre=1 WHERE user_id=?");
				$upgrade->bindValue(1, $beneficiaire["user_id"]);
				$upgrade->execute();
			}
		}

		/**** PDF ****/
		/*$pdf = new FPDI();
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
		$infos = $adherent["user_prenom"]." ".$adherent["user_nom"]."\n".$adherent["rue"]." - ".$adherent["code_postal"]." ".$adherent["ville"]."\n".$adherent["mail"]."\nTél : ".$adherent["telephone"];
		$infos = iconv('UTF-8', 'windows-1252', $infos);
		$pdf->MultiCell(0, 7, $infos);
		// Vente
		$pdf->setXY(10, 117);
		if($_POST["date_activation"] != ''){
			$infos = "Forfait ".$produit["produit_nom"]."\nValide du ".date_create($_POST["date_activation"])->format("d/m/Y")." au ".date_create($new_exp_date)->format("d/m/Y");
		} else {
			$infos = "Activation au premier passage";
		}
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
		$pdf->Write(0, $infos);*/

		// Création de toutes les échéances associées à la transaction
		for($k = 1; $k <= $echeances; $k++){
			if($_POST["statut-echeance-".$k] == '1'){$date_paiement = $date_achat;}

			$new_echeance = $db->prepare("INSERT INTO produits_echeances(reference_achat, date_echeance, montant, payeur_echeance, methode_paiement, echeance_effectuee, date_paiement)
			VALUES(:transaction, :date_echeance, :prix, :payeur, :methode, :echeance_effectuee, :date_paiement)");
			$new_echeance->bindParam(':transaction', $transaction);
			$new_echeance->bindParam(':date_echeance', $_POST["date-echeance-".$k]);
			$new_echeance->bindParam(':prix', $_POST["montant-echeance-".$k]);
			$new_echeance->bindParam(':payeur', $_POST["titulaire-paiement-".$k]);
			$new_echeance->bindParam(':methode', $_POST["moyen-paiement-".$k]);
			$new_echeance->bindParam(':echeance_effectuee', $_POST["statut-echeance-".$k]);
			$new_echeance->bindParam('date_paiement', $date_achat);
			$new_echeance->execute();

			//Echeances - Contenu du tableau
			/*			$pdf->Rect(10, 149 + (8*$k), 35, 8);
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
			$infos = $_POST["moyen-paiement-".$k];
			$infos = iconv('UTF-8', 'windows-1252', $infos);
			$pdf->Write(0, $infos);*/
		}

		$emptyPanier = $db->query("TRUNCATE panier");
		$db->commit();

		//		$pdf->Output();
		/**** /PDF ****/
		header('Location: merci.php');
	}catch(PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
}

/** INVITATION **/
function invitation(){
	$db = PDOFactory::getConnection();
	$data = explode(' ', $_POST["identite_nom"]);
	$prenom = $data[0];
	$nom = '';
	for($i = 1; $i < count($data); $i++){
		$nom .= $data[$i];
		if($i != count($data)){
			$nom .= " ";
		}
	}
	$adherent = getAdherent($prenom, $nom);

	$transaction = generateReference();

	$date_achat = date_create("now")->format('Y-m-d H:i:s');

	$actif = 0;
	$volume_horaire = 0;
	$prix_achat = 0;
	$echeances = 0;
	$montant_echeance = 0;
	$arep = 0;

	try{
		$db->beginTransaction();

		if($_POST["id-cours"] != ''){
			$new = $db->prepare("INSERT INTO produits_adherents(id_transaction, id_user_foreign, id_produit, date_achat, volume_cours, prix_achat, actif, arep)
		VALUES(:transaction, :adherent, :produit_id, :date_achat, :volume_horaire, :prix_achat, :actif, :arep)");
			$new->bindParam(':transaction', $transaction);
			$new->bindParam(':adherent', $adherent["user_id"]);
			$new->bindParam(':produit_id', $_POST["produit"]);
			$new->bindParam(':date_achat', $date_achat);
			$new->bindParam(':volume_horaire', $volume_horaire);
			$new->bindParam(':prix_achat', $prix_achat);
			$new->bindParam(':actif', $actif);
			$new->bindParam(':arep', $arep);
			$new->execute();

			$passage = $db->prepare("INSERT INTO cours_participants(cours_id_foreign, eleve_id_foreign, produit_adherent_id) VALUES(:cours, :eleve, :transaction)");
			$passage->bindParam(':cours', $_POST["id-cours"]);
			$passage->bindParam(':eleve', $adherent["user_id"]);
			$passage->bindParam(':transaction', $transaction);
			$passage->execute();
		} else {
			$actif = 1;

			$new = $db->prepare("INSERT INTO produits_adherents(id_transaction, id_user_foreign, id_produit, date_achat, date_activation, date_expiration, volume_cours, prix_achat, actif, arep)
		VALUES(:transaction, :adherent, :produit_id, :date_achat, :date_activation, :date_expiration, :volume_horaire, :prix_achat, :actif, :arep)");
			$new->bindParam(':transaction', $transaction);
			$new->bindParam(':adherent', $adherent["user_id"]);
			$new->bindParam(':produit_id', $_POST["produit"]);
			$new->bindParam(':date_achat', $date_achat);
			$new->bindParam(':date_activation', $_POST["date_activation"]);
			$new->bindParam(':date_expiration', $_POST["date_expiration"]);
			$new->bindParam(':volume_horaire', $volume_horaire);
			$new->bindParam(':prix_achat', $prix_achat);
			$new->bindParam(':actif', $actif);
			$new->bindParam(':arep', $arep);
			$new->execute();
		}

		$db->commit();

		header('Location: dashboard.php');
	}catch(PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
}
?>

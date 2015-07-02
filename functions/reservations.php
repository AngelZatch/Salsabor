<?php
require_once "db_connect.php";
include "librairies/fpdf/fpdf.php";
include "librairies/fpdi/fpdi.php";

class ReservationPDF extends FPDF
{
	var $col = 0;
	var $y0;
	
	function Header(){
		global $titre;
		$this->setFont('Arial', 'B', 15);
		$w = $this->GetStringWidth($titre)+6;
		$this->SetX((210 - $w)/2);
		$this->SetLineWidth(1);
		$this->Cell($w,9,$titre,1,1,'C',false);
		$this->Ln(10);
		$this->y0 = $this->GetY();
	}
	
	function PersonalInformation(){
		$db = PDOFactory::getConnection();
		$eleve = $db->prepare('SELECT * FROM adherents WHERE eleve_id=?');
		$eleve->bindValue(1, 1);
		$eleve->execute();
		$res_eleve = $eleve->fetch(PDO::FETCH_ASSOC);
	}
	
	function ReservationDetails(){
		
	}
}

function addResa(){
	$demandeur = $_POST['identite'];
	$prestation = $_POST['prestation'];
	$date_debut = $_POST['date_debut']." ".$_POST['heure_debut'];
	$date_fin = $_POST['date_debut']." ".$_POST['heure_fin'];
	$lieu = $_POST['lieu'];
	
	$unite = (strtotime($_POST['heure_fin']) - strtotime($_POST['heure_debut']))/3600;
	$prix = $_POST['prix_resa'];
	
	$priorite = $_POST['priorite'];
	$paiement = $_POST['paiement'];
	
	$pdf = new FPDI();
	$pdf->AddPage();
	$pdf->SetSourceFile("librairies/Salsabor-resa-facture.pdf");
	$tplIdx = $pdf->importPage(1);
	$pdf->useTemplate($tplIdx, 0, 0, 210);
	$pdf->setXY(10, 73);
	$pdf->SetFont('Arial', '', 12);
	
	$infos = "M.\n".$demandeur."\n51, rue Servan - 75011 Paris\npinbouen.andreas@gmail.com\nTél : 06 82 71 11 71";
	$infos = iconv('UTF-8', 'windows-1252', $infos);
	$pdf->MultiCell(0, 7, $infos);
	/**	
	// Réservation
	if($priorite == 0) {
		$textPriorite = 'libre (Attention : une réservation libre peut être supprimée sans préavis au profit d\'un cours)';
	} else $textPriorite = 'payée';
	$reservation = "Détail de la réservation : \n".$_POST['prestation']."\n Le ".date_create($date_debut)->format('d/m/Y')." de ".date_create($date_debut)->format('H:i')." à ".date_create($date_fin)->format('H:i')."\nRéservation ".$textPriorite;
	$reservation = iconv('UTF-8', 'windows-1252', $reservation);
	
	$pdf->SetFont('Arial', '', 15);
	$pdf->MultiCell(0, 10, "Informations du Demandeur : \n".$infos, 1, 1);
	$pdf->MultiCell(0, 10, $reservation, 1, 1);**/
	$pdf->Output();
	
	/**$pdf = new ReservationPDF();
	$titre = "FACTURE DE RESERVATION DE SALLE";
	$pdf->setTitle($titre);
	$pdf->Output();**/
	
	/**$db = PDOFactory::getConnection();
	try{
		$db->beginTransaction();
		$insertResa = $db->prepare('INSERT INTO reservations(reservation_personne, type_prestation, reservation_start, reservation_end, reservation_salle, reservation_unite, reservation_prix, priorite, paiement_effectue)
		VALUES(:reservation_personne, :type_prestation, :reservation_start, :reservation_end, :lieu, :unite, :prix, :priorite, :paiement_effectue)');
		$insertResa->bindParam(':reservation_personne', $demandeur);
		$insertResa->bindParam(':type_prestation', $prestation);
		$insertResa->bindParam(':reservation_start', $date_debut);
		$insertResa->bindParam(':reservation_end', $date_fin);
		$insertResa->bindParam(':lieu', $lieu);
		$insertResa->bindParam(':unite', $unite);
		$insertResa->bindParam(':prix', $prix);
		$insertResa->bindParam(':priorite', $priorite);
		$insertResa->bindParam(':paiement_effectue', $paiement);
		$insertResa->execute();
		
		$db->commit();
		// On génère le PDF "facture" une fois que la transaction est terminée
	} catch(PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}**/
}

function deleteResa(){
	$index = $_POST['id'];
    $db = PDOFactory::getConnection();
    try{
        $db->beginTransaction();
        $delete = $db->prepare('DELETE FROM reservations WHERE reservation_id=?');
        $delete->bindValue(1, $index, PDO::PARAM_INT);
        $delete->execute();
        $db->commit();
    } catch(PDOException $e){
        $db->rollBack();
        var_dump($e->getMessage());
    }
    header('Location: planning.php');
}
<?php
require_once "db_connect.php";
include "librairies/fpdf.php";
function addResa(){
	$demandeur = $_POST['identite'];
	$prestation = $_POST['prestation'];
	$date_debut = $_POST['date_debut']." ".$_POST['heure_debut'];
	$date_fin = $_POST['date_debut']." ".$_POST['heure_fin'];
	$lieu = $_POST['lieu'];
	
	$unite = (strtotime($_POST['heure_fin']) - strtotime($_POST['heure_debut']))/3600;
	$prix = $_POST['prix_resa'];
	
	$priorite = 0;
	$paiement = 0;
	
	$db = PDOFactory::getConnection();
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
		/**$pdf = new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial', 'B', 15);
		$pdf->Cell(40,10, $demandeur);
		$pdf->Output();**/
	} catch(PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
}

function deleteResa(){
	
}
<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

// This file is called when loading all the "yet to be banked" maturities
$date = new DateTime('now');
$year = $date->format('Y');
$month = $date->format('m');
$day = $date->format('d');
if($day >= 1 && $day <= 8){
	$maturityDay = 10;
} else if($day >= 9 && $day <= 18){
	$maturityDay = 20;
} else if($day >= 19 && $day <= 28){
	$maturityDay = 30;
}else{
	$maturityDay = 10;
	$month++;
	if($month > 12){
		$year++;
		$month = 1;
	}
}
$time = new DateTime($year.'-'.$month.'-'.$maturityDay);
$maturityTime = $time->format('Y-m-d');

$today = $date->format("Y-m-d");

$load = $db->query("SELECT * FROM produits_echeances pe
						JOIN transactions t ON pe.reference_achat = t.id_transaction
						WHERE ((date_echeance <= '$maturityTime' AND date_echeance > '$today' AND (methode_paiement !='Carte Bancaire' OR methode_paiement IS NULL))
						OR (date_echeance <= '$today' AND date_encaissement IS NULL))
						ORDER BY date_echeance DESC");

$maturities = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$m = array(
		"id" => $details["produits_echeances_id"],
		"transaction_id" => $details["reference_achat"],
		"transaction_user" => $details["payeur_transaction"],
		"payer" => $details["payeur_echeance"],
		"date" => $details["date_echeance"],
		"price" => $details["montant"],
		"method" => ($details["methode_paiement"]!=NULL)?$details["methode_paiement"]:"En attente",
		"reception_status" => $details["echeance_effectuee"],
		"date_reception" => $details["date_paiement"],
		"bank_status" => $details["statut_banque"],
		"date_bank" => $details["date_encaissement"],
		"lock_montant" => $details["lock_montant"]
	);
	array_push($maturities, $m);
}
echo json_encode($maturities);
?>

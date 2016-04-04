<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

if(isset($_GET["carte"])){
	$data = explode('*', $_GET["carte"]);
	$tag_rfid = $data[0];
	$ip_rfid = $data[1];
	add($tag_rfid, $ip_rfid);
}

if(isset($_POST["add"])){
	$tag_rfid = $_POST["tag"];
	$ip_rfid = $_POST["salle"];
	add($tag_rfid, $ip_rfid);
}

function add($tag, $ip){
	$db = PDOFactory::getConnection();
	$today = date_create('now')->format('Y-m-d H:i:s');

	if($ip == "192.168.0.3"){
		$status = "1";
	} else {
		// If the tag is not for associating, we search a product that could be used for this session.
		// First, we get the name of the session and the ID of the user.
		// For the session, we have to find it based on the time of the record and the position.
		$session = $db->query("SELECT cours_intitule, cours_id FROM cours c
								JOIN lecteurs_rfid lr ON c.cours_salle = lr.lecteur_lieu
								WHERE ouvert = '1' AND lecteur_ip = '$ip'")->fetch(PDO::FETCH_GROUP);
		$cours_name = $session["cours_intitule"];
		$session_id = $session["cours_id"];
		$user_id = $db->query("SELECT user_id FROM users WHERE user_rfid = '$tag'")->fetch(PDO::FETCH_COLUMN);


		if($session_id != null){ // If we could find a session, then we're gonna look for a product.
			if(preg_match("/jazz/i", $cours_name, $matches) || preg_match("/pilates/i", $cours_name, $matches) || preg_match("/particulier/i", $cours_name, $matches)){ // Search for specific Jazz, Pilates or private sessions
				/*echo $matches[0];*/
				$checkSpecific = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom LIKE '%$matches[0]%'
									AND pa.actif != '2'
									ORDER BY date_achat ASC");
				if($checkSpecific->rowCount() > 0){
					$product = $checkSpecific->fetch(PDO::FETCH_ASSOC);
				}
			} else { // First, we search for any freebies
				$checkInvitation = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom = 'Invitation'
									AND pa.actif = '0'
									ORDER BY date_achat ASC");
				if($checkInvitation->rowCount() > 0){ // If there are freebies still available, we take the first one.
					$product = $checkInvitation->fetch(PDO::FETCH_ASSOC);
				} else { // If no freebies, we look for every currently active products.
					$checkActive = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom != 'Invitation'
									AND pa.actif = '1'
									AND est_abonnement = '0'
									AND est_cours_particulier = '0'
									ORDER BY date_achat ASC");
					if($checkActive->rowCount() > 0){ // If there are active products that are not an annual sub
						$product = $checkActive->fetch(PDO::FETCH_ASSOC);
					} else { // We check inactive products now.
						$checkPending = $db->query("SELECT id_produit_adherent, id_produit_foreign, produit_nom, pa.actif AS produit_adherent_actif, date_achat FROM produits_adherents pa
									JOIN produits p ON pa.id_produit_foreign = p.produit_id
									JOIN transactions t ON pa.id_transaction_foreign = t.id_transaction
									WHERE id_user_foreign='$user_id'
									AND produit_nom != 'Invitation'
									AND pa.actif = '0'
									AND est_abonnement = '0'
									AND est_cours_particulier = '0'
									ORDER BY date_achat ASC");
						if($checkPending->rowCount() > 0){
							$product = $checkPending->fetch(PDO::FETCH_ASSOC);
						}
					}
				}
			}
			if(isset($product)){
				$product_id = $product["id_produit_adherent"];
				$status = "0";
			} else {
				$product = NULL;
				$status = "3";
			}
			$new = $db->query("INSERT INTO passages(passage_eleve, passage_eleve_id, passage_salle, passage_date, cours_id, produit_adherent_cible, status)
					VALUES('$tag', '$user_id', '$ip', '$today', '$session_id', '$product_id', '$status')");
		} else {
			$status = "2";
			$new = $db->query("INSERT INTO passages(passage_eleve, passage_eleve_id, passage_salle, passage_date, status)
					VALUES('$tag', '$user_id', '$ip', '$today', '$status')");
		}
	}


	header('Location: passages.php');
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Template - Salsabor</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-qrcode"></span> Simuler un passage RFID</legend>
					<p class="page-title"></p>
					<form action="" method="post">
						<label for="tag">Tag</label>
						<input type="text" name="tag" class="form-control">

						<label for="salle">Salle du lecteur</label>
						<input type="text" name="salle" class="form-control">

						<input type="submit" value="SIMULER UN PASSAGE" name="add" class="btn btn-primary confirm-add">
					</form>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>

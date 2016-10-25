<?php
session_start();
require_once 'functions/db_connect.php';
include "functions/mails.php";
include "functions/tools.php";
$db = PDOFactory::getConnection();
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
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-warning-sign"></span> Page Test !</legend>
					<?php
					$loading = microtime();
					$loading = explode(' ', $loading);
					$loading = $loading[1] + $loading[0];
					$start = $loading;
					$searchTerms = "An";
					$location = 2;
					/** CODE **/
					$general_query = "SELECT u.user_id, CONCAT(u.user_prenom, ' ', u.user_nom) AS identity, u.user_prenom, u.user_nom, u.mail, u.telephone, u.photo, u.user_location, u.actif, u.archived FROM users u";
					$general_where = " (u.user_nom LIKE ? OR u.user_prenom LIKE ? OR u.mail LIKE ? OR u.telephone LIKE ?)";
					$criteria_array = array("%".$searchTerms."%",
											"%".$searchTerms."%",
											"%".$searchTerms."%",
											"%".$searchTerms."%");

					// Query to find staff
					$staff_query = $general_query." WHERE user_location = $location AND".$general_where;
					if(isset($_GET["archive"]) && $_GET["archive"] == "0")
						$staff_query .= " AND archived = 0";
					$match_by_staff = $db->prepare($staff_query);
					$match_by_staff->execute($criteria_array);

					// Query to find by participations
					$by_participations_query = $general_query." RIGHT JOIN participations p ON u.user_id = p.user_id
				LEFT JOIN sessions s ON p.session_id = s.session_id
				LEFT JOIN rooms r ON s.session_room = r.room_id
				LEFT JOIN locations l ON r.room_location = l.location_id
				WHERE".$general_where." AND (l.location_id = $location OR u.user_location = $location)";
					if(isset($_GET["archive"]) && $_GET["archive"] == "0")
						$by_participations_query .= " AND archived = 0";
					$by_participations_query .= " GROUP BY u.user_id ORDER BY u.archived ASC, u.actif DESC, u.user_nom ASC, u.user_prenom ASC";
					$match_by_participations = $db->prepare($by_participations_query);
					$match_by_participations->execute($criteria_array);

					// Query to find by transactions
					$by_transactions_query = $general_query." RIGHT JOIN transactions t on u.user_id = t.payeur_transaction
				LEFT JOIN users u2 ON t.transaction_handler = u2.user_id
				LEFT JOIN locations l ON u2.user_location = l.location_id
				WHERE".$general_where." AND (u2.user_location = $location)";
					if(isset($_GET["archive"]) && $_GET["archive"] == "0")
						$by_transactions_query .= " AND archived = 0";
					$by_transactions_query .= " GROUP BY l.location_id ORDER BY u.archived ASC, u.actif DESC, u.user_nom ASC, u.user_prenom ASC";
					$match_by_transactions = $db->prepare($by_transactions_query);
					$match_by_transactions->execute($criteria_array);

					$result_array = array();
					while($match = $match_by_staff->fetch(PDO::FETCH_ASSOC)){
						array_push($result_array, $match);
					}
					while($match = $match_by_participations->fetch(PDO::FETCH_ASSOC)){
						array_push($result_array, $match);
					}
					while($match = $match_by_transactions->fetch(PDO::FETCH_ASSOC)){
						array_push($result_array, $match);
					}

					?>
					<pre>
						<?php
/*print_r($match_by_staff->fetchAll(PDO::FETCH_ASSOC));
print_r($match_by_participations->fetchAll(PDO::FETCH_ASSOC));
print_r($match_by_transactions->fetchAll(PDO::FETCH_ASSOC));*/

$result = array_intersect_key($result_array, array_unique(array_map('serialize' , $result_array)));
usort($result, function($a, $b){
	if($a['user_nom'] == $b['user_prenom']){
		return ($a['user_prenom'] < $b['user_prenom']) ? -1 : 1;
	}
	return ($a['user_nom'] < $b['user_nom']) ? -1 : 1;
});
print_r($result);
						?>
					</pre>

					<?php
					/** /CODE **/
					$loading = microtime();
					$loading = explode(' ', $loading);
					$loading = $loading[1] + $loading[0];
					$finish = $loading;
					$total = round(($finish - $start), 4);
					echo "<br>Traitement effectué en ".$total." secondes";
					?>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
		</script>
	</body>
</html>
<script>
</script>

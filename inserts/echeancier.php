<?php
$queryEcheances->execute();
if(basename($_SERVER["PHP_SELF"]) == "echeances.php"){ ?>
	<p>Au total, <?php echo $queryEcheances->rowCount();?> échéances à encaisser pour cette date.</p>
<?php } ?>
<table class="table">
	<thead>
		<tr>
			<th class="col-lg-2">Date</th>
			<?php if(basename($_SERVER['PHP_SELF']) == "echeances.php"){ ?>
			<th class="col-lg-1">Transaction</th>
			<?php } ?>
			<th class="col-lg-2">Montant</th>
			<th class="col-lg-2">Méthode</th>
			<th class="col-lg-2">Payeur</th>
			<th class="col-lg-2">Statut Salsabor</th>
			<th class="col-lg-2">Statut Banque</th>
		</tr>
	</thead>
	<tbody id="filter-enabled" class="list">
		<?php while($echeances = $queryEcheances->fetch(PDO::FETCH_ASSOC)){
	switch($echeances["echeance_effectuee"]){
		case 0:
		$status = "En attente";
		$statusClass = "default";
		break;

		case 1:
		$status = "Réceptionnée";
		$statusClass = "success";
		break;

		case 2:
		$status = "En retard";
		$statusClass = "danger";
		break;
	} ?>
		<tr>
			<td class="date col-lg-1"><span class="editable" id="date_echeance-<?php echo $echeances["produits_echeances_id"];?>"><?php echo date_create($echeances["date_echeance"])->format('d/m/Y');?></span></td>
			<?php if(basename($_SERVER['PHP_SELF']) == "echeances.php"){ ?>
			<td class="forfait-name"><a href="transaction_details.php?id=<?php echo $echeances["id_transaction_foreign"];?>&status=echeances"><?php echo $echeances["id_transaction_foreign"];?></a></td>
			<?php } ?>
			<td class="montant"><span class="editable" id="montant-<?php echo $echeances["produits_echeances_id"];?>"><?php echo $echeances["montant"];?></span> €</td>
			<td><span class="editable" id="methode_paiement-<?php echo $echeances["produits_echeances_id"];?>"><?php echo $echeances["methode_paiement"];?></span></td>
			<td class="user-name"><span class="editable" id="payeur_echeance-<?php echo $echeances["produits_echeances_id"];?>"><?php echo $echeances["payeur_echeance"];?></span></td>
			<td class="status">
				<?php if($status == "Réceptionnée"){ ?>
				<span class="label label-<?php echo $statusClass;?>"><span class="glyphicon glyphicon-ok"></span> (<?php echo date_create($echeances["date_paiement"])->format('d/m/Y');?>)</span>
				<span class="statut-salsabor span-btn glyphicon glyphicon-remove"></span>
				<?php } else if($status == "En retard"){ ?>
				<span class="label label-<?php echo $statusClass;?>"><span class="glyphicon glyphicon-fire"></span> <?php echo $status;?></span>
				<span class="statut-salsabor span-btn glyphicon glyphicon-download-alt"></span>
				<?php } else { ?>
				<span class="label label-info"><span class="glyphicon glyphicon-option-horizontal"></span> En attente</span>
				<span class="statut-salsabor span-btn glyphicon glyphicon-download-alt"></span>
				<?php } ?>
				<input type="hidden" name="echeance-id" value="<?php echo $echeances["produits_echeances_id"];?>">
			</td>
			<td class="bank">
				<?php if($echeances["statut_banque"] == '1'){ ?>
				<span class="label label-success"><span class="glyphicon glyphicon-ok"></span> (<?php echo date_create($echeances["date_encaissement"])->format('d/m/Y');?>)</span>
				<span class="statut-banque span-btn glyphicon glyphicon-remove"></span>
				<?php } else { ?>
				<span class="label label-info"><span class="glyphicon glyphicon-option-horizontal"></span> Dépôt à venir</span>
				<span class="statut-banque span-btn glyphicon glyphicon-download-alt"></span>
				<?php } ?>
				<input type="hidden" name="echeance-id" value="<?php echo $echeances["produits_echeances_id"];?>">
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>
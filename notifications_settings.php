<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$notifications = $db->query("SELECT * FROM master_settings WHERE user_id = '0'")->fetch(PDO::FETCH_ASSOC);
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Réglage des notifications | Salsabor</title>
		<base href="../">
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<legend><span class="glyphicon glyphicon-cog"></span> Réglage des notifications<button class="btn btn-primary save-settings">Enregistrer</button></legend>
					<p>Attention ! Cette page manipule des valeurs qui assurent le bon fonctionnement de l'application. Ne modifiez pas ces valeurs n'importe comment!</p>
					<p class="sub-legend">Notifications qui affectent l'utilisateur</p>
					<p>Ces valeurs affectent le rythme auquel les utilisateurs recevront des notifications par mail de l'état de leur consommation. Evitez de les changer régulièrement ou vous pourriez spammer les consommateurs.</p>
					<form action="" class="form-horizontal" name="master_values" id="master_values">
						<div class="form-group">
							<label for="" class="control-label col-sm-4">Jours avant l'expiration des forfaits</label>
							<div class="col-sm-2">
								<div class="input-group">
									<input type="number" class="form-control" name="days_before_exp" value="<?php echo $notifications["days_before_exp"];?>">
									<span class="input-group-addon">jours</span>
								</div>
							</div>
							<span class="label-tip">Le système enverra une notification à l'utilisateur lorsque son forfait sera proche de l'expiration par ce nombre de jours.</span>
						</div>
						<div class="form-group">
							<label for="" class="control-label col-sm-4">Volume restant</label>
							<div class="col-sm-2">
								<div class="input-group">
									<input type="number" class="form-control" name="hours_before_exp" value="<?php echo $notifications["hours_before_exp"];?>">
									<span class="input-group-addon">heures</span>
								</div>
							</div>
							<span class="label-tip">L'utilisateur sera notifié de l'expiration prochaine de son forfait lorsqu'il ne lui restera plus que quelques heures de disponibles, réglable ici.</span>
						</div>
						<div class="form-group">
							<label for="" class="control-label col-sm-4">Approche d'échéance</label>
							<div class="col-sm-2">
								<div class="input-group">
									<input type="number" class="form-control" name="days_before_maturity" value="<?php echo $notifications["days_before_maturity"];?>">
									<span class="input-group-addon">jours</span>
								</div>
							</div>
							<span class="label-tip">Rappelle l'utilisateur qu'une échéance de transaction arrive prochainement.</span>
						</div>
						<div class="form-group">
							<label for="" class="control-label col-sm-4">Retard d'échéance</label>
							<div class="col-sm-2">
								<div class="input-group">
									<input type="number" class="form-control" name="days_after_maturity" value="<?php echo $notifications["days_after_maturity"];?>">
									<span class="input-group-addon">jours</span>
								</div>
							</div>
							<span class="label-tip">Rappelle l'utilisateur qu'une échéance de transaction est en retard.</span>
						</div>
						<p class="sub-legend">Notifications exclusives à l'équipe de gestion</p>
						<div class="form-group">
							<label for="" class="control-label col-sm-4">Délai après expiration</label>
							<div class="col-sm-2">
								<div class="input-group">
									<input type="number" class="form-control" name="days_after_exp" value="<?php echo $notifications["days_after_exp"];?>">
									<span class="input-group-addon">jours</span>
								</div>
							</div>
							<span class="label-tip">Cette notification n'affectera pas l'utilisateur. Elle sert à l'équipe de gestion pour les produits ayant expiré récemment</span>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<script>
			$(document).on('click', '.save-settings', function(){
				var form = $("#master_values");
				$.post("functions/set_notifications_settings.php", {values : form.serialize()}).done(function(){
					$(".save-settings").blur();
					$(".save-settings").text("Modifications enregistrées");
					$(".save-settings").switchClass("btn-primary", "btn-success", 200, "easeOutBack");
					setTimeout(function(){
						$(".save-settings").switchClass("btn-success feedback", "btn-primary", 1000, "easeInQuad")
						$(".save-settings").text("Enregistrer");
					}, 1500);
				})
			})
		</script>
	</body>
</html>
<script>
</script>

<?php
require_once "../functions/db_connect.php";
$db = PDOFactory::getConnection();
?>
<div class="row">
	<form action="staff_liste.php?rank=0" method="post" class="lightbox-form">
		<div class="form-group">
			<label for="rank_name" class="control-label">Nom du Rang</label>
			<input type="text" class="form-control" name="rank_name" placeholder="rank_name">
			<br>
			<input type="submit" name="addRank" value="Ajouter" class="btn btn-default">
		</div>
	</form>
</div>

<div class="modal fade" id="maturity-modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"></h4>
				<p class="purchase-sub"></p>
			</div>
			<div class="modal-purchase modal-body">
				<div class="container-fluid purchase-details-container">
					<div class="col-lg-9">
						<h2 class="modal-body-title first">Statut de l'échéance</h2>
						<div class="container-fluid">
							<div class="col-lg-6">
								<div class="product-status-container maturity-status">
									<p class="value-slot" title="Montant"><span class="glyphicon glyphicon-euro"></span><span class="value-slot-value"> - </span></p>
									<p class="method-slot" title="Méthode"><span class="glyphicon glyphicon-time"></span><span class="method-slot-value"> - </span></p>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="product-status-container maturity-status">
									<p class="deadline-slot" title="Date limite"><span class="glyphicon glyphicon-time"></span><span class="deadline-slot-date"> - </span></p>
									<p class="reception-slot" title="Date de réception"><span class="glyphicon glyphicon-ok"></span><span class="reception-slot-date"> - </span></p>
									<p class="bank-slot" title="Date d'encaissement"><span class="glyphicon glyphicon-download-alt"></span><span class="bank-slot-date"> - </span></p>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-3 modal-actions-container">
						<h2 class="modal-body-title first">Actions</h2>
						<div class="modal-actions"></div>
					</div>
				</div>
				<?php include "sub_modal_product.php";?>
			</div>
		</div>
	</div>
</div>

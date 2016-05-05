<?php if(preg_match("/historique/",$_SERVER["REQUEST_URI"],$matches) || preg_match("/participations/",$_SERVER["REQUEST_URI"],$matches) || preg_match("/cours/",$_SERVER["REQUEST_URI"],$matches)){
	$width = "3";
} else {
	$width = "7";
}?>
<div class="col-lg-<?php echo $width;?> sub-modal">
	<div class="sub-modal-header">
		<button type="button" class="close sub-modal-close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<p class="sub-modal-title"></p>
	</div>
	<div class="sub-modal-body container-fluid">
	</div>
	<div class="sub-modal-footer"></div>
</div>

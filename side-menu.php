<?php
$menu = $db->query("SELECT * FROM app_menus am
					JOIN app_pages ap ON am.menu_id = ap.page_menu
					WHERE am.is_visible = 1
					ORDER BY am.menu_id ASC");
?>
<div class="sidebar-container">
	<div class="hidden-xs col-md-3 col-lg-2 sidebar separate-scroll" id="large-menu" style="display:block;">
		<ul class="nav nav-sidebar">
			<?php
			$previousMenu = -1;
			while($option = $menu->fetch(PDO::FETCH_ASSOC)){
				$page_title = $option["page_name"];
				$menu_title = $option["menu_name"];
				$page_glyph = $option["page_glyph"];
				$menu_glyph = $option["menu_glyph"];
				$url = $option["page_url"];
				$menu_id = $option["menu_id"];
				$badge = $option["badge"];
				if(isset($badge)){
					$page_title .= " <span class='badge sidebar-badge badge-$badge' id='badge-$badge'></span>";
				}
				if($previousMenu != $menu_id){
					if($previousMenu != -1){
			?>
		</ul>
		<?php } ?>
		<li><a class="main-section"><span class="glyphicon glyphicon-<?php echo $menu_glyph;?>"></span> <?php echo $menu_title;?> </a></li>
		<ul class="nav nav-sub-sidebar">
			<?php } ?>
			<li class="main-option-container"><a href="<?php echo $url;?>" class="main-option"><span class="glyphicon glyphicon-<?php echo $page_glyph;?>"></span> <?php echo $page_title;?></a></li>
			<?php
				$previousMenu = $menu_id;
			}
			?>
		</ul>
	</div>
</div>

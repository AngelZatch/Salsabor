<?php
$menu = $db->query("SELECT * FROM app_menus am
					JOIN app_pages ap ON am.menu_id = ap.page_menu
					LEFT JOIN assoc_page_tags apt ON ap.page_id = apt.page_id_foreign
					WHERE am.is_visible = 1
					AND (tag_id_foreign IN (SELECT tag_id_foreign FROM assoc_user_tags WHERE user_id_foreign = $_SESSION[user_id]) OR tag_id_foreign IS NULL)
					GROUP BY ap.page_id
					ORDER BY am.menu_order, ap.page_order ASC");
?>
<div class="sidebar-container">
	<div class="hidden-xs col-sm-3 col-lg-2 sidebar separate-scroll" id="large-menu" style="display:block;">
		<ul class="nav nav-sidebar">
			<?php
			$previousMenu = -1;
			while($option = $menu->fetch(PDO::FETCH_ASSOC)){
				$page_title = $option["page_name"];
				$menu_id = $option["menu_id"];
				$badge = $option["badge"];
				if(isset($badge)){
					$page_title .= " <span class='badge sidebar-badge badge-$badge' id='badge-$badge'></span>";
				}
				if($previousMenu != $menu_id){
					if($previousMenu != -1){ ?>
		</ul>
		<?php } ?>
		<li><a class="main-section"><span class="glyphicon glyphicon-<?php echo $option["menu_glyph"];?>"></span> <?php echo $option["menu_name"];?> </a></li>
		<ul class="nav nav-sub-sidebar">
			<?php } ?>
			<li class="main-option-container"><a href="<?php echo $option["page_url"];?>" class="main-option"><span class="glyphicon glyphicon-<?php echo $option["page_glyph"];?>"></span> <?php echo $page_title;?></a></li>
			<?php
				$previousMenu = $menu_id;
			}
			?>
		</ul>
	</div>
</div>

$(document).ready(toggleNavTabs());
$("li[id$=-toggle]").css('cursor', 'pointer');

function toggleNavTabs(){
	$("section").hide();
	var token = $(".active").attr('id').replace("-toggle", "");
	$("section#"+token).show();	
}

$("li[id$=-toggle]").click(function(){
    $("li").attr('class', '');
    $(this).attr('class', 'active');
    toggleNavTabs();
});
$(document).on('click', '.label-deletable', function(){
	var id = $(this).attr("id");
	var target = document.getElementById(id).dataset.target;
	$.when(deleteEntry("user_ranks", target)).done(function(data){
		$("#"+id).remove();
	});
}).on('click', '.label-addable', function(e){
	e.stopPropagation();
	var tag = document.getElementById($(this).attr("id")).dataset.tag;
	var user = /([0-9]+)/.exec(document.location.href);
	var tag_text = $(this).text();
	if($(this).hasClass("toggled")){
		$.post("functions/detach_tag.php", {tag : tag, user : user[0]}).done(function(data){
			$("#tag-"+tag).removeClass("toggled");
			$("#tag-"+tag).find("span").remove();
			$("#user-tag-"+data).remove();
		})
	} else {
		$.post("functions/attach_tag.php", {tag : tag, user : user[0]}).done(function(data){
			$("#tag-"+tag).addClass("toggled");
			$("#tag-"+tag).append("<span class='glyphicon glyphicon-ok remove-extension'></span>");
			$(".label-add").before("<span class='label label-salsabor label-clickable label-deletable' title='Supprimer l&apos;étiquette' id='user-tag-"+data+"' data-target='"+data+"'>"+tag_text+"</span>");
		})
	}
}).on('click', '.label-new-tag', function(){
	$(this).before("<input class='tag-input form-control' placeholder='Titre de l&apos;étiquette'>");
	$(".tag-input").focus();
}).on('focus', '.tag-input', function(){
	$(this).keyup(function(event){
		if(event.which == 13){
			var tag_name = $(this).val();
			createUserTag(tag_name);
		} else if(event.which == 27){
			$(".tag-input").remove();
		}
	})
})

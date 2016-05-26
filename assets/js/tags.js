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
}).on('click', '.color-cube', function(e){
	// Assign a color to a tag
	e.stopPropagation();
	var cube = $(this);
	var target = document.getElementById(cube.attr("id")).dataset.target;
	var value = /([a-z0-9]+)/i.exec(cube.css("backgroundColor"));
	$.when(updateColumn("tags_user", "tag_color", value[0], target)).done(function(data){
		$("#tag-"+target).css("background-color", "#"+value[0]);
		$(".color-cube").empty();
		cube.append("<span class='glyphicon glyphicon-ok color-selected'></span>");
	})
}).on('click', '.btn-tag-name', function(){
	var target = $("#edit-tag-name").data().target;
	var value = $("#edit-tag-name").val();
	$.when(updateColumn("tags_user", "rank_name", value, target)).done(function(data){
		$("#tag-"+target).text(value);
	})
}).on('click', '.delete-tag', function(){
	$(".sub-modal").hide(0);
	var target = $("#delete-tag").data().target;
	$.when(deleteEntry("tags_user", target)).done(function(){
		$("#edit-"+target).remove();
		$("#tag-"+target).remove();
	})
})

function fetchUserTags(){
	return $.get("functions/fetch_user_tags.php");
}

function displayTargetTags(data){
	var tags = JSON.parse(data), addable = "", added = "", body = "";
	for(var i = 0; i < tags.length; i++){
		$(".label-deletable").each(function(){
			console.log($(this).text(), tags[i].rank_name, tags[i].rank_name == $(this).text());
			if(tags[i].rank_name == $(this).text()){
				addable = " toggled";
				added = " <span class='glyphicon glyphicon-ok remove-extension'></span>";
				return false;
			} else {
				addable = "";
				added = "";
			}
		})
		body += "<h4><span class='label col-xs-12 label-clickable label-addable"+addable+"' id='tag-"+tags[i].rank_id+"' data-tag='"+tags[i].rank_id+"' style='background-color:"+tags[i].color+"'>"+tags[i].rank_name+added+"</span></h4>";
	}
	body += "<h4><span class='label col-xs-12 label-default label-clickable label-new-tag'>Créer une étiquette</span></h4>";
	return body;
}

function createUserTag(tag_name){
	$.post("functions/create_user_tag.php", {name : tag_name}).done(function(data){
		if(top.location.pathname === "/Salsabor/tags"){
			$(".tag-input").replaceWith("<span class='label col-xs-7 label-salsabor label-clickable label-addable' id='tag-"+data+"' data-tag='"+data+"'>"+tag_name+"</span><span class='glyphicon glyphicon-pencil glyphicon-button glyphicon-button-alt col-xs-1 trigger-sub' id='edit-"+data+"' data-subtype='edit-tag' data-target='"+data+"' title='Editer l&apos;étiquette'></span>");
		} else {
			$(".tag-input").replaceWith("<h4><span class='label col-xs-12 label-salsabor label-clickable label-addable' id='tag-"+data+"' data-tag='"+data+"'>"+tag_name+"</span></h4>");
		}
	})
}

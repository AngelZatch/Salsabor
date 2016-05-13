$(document).on('focus', '.name-input', function(){
	var id = $(this).attr("id");
	$.get("functions/fetch_user_list.php", {filter : "staff"}).done(function(data){
		var userList = JSON.parse(data);
		var autocompleteList = [];
		for(var i = 0; i < userList.length; i++){
			autocompleteList.push(userList[i].user);
		}
		$("#"+id).textcomplete([{
			match: /(^|\b)(\w{2,})$/,
			search: function(term, callback){
				callback($.map(autocompleteList, function(item){
					return item.toLowerCase().indexOf(term.toLocaleLowerCase()) === 0 ? item : null;
				}));
			},
			replace: function(item){
				return item;
			}
		}]);
	});
}).on('click', '.btn-comment', function(){
	var task_id = document.getElementById($(this).attr("id")).dataset.task;
	var comment = $("#comment-form-"+task_id+">textarea").val();
	var comment_author = $("#name-input-"+task_id).val();
	postComment(comment, comment_author, task_id);
}).on('focus', '#task-target-input', function(e){
	e.stopPropagation();
	var id = $(this).data().user;
	$.get("functions/fetch_targets.php", {user_id : id}).done(function(data){
		console.log(data);
		var targetList = JSON.parse(data);
		var autocompleteList = [];
		for(var i = 0; i < targetList.length; i++){
			autocompleteList.push(targetList[i].name);
		}
		$("#task-target-input").textcomplete([{
			match: /(^|\b)(\w{2,})$/,
			search: function(term, callback){
				callback($.map(autocompleteList, function(item){
					return item.toLowerCase().indexOf(term.toLocaleLowerCase()) === 0 ? item : null;
				}));
			},
			replace: function(item){
				return item;
			}
		}]);
	})
}).on('click', '.post-task', function(){
	var task_title = $(".task-title-input").val();
	var task_description = $(".task-description-input").val();
	var task_token = $("#task-target-input").val();
	if(task_token == ""){
		task_token = "[USR-"+$("#task-target-input").data().user+"]";
	}
	postTask(task_title, task_description, task_token);
}).on('click', '.toggle-task', function(e){
	e.stopPropagation();
	var table_name = "tasks";
	var flag = "task_state";
	var target_id = document.getElementById($(this).attr("id")).dataset.target;

	if($("#task-"+target_id).hasClass("task-new")){
		var value = "1";
	} else {
		var value = "0";
	}

	$.when(updateFlag(table_name, flag, value, target_id)).done(function(){
		$("#task-"+target_id).removeClass("task-new");
		$("#task-"+target_id).removeClass("task-old");
		$("#toggle-task-"+target_id).removeClass("glyphicon-ok-circle");
		$("#toggle-task-"+target_id).removeClass("glyphicon-ok-sign");
		if(value == 1){
			$("#task-"+target_id).addClass("task-old");
			$("#toggle-task-"+target_id).addClass("glyphicon-ok-circle");
			$("#toggle-task-"+target_id).attr("title", "Marquer comme non traitée");
		} else {
			$("#task-"+target_id).addClass("task-new");
			$("#toggle-task-"+target_id).addClass("glyphicon-ok-sign");
			$("#toggle-task-"+target_id).attr("title", "Marquer comme traitée");
		}
	})
}).on('click', '.link-glyphicon', function(e){
	e.stopPropagation();
})

function fetchTasks(user_id, limit){
	$.get("functions/fetch_tasks.php", {user_id : user_id, limit : limit}).done(function(data){
		if(limit == 0 || $(".sub-modal-notification").is(":visible")){
			displayTasks(data, user_id, limit);
		}
	});
}

function fetchComments(task_id){
	$.get("functions/fetch_comments.php", {task_id : task_id}).done(function(data){
		displayComments(task_id, data);
	})
}

function refreshTask(task){
	// Title
	$("#task-title-"+task.id).html(task.title);

	// Description
	$("#task-description-"+task.id).html("<span class='glyphicon glyphicon-align-left'></span> "+task.description);

	// Deadline
	if(task.deadline != null){
		var deadline_class = displayDeadline(moment(task.deadline));
		$("#deadline-"+task.id).removeClass("deadline-near");
		$("#deadline-"+task.id).removeClass("deadline-expired");
		$("#deadline-"+task.id).addClass(deadline_class);
		console.log(deadline_class);
		$("#deadline-"+task.id).html("<span class='glyphicon glyphicon-time'></span> "+moment(task.deadline).format("D MMM [à] H:mm"));
	} else {
		$("#deadline-"+task.id).html("<span class='glyphicon glyphicon-time'></span> Ajouter une date limite");
	}


	// Comments count
	$("#comments-count-"+task.id).html("<span class='glyphicon glyphicon-comment'></span> "+task.message_count);
}

function displayTasks(data, user_id, limit){
	var tasks = JSON.parse(data);
	for(var i = 0; i < tasks.length; i++){
		if($("#task-"+tasks[i].id).length > 0){
			refreshTask(tasks[i]);
		} else {
			if(i == 0){
				if(limit != 0){
					$(".smn-body").empty();
				} else {
					$(".tasks-container").empty();
				}
			}
			// Status handling
			var notifMessage = "", notifClass = "", link = "", linkTitle = "", deadline = moment(tasks[i].deadline);
			if(tasks[i].status == '0'){
				notifClass = "task-new";
			} else {
				notifClass = "task-old";
			}
			notifMessage += "<div id='task-"+tasks[i].id+"' data-task='"+tasks[i].id+"' data-state='"+tasks[i].status+"' class='panel task-line "+notifClass+"'>";
			notifMessage += "<div class='panel-heading panel-heading-task container-fluid' id='ph-task-"+tasks[i].id+"' data-trigger='"+tasks[i].id+"'>";

			notifMessage += "<div class='col-lg-1'>";
			notifMessage += "<div class='notif-pp'>";
			notifMessage += "<image src='"+tasks[i].photo+"'>";
			notifMessage += "</div>";
			notifMessage += "</div>";

			notifMessage += "<div class='col-sm-11'>";
			notifMessage += "<div class='row'>";

			notifMessage += "<p class='task-title col-sm-10' id='task-title-"+tasks[i].id+"'>";

			notifMessage += tasks[i].title;

			// Token handling
			switch(tasks[i].type){
				case "USR":
					linkTitle += "Aller à l&apos;utilisateur";
					break;

				case "PRD":
					linkTitle += "Aller au produit";
					break;

				default:
					break;
			}

			notifMessage += "</p>";

			notifMessage += "<a href='"+tasks[i].link+"' class='link-glyphicon' target='_blank'><span class='glyphicon glyphicon-share-alt col-sm-1 glyphicon-button-alt glyphicon-button-big' title='"+linkTitle+"'></span></a>";
			if(tasks[i].status == 1){
				notifMessage += "<span class='glyphicon glyphicon-ok-circle col-sm-1 glyphicon-button-alt glyphicon-button-big toggle-task' id='toggle-task-"+tasks[i].id+"' data-target='"+tasks[i].id+"' title='Marquer comme non traitée'></span>";
			} else {
				notifMessage += "<span class='glyphicon glyphicon-ok-sign col-sm-1 glyphicon-button-alt glyphicon-button-big toggle-task' id='toggle-task-"+tasks[i].id+"' data-target='"+tasks[i].id+"' title='Marquer comme traitée'></span>";
			}
			notifMessage += "</div>";

			notifMessage += "<div class='container-fluid'>";
			notifMessage += "<p class='task-hour col-sm-12'> créée "+moment(tasks[i].date).format("[le] ll [à] HH:mm")+"</p>";
			notifMessage += "<p id='task-description-"+tasks[i].id+"'><span class='glyphicon glyphicon-align-left'></span> "+tasks[i].description+"</p>";
			notifMessage += "<div class='col-sm-1 comment-span' id='comments-count-"+tasks[i].id+"'>";
			notifMessage += "<span class='glyphicon glyphicon-comment'></span> "+tasks[i].message_count;
			notifMessage += "</div>";

			var deadline_class = displayDeadline(deadline);
			notifMessage += "<div class='col-sm-3 deadline-span "+deadline_class+"' id='deadline-"+tasks[i].id+"'>";
			if(tasks[i].deadline != null){
				notifMessage += "<span class='glyphicon glyphicon-time'></span> "+deadline.format("D MMM [à] HH:mm");
			} else {
				notifMessage += "<span class='glyphicon glyphicon-time'></span> Ajouter une date limite";
			}
			notifMessage += "</div>";

			notifMessage += "</div>";
			notifMessage += "</div>";
			notifMessage += "</div>";

			// Commentaires de la notification
			notifMessage += "<div class='panel-body panel-task-body collapse' id='body-task-"+tasks[i].id+"' data-task='"+tasks[i].id+"'>";
			notifMessage += "<p><span class='glyphicon glyphicon-comment'></span> Commentaires</p>";
			notifMessage += "<div class='comment-unit comment-form' id='comment-form-"+tasks[i].id+"'>";
			notifMessage += "<textarea rows='2' class='form-control' placeholder='&Eacute;crire un commentaire...'></textarea>";
			notifMessage += "<div class='input-group'>";
			notifMessage += "<input class='form-control name-input' id='name-input-"+tasks[i].id+"' type='text' placeholder='Auteur du commentaire'>";
			notifMessage += "<span class='input-group-btn'><button class='btn btn-primary btn-comment' id='comment-task-"+tasks[i].id+"' data-task='"+tasks[i].id+"'>Envoyer</button></span>";
			notifMessage += "</div>";
			notifMessage += "</div>";
			notifMessage += "<div class='task-comments' id='task-comments-"+tasks[i].id+"'></div>";
			notifMessage += "</div>";

			if(limit == 0){
				$(".tasks-container").append(notifMessage);
			} else {
				$(".smn-body").append(notifMessage);
			}
		}
	}
	setTimeout(fetchTasks, 10000, user_id, limit);
}

function displayDeadline(deadline){
	var deadline_class = "";
	if(deadline < moment()){
		deadline_class = "deadline-expired";
	} else if(deadline < moment().add(3, 'days')){
		deadline_class = "deadline-near";
	}
	return deadline_class;
}

function displayComments(task_id, data){
	$("#task-comments-"+task_id).empty();
	var messages = "";
	var message_list = JSON.parse(data);
	for(var i = 0; i < message_list.length; i++){
		messages += "<div class='comment-unit'>";
		messages += "<a href='user/"+message_list[i].author_id+"' class='link-alt message-author'>"+message_list[i].author+"</a>";
		messages += "<div class='message-container'>"+message_list[i].comment+"</div>";
		messages += "<p class='message-details'>"+moment(message_list[i].date).format("[le] ll [à] HH:mm")+"</p>";
		messages += "</div>";
	}
	$("#task-comments-"+task_id).append(messages);
	setTimeout(fetchComments, 10000, task_id);
}

function postComment(comment, author, task_id){
	$.post("functions/post_comment.php", {comment : comment, user_id : author, task_id : task_id}).done(function(e){
		console.log(e);
		$("#comment-form-"+task_id+">textarea").val('');
		$("#name-input-"+task_id).val('');
		fetchComments(task_id);
	})
}

function postTask(title, description, token){
	$.post("functions/post_task.php", {task_title : title, task_description : description, task_token : token}).done(function(message){
		console.log(message);
		$(".panel-new-task").remove();
	})
}

function updateFlag(table, flag, value, target){
	return $.post("functions/update_flag.php", {table : table, flag : flag, value : value, target_id : target});
}

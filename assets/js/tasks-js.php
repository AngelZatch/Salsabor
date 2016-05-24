<?php header("Content-type: application/javascript");?>
	<?php session_start();?>
		// Oh yeah we cheating boys. Basically we need to get $_SESSION variables for comments, so this is an acceptable method.

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
	}).on('click', '.panel-heading-task', function(){
		var id = document.getElementById($(this).attr("id")).dataset.trigger;
		$("#body-task-"+id).collapse("toggle");
	}).on('show.bs.collapse', '.panel-task-body', function(){
		var task_id = document.getElementById($(this).attr("id")).dataset.task;
		fetchComments(task_id);
	}).on('click', '.btn-comment', function(){
		var task_id = document.getElementById($(this).attr("id")).dataset.task;
		var comment = $("#comment-form-"+task_id+">textarea").val();
		var comment_author = <?php echo json_encode($_SESSION["user_id"]);?>;
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
	}).on('click', '.delete-task', function(){
		var task_id = document.getElementById($(this).attr("id")).dataset.task;
		var table = "tasks";
		$(".sub-modal").hide(0);
		console.log(task_id, table);
		$.when(deleteEntry(table, task_id)).done(function(){
			$("#task-"+task_id).remove();
		})
	}).on('click', '.toggle-task', function(){
		var table_name = "tasks";
		var flag = "task_state";
		var target_id = document.getElementById($(this).attr("id")).dataset.target;

		if($("#task-"+target_id).hasClass("task-new")){
			var value = "1";
		} else {
			var value = "0";
		}

		$.when(updateColumn(table_name, flag, value, target_id)).done(function(){
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
			if(top.location.pathname === "/Salsabor/dashboard"){
				$("#task-"+target_id).fadeOut('normal', function(){
					$(this).remove();
				});
			}
		})
	}).on('click', '.glyphicon-button-alt', function(e){
		e.stopPropagation();
	}).on('click', '.task-deadline', function(){
		var deadline = moment($(".datepicker").val(), "DD/MM/YYYY HH:mm").format("YYYY-MM-DD HH:mm");
		var task_id = document.getElementById($(this).attr("id")).dataset.task;
		$(".sub-modal").hide(0);
		$.when(updateColumn("tasks", "task_deadline", deadline, task_id)).done(function(){
			// Deadline
			if(deadline != null){
				var deadline_class = displayDeadline(moment(deadline));
				$("#deadline-"+task_id).removeClass("deadline-near");
				$("#deadline-"+task_id).removeClass("deadline-expired");
				$("#deadline-"+task_id).addClass(deadline_class);
				console.log(deadline_class);
				$("#deadline-"+task_id).html("<span class='glyphicon glyphicon-time'></span> "+moment(deadline).format("D MMM [à] H:mm"));
			} else {
				$("#deadline-"+task_id).html("<span class='glyphicon glyphicon-time'></span> Ajouter une date limite");
			}
		})
	})

	function fetchTasks(user_id, filter, limit){
		$.get("functions/fetch_tasks.php", {user_id : user_id, limit : limit, filter : filter}).done(function(data){
			if(limit == 0 || $(".sub-modal-notification").is(":visible")){
				if(top.location.pathname === "/Salsabor/dashboard"){
					var half = true;
				} else {
					var half = false;
				}
				displayTasks(data, user_id, limit, filter, half);
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
	$("#task-description-"+task.id+":not(.editing)").html(task.description);

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

function displayTasks(data, user_id, limit, filter, half){
	var tasks = JSON.parse(data);
	if(tasks.length == 0){
		$(".tasks-container").css("background-image", "url(assets/images/logotype_white.png)");
	}
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
			var contents = "", notifClass = "", link = "", linkTitle = "", deadline = moment(tasks[i].deadline);
			if(tasks[i].status == '0'){
				notifClass = "task-new";
			} else {
				notifClass = "task-old";
			}
			contents += "<div id='task-"+tasks[i].id+"' data-task='"+tasks[i].id+"' data-state='"+tasks[i].status+"' class='panel task-line "+notifClass+"'>";
			contents += "<div class='panel-heading panel-heading-task container-fluid' id='ph-task-"+tasks[i].id+"' data-trigger='"+tasks[i].id+"'>";

			if(half){
				var image_width = "col-lg-2";
				var contents_width = "col-lg-10";
				var comments_count_width = "col-lg-3";
				var deadline_width = "col-lg-5";
				var recipient_width = "col-lg-4"
				} else {
					var image_width = "col-lg-1";
					var contents_width = "col-lg-11";
					var comments_count_width = "col-lg-2";
					var deadline_width = "col-lg-3";
					var recipient_width = "col-lg-3";
				}
			contents += "<div class='col-sm-2 "+image_width+"'>";
			contents += "<div class='notif-pp'>";
			contents += "<image src='"+tasks[i].photo+"'>";
			contents += "</div>";
			contents += "</div>";

			contents += "<div class='col-sm-10 "+contents_width+"'>";
			contents += "<div class='row'>";

			contents += "<p class='task-title col-sm-9' id='task-title-"+tasks[i].id+"'>";

			contents += tasks[i].title;

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

			contents += "</p>";

			contents += "<a href='"+tasks[i].link+"' class='link-glyphicon' target='_blank'><span class='glyphicon glyphicon-share-alt col-sm-1 glyphicon-button-alt glyphicon-button-big' title='"+linkTitle+"'></span></a>";
			if(tasks[i].status == 1){
				contents += "<span class='glyphicon glyphicon-ok-circle col-sm-1 glyphicon-button-alt glyphicon-button-big toggle-task' id='toggle-task-"+tasks[i].id+"' data-target='"+tasks[i].id+"' title='Marquer comme non traitée'></span>";
			} else {
				contents += "<span class='glyphicon glyphicon-ok-sign col-sm-1 glyphicon-button-alt glyphicon-button-big toggle-task' id='toggle-task-"+tasks[i].id+"' data-target='"+tasks[i].id+"' title='Marquer comme traitée'></span>";
			}
			contents += "<p class='col-sm-1 panel-item-options'><span class='glyphicon glyphicon-trash glyphicon-button-alt glyphicon-button-big trigger-sub' id='delete-task-"+tasks[i].id+"' data-subtype='delete-task' data-target='"+tasks[i].id+"' title='Supprimer la tache'></span></p>";
			contents += "</div>";

			contents += "<div class='container-fluid'>";
			contents += "<p class='task-hour col-sm-12'> créée "+moment(tasks[i].date).format("[le] ll [à] HH:mm")+"</p>";
			contents += "<div><span class='glyphicon glyphicon-align-left glyphicon-description'></span><p class='editable' id='task-description-"+tasks[i].id+"' data-input='textarea' data-table='tasks' data-column='task_description' data-target='"+tasks[i].id+"'>"+tasks[i].description+"</p></div>";
			contents += "<div class='col-md-2 "+comments_count_width+" comment-span' id='comments-count-"+tasks[i].id+"'>";
			contents += "<span class='glyphicon glyphicon-comment'></span> "+tasks[i].message_count;
			contents += "</div>";

			var deadline_class = displayDeadline(deadline);
			contents += "<div class='col-md-5 "+deadline_width+" deadline-span "+deadline_class+" trigger-sub' id='deadline-"+tasks[i].id+"' data-subtype='deadline' data-task='"+tasks[i].id+"'>";
			if(tasks[i].deadline != null){
				contents += "<span class='glyphicon glyphicon-time'></span> "+deadline.format("D MMM [à] HH:mm");
			} else {
				contents += "<span class='glyphicon glyphicon-time'></span> Ajouter une date limite";
			}
			contents += "</div>";

			contents += "<div class='col-md-5 "+recipient_width+" comment-span'>";
			contents += "<span class='glyphicon glyphicon-user glyphicon-description'></span> ";
			contents += "<p class='editable' id='task-recipient-"+tasks[i].id+"' data-input='text' data-table='tasks' data-column='task_recipient' data-target='"+tasks[i].id+"'>"+tasks[i].recipient+"</p>";
			contents += "</div>";

			contents += "</div>";
			contents += "</div>";
			contents += "</div>";

			// Commentaires de la notification
			contents += "<div class='panel-body panel-task-body collapse' id='body-task-"+tasks[i].id+"' data-task='"+tasks[i].id+"'>";
			contents += "<p><span class='glyphicon glyphicon-comment'></span> Commentaires</p>";
			contents += "<div class='comment-unit comment-form' id='comment-form-"+tasks[i].id+"'>";
			contents += "<textarea rows='2' class='form-control' placeholder='&Eacute;crire un commentaire...'></textarea>";
			contents += "<div class='input-group'>";
			contents += "<span class='input-group-btn'><button class='btn btn-primary btn-comment' id='comment-task-"+tasks[i].id+"' data-task='"+tasks[i].id+"'>Envoyer</button></span>";
			contents += "</div>";
			contents += "</div>";
			contents += "<div class='task-comments' id='task-comments-"+tasks[i].id+"'></div>";
			contents += "</div>";

			if(limit == 0){
				$(".tasks-container").append(contents);
			} else {
				$(".smn-body").append(contents);
			}
		}
	}
	setTimeout(fetchTasks, 10000, user_id, filter, limit);
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
		fetchComments(task_id);
	})
}

function postTask(title, description, token){
	$.post("functions/post_task.php", {task_title : title, task_description : description, task_token : token}).done(function(message){
		console.log(message);
		$(".panel-new-task").remove();
	})
}

var addEditUserAction;

$(document).ready(function()
{
	// Initialize elements
	$.initElwoodPopups();
	makeUsersTableEditable();
	$("#saveAccessBtn").hide();
	$("#saveUsersBtn").hide();
	$("#saveUsersBtn").hide();
	$("#accessMessages").hide();
	$("#usersMessages").hide();

	if (currentGroup != "admins")
	{
		$(".accessInput").attr("disabled", "disabled");
		$("#addUserBtn").attr("disabled", "disabled");
	}

	// Register event handlers
	$("#addUserBtn").click(function()
	{
		addEditUserAction = "add";
		addEditUserDlg(false);
	});

	$("#cancelAddEditUserBtn").click(function(e)
	{
		$("#addEditUserPopup").closeElwoodPopup();
	});
	
	$(".accessInput").change(function()
	{
		if (!$("#saveAccessBtn").is(":visible"))
			$("#saveAccessBtn").fadeIn();
	});

	$(".usersInput").change(function()
	{
		if (!$("#saveUsersBtn").is(":visible"))
			$("#saveUsersBtn").fadeIn();
	});

	$("#saveAccessBtn").click(function()
	{
		var params =	{
							handler: "EditAccessMethods",
							parameters:
							{
								httpWan: $("#httpwan:checked").is(":checked") ? 1 : 0,
								httpLan: $("#httplan:checked").is(":checked") ? 1 : 0,
								sshWan: $("#sshwan:checked").is(":checked") ? 1 : 0,
								sshLan: $("#sshlan:checked").is(":checked") ? 1 : 0,
								icmpWan: $("#icmpwan:checked").is(":checked") ? 1 : 0,
								icmpLan: $("#icmplan:checked").is(":checked") ? 1 : 0,
								httpPort: $("#httpport").val(),
								sshPort: $("#sshport").val()
							}
						};

		$.getJSON("ajax/ajaxRequest.php", params, function(response)
		{
			if (response.errors.length > 0)
			{
				$("#accessMessages")
					.css("color", "red")
					.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>")
					.show();
			}
			else
			{
				$("#saveAccessBtn").hide();
				$("#accessMessages")
					.css("color", "green")
					.html("Access settings saved successfully")
					.show()
					.fadeOut(3000);
			}
		});
	});

	$("#saveAddEditUserBtn").click(function()
	{	
		var params =	{
							handler: "AddEditUser",
							parameters:
							{
								action: addEditUserAction,
								username: $("#username").val(),
								passwd: $("#passwd").val(),
								confPasswd: $("#confPasswd").val(),
								groupname: $("#groupSelect").val()
							}
						};
		
		$.getJSON("ajax/ajaxRequest.php", params, function(response)
		{
			if (response.errors.length > 0)
			{
				$("#addEditUserMsgs")
					.css("color", "red")
					.html	(	"The following error(s) occured:" +
								"<ul><li>" + response.errors.join("</li><li>") + "</li><ul>"
							);
			}
			else
			{
				var action = params.parameters.action;
				var username = params.parameters.username;
				var groupname = params.parameters.groupname;
				
				if (action == "add")
				{	
					// add new user to the UI
					$("#usersTable tbody").append	(
														"<tr class='user' id='user-" + username + "'>" +
															"<td class='username-cell'>" + username + "</td>" +
															"<td class='groupname-cell'>" + groupname + "</td>" +
															"<td class='actions-cell'>&nbsp;</td>" +
														"</tr>"
													);

					makeUsersTableEditable();
				}
				else
					// update user group on the UI (in case it changed)
					$("#user-" + username).children(".groupname-cell").html(groupname);

				$("#addEditUserPopup").closeElwoodPopup();

				$("#usersMessages")
					.css("color", "green")
					.html("Changes to users saved successfully")
					.show()
					.fadeOut(3000);
			}
		});
	});

	$("#rmUserYesBtn").click(function()
	{
		var params =	{
							handler: "RemoveUser",
							parameters:
							{
								username: $("#username").val()
							}
						};

		$.getJSON("ajax/ajaxRequest.php", params, function(response)
		{
			$("#deleteUserPopup").closeElwoodPopup();
			
			if (response.errors.length > 0)
			{
				$("#addEditUserMsgs")
					.css("color", "red")
					.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>")
					.show();
			}
			else
			{
				$("#addEditUserPopup").closeElwoodPopup();
				$("#user-" + params.parameters.username).remove();
				$("#usersMessages")
					.css("color", "green")
					.html("Changes to users saved successfully")
					.show()
					.fadeOut(3000);
			}
		});		
	});

	$("#deleteUserBtn").click(function()
	{
		$("#removeUserConfirm").html("Are you sure you want to remove user " + $("#username").val() + "?");
		$("#deleteUserPopup").openElwoodPopup();
	});

	$("#rmUserNoBtn").click(function()
	{
		$("#deleteUserPopup").closeElwoodPopup();
	});
});

function addEditUserDlg(editUser)
{
	$("#addEditUserMsgs").html("");
	$("#username").val(editUser ? editUser : "");
	$("#passwd").val("");
	$("#confPasswd").val("");
	$("#deleteUserBtn").attr("disabled", "disabled");
	$("#groupSelect").attr("disabled", "disabled");

	if (editUser)
	{
		$("#username").attr("disabled", "disabled");

		if (editUser != "admin" && currentGroup == "admins")
			$("#deleteUserBtn").removeAttr("disabled");
	}
	else
		$("#username").removeAttr("disabled");

	if (!editUser || (currentGroup == "admins" && editUser != "admin"))
		$("#groupSelect").removeAttr("disabled");
	
	$("#groupSelect").val(editUser ? $("#user-" + editUser).children(".groupname-cell").html() : "users");
	$("#addEditUserPopup").openElwoodPopup();
}

function makeUsersTableEditable()
{
	$(".user").each(function()
	{
		var actionsCell = $(this).children(".actions-cell");

		if (actionsCell.children("button [id $= -edit]").length == 0)
		{
			var username = $(this).children(".username-cell").html();
			var groupname = $(this).children(".groupname-cell").html();
			
			actionsCell.html("<button type='button' id='" + username + "-edit' type='button' title='Edit settings for " + username + "'>Edit</button");
			$("#" + username + "-edit").click(function()
			{
				addEditUserAction = currentGroup == "admins" ? "edit" : "pw";
				addEditUserDlg(username);
			});

			if (currentUser != username && currentGroup != "admins")
				$("#" + username + "-edit").attr("disabled", "disabled");
		}
	});
}
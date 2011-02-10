<?php
	require_once "Page.class.php";
	require_once "RouterSettings.class.php";
	require_once "Database.class.php";
	require_once "Service.class.php";
	require_once "User.class.php";
	
	class AccessPage implements Page
	{
		// Override
		public function id()
		{
			return "access";
		}
		
		// Override
		public function name()
		{
			return "Access";
		}
		
		// Override
		public function head(array $parameters)
		{
		}
		
		// Override
		public function style(array $parameters)
		{
		}
				
		// Override
		public function javascript(array $parameters)
		{
			$user = User::getUser();
			$group = !empty($user) ? $user->getGroup() : null;
			
			return <<<END
			
			var showSaveAccessButton = function()
			{
				if (!$("#saveAccessBtn").is(":visible"))
				{
					$("#saveAccessBtn")
						.html("Save")
						.removeAttr("disabled")
						.fadeIn();
				}
			}
						
			var addEditUserAction;
			var currentUser = "$user";
			var currentGroup = "$group";

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
	
				$(".accessInput").change(showSaveAccessButton);

				$(".usersInput").change(function()
				{
					if (!$("#saveUsersBtn").is(":visible"))
						$("#saveUsersBtn").fadeIn();
				});
			
				$("#saveAccessBtn").click(function()
				{
					var saveButton = $(this);
					
					saveButton
						.html("Saving...&nbsp;<img src='images/loading.gif' />")
						.attr("disabled", "disabled");
						
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
								
							saveButton
								.html("Save")
								.removeAttr("disabled");
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
								//  add new user to the UI
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
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			$extIf = RouterSettings::getSettingValue("EXTIF");
			$intIf = RouterSettings::getSettingValue("INTIF");
			
			$httpService = Service::getInstance("http");
			$sshService = Service::getInstance("ssh");
			$icmpService = Service::getInstance("icmp");
			$httpService->load();
			$sshService->load();
			$icmpService->load();
			
			foreach ($httpService->getAccessRules() as $rule)
			{
				$httpPort = $rule->getAttribute("dport");
				$lanHttpEnabled = ($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $intIf) ? "checked" : "";
				$wanHttpEnabled = ($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $extIf) ? "checked" : "";
			}
			
			foreach ($sshService->getAccessRules() as $rule)
			{
				$sshPort = $rule->getAttribute("dport");
				$lanSshEnabled = ($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $intIf) ? "checked" : "";
				$wanSshEnabled = ($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $extIf) ? "checked" : "";
			}
			
			foreach ($icmpService->getAccessRules() as $rule)
			{
				$lanIcmpEnabled = ($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $intIf) ? "checked" : "";
				$wanIcmpEnabled = ($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $extIf) ? "checked" : "";
			}
						
			$selectHash = new User();
			$users = $selectHash->executeSelect();
			
			$out = <<<END
			
			<!-- access methods section -->
			<div class="section-header">Access Methods</div>
			<form name="access_method">
			<table class="access-table">
				<tr>
					<th>Access Method</th>
					<th>WAN</th>
					<th>LAN</th>
					<th>Port</th>
				</tr>
				<tr>
					<td>HTTP</td>
					<td>
						<input class="accessInput" type="checkbox" id="httpwan" name="httpwan" value="httpwan" $wanHttpEnabled>
					</td>
					<td>
						<input class="accessInput" type="checkbox" id="httplan"name="httplan" value="httplan" $lanHttpEnabled>
					</td>
					<td>
						<input class="textfield accessInput" id="httpport" name="httpport" size="7" maxlength="5" value="$httpPort">
					</td>
				</tr>
				<tr>
					<td>SSH</td>
					<td>
						<input class="accessInput" type="checkbox" id="sshwan" name="sshwan" value="sshwan" $wanSshEnabled>
					</td>
					<td>
						<input class="accessInput" type="checkbox" id="sshlan" name="sshlan" value="sshlan" $lanSshEnabled>
					</td>
					<td>
						<input class="textfield accessInput" id="sshport" name="sshport" size="7" maxlength="5" value="$sshPort">
					</td>
				</tr>
				<tr>
					<td>ICMP</td>
					<td>
						<input class="accessInput" type="checkbox" id="icmpwan" name="icmpwan" value="icmpwan" $wanIcmpEnabled>
					</td>
					<td>
						<input class="accessInput" type="checkbox" id="icmplan" name="icmplan" value="icmplan" $lanIcmpEnabled>
					</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			<div style="margin-top: 10px;">
				<span style="font-size: 12pt; font-weight: bold; color: red;">WARNING:</span>If no access type is checked, the only access is shell access through the console.
			</div>
			<button id="saveAccessBtn" type="button" style="margin-top: 5px;">Save</button>
			<div id="accessMessages"></div>
			</form>
			<br>

			<!-- users section -->
			<div class="section-header">Users</div>
			<button type="button" id="addUserBtn">Add User</button>
			<br />
			<br />
			<form name="users">
				<table id="usersTable" class="access-table">
					<tr>
						<th>User</th>
						<th>Group</th>
						<th>Actions</th>
					</tr>
END;
			foreach ($users as $user)
			{
				$username = $user->getAttribute("username");

				$out .= <<<END
					
					<tr class="user" id="user-$username">
						<td class="username-cell">$username</td>
						<td class="groupname-cell">{$user->getGroup()}</td>
						<td class="actions-cell">&nbsp;</td>
					</tr>
END;
			}
			
			return $out .= <<<END
			
				</table>
			</form>
			<button type="button" id="saveUsersBtn" style="margin-top: 5px;">Save</button>
			<br />
			<div id="usersMessages"></div>
END;
		}
		
		// Override
		public function popups(array $parameters)
		{
			return <<<END
			
			<div id="addEditUserPopup" class="elwoodPopup">
				<div id="addEditUserMsgs"></div>
				<table>
					<tr>
						<td class="tabInputLabel">Username:</td>
						<td class="tabInputValue">
							<input type="text" id="username" name="username" size="20" maxlength="32" />
						</td>
					</tr>
					<tr>
						<td class="tabInputLabel">Password:</td>
						<td class="tabInputValue">
							<input type="password" id="passwd" name="passwd" size="20" maxlength="32" />
						</td>
					</tr>
					<tr>
						<td class="tabInputLabel">Confirm Password:</td>
						<td class="tabInputValue">
							<input type="password" id="confPasswd" name="confPasswd" size="20" maxlength="32" />
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td class="tabInputLabel">Group</td>
						<td class="tabInputValue">
							<select id="groupSelect">
								<option value="admins">admins</option>
								<option value="users">users</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2">
							<button type="button" id="saveAddEditUserBtn">Save</button>
							<button type="button" id="cancelAddEditUserBtn">Cancel</button>
							&nbsp;&nbsp;
							<button type="button" id="deleteUserBtn">Delete</button>
						</td>
					</tr>
				</table>
			</div>	

			<div id="deleteUserPopup" class="elwoodPopup">
				<div id="removeUserConfirm"></div>
				<br />
				<button type="button" id="rmUserYesBtn">Yes</button>
				<button type="button" id="rmUserNoBtn">No</button>
			</div>
END;
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>
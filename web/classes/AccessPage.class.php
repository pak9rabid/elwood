<?php
	require_once "Page.class.php";
	require_once "NetworkInterface.class.php";
	require_once "Service.class.php";
	require_once "User.class.php";
	require_once "CheckBox.class.php";
	require_once "TextField.class.php";
	require_once "Button.class.php";
	require_once "SaveButton.class.php";
	require_once "ComboBox.class.php";
	
	class AccessPage extends Page
	{
		private $users = array();
		private $currentUser;
		
		public function __construct()
		{
			$this->currentUser = User::getUser();
			
			$selectHash = new User();
			$this->users = $selectHash->executeSelect();
			
			$this->addElement(new CheckBox("httpwan"));
			$this->addElement(new CheckBox("httplan"));
			$this->addElement(new CheckBox("sshwan"));
			$this->addElement(new CheckBox("sshlan"));
			$this->addElement(new CheckBox("icmpwan"));
			$this->addElement(new CheckBox("icmplan"));
			$this->addElement(new TextField("httpport"));
			$this->addElement(new TextField("sshport"));
			$this->addElement(new SaveButton("saveAccessBtn", "Save"));
			
			$this->addElement(new Button("addUserBtn", "Add User"));
			
			$this->addElement(new TextField("username"));
			$this->addElement(new TextField("passwd"));
			$this->addElement(new TextField("confPasswd"));
			$this->addElement(new ComboBox("groupSelect", array("admins" => "admins", "users" => "users")));
			$this->addElement(new Button("saveAddEditUserBtn", "Save"));
			$this->addElement(new Button("cancelAddEditUserBtn", "Cancel"));
			$this->addElement(new Button("deleteUserBtn", "Delete"));
			$this->addElement(new Button("rmUserYesBtn", "Yes"));
			$this->addElement(new Button("rmUserNoBtn", "No"));
			
			foreach ($this->users as $user)
			{
				$username = $user->getAttribute("username");
				$editUserButton = new Button("{$username}-edit", "Edit");
				$editUserButton->addClass("editUserButton");
				$editUserButton->setAttribute("title", "Edit settings for $username");
				$editUserButton->addHandler("click", "editUser");
				
				if ($this->currentUser != null && $this->currentUser->getGroup() != "admins" && $this->currentUser != $user)
					$editUserButton->setAttribute("disabled", "disabled");
				
				$this->addElement($editUserButton);
			}
			
			$this->getElement("httpport")->setAttribute("size", "7");
			$this->getElement("httpport")->setAttribute("maxlength", "5");
			$this->getElement("sshport")->setAttribute("size", "7");
			$this->getElement("sshport")->setAttribute("maxlength", "5");
			$this->getElement("httpwan")->addClasses(array("accessInput", "adminInput"));
			$this->getElement("httplan")->addClasses(array("accessInput", "adminInput"));
			$this->getElement("sshwan")->addClasses(array("accessInput", "adminInput"));
			$this->getElement("sshlan")->addClasses(array("accessInput", "adminInput"));
			$this->getElement("icmpwan")->addClasses(array("accessInput", "adminInput"));
			$this->getElement("icmplan")->addClasses(array("accessInput", "adminInput"));
			$this->getElement("httpport")->addClasses(array("accessInput", "adminInput"));
			$this->getElement("sshport")->addClasses(array("accessInput", "adminInput"));
			
			$this->getElement("saveAccessBtn")->addStyle("display", "none");
			$this->getElement("saveAccessBtn")->addHandler("click", "saveAccessSettings");
			$this->getElement("saveAccessBtn")->addClass("adminInput");
			$this->getElement("addUserBtn")->addHandler("click", "addUser");
			$this->getElement("addUserBtn")->addClass("adminInput");
			
			$this->getElement("groupSelect")->addClass("adminInput");
			$this->getElement("saveAddEditUserBtn")->addHandler("click", "applyUserChange");
			$this->getElement("cancelAddEditUserBtn")->addHandler("click", "function(){\$('#addEditUserPopup').closeElwoodPopup();}");
			$this->getElement("deleteUserBtn")->addHandler("click", "deleteUserConfirm");
			$this->getElement("deleteUserBtn")->addClass("adminInput");
			$this->getElement("rmUserYesBtn")->addHandler("click", "deleteUser");
			$this->getElement("rmUserNoBtn")->addHandler("click", "function(){\$('#deleteUserPopup').closeElwoodPopup();}");
			
			$extIf = NetworkInterface::getInstance("WAN")->getPhysicalInterface();
			$intIf = NetworkInterface::getInstance("LAN")->getPhysicalInterface();
			
			$httpService = Service::getInstance("http");
			$sshService = Service::getInstance("ssh");
			$icmpService = Service::getInstance("icmp");
			$httpService->load();
			$sshService->load();
			$icmpService->load();
			
			foreach ($httpService->getAccessRules() as $rule)
			{
				$this->getElement("httpport")->setValue($rule->getAttribute("dport"));
				$this->getElement("httplan")->setSelected($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $intIf);
				$this->getElement("httpwan")->setSelected($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $extIf);
			}
			
			foreach ($sshService->getAccessRules() as $rule)
			{
				$this->getElement("sshport")->setValue($rule->getAttribute("dport"));
				$this->getElement("sshlan")->setSelected($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $intIf);
				$this->getElement("sshwan")->setSelected($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $extIf);
			}
			
			foreach ($icmpService->getAccessRules() as $rule)
			{
				$this->getElement("icmplan")->setSelected($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $intIf);
				$this->getElement("icmpwan")->setSelected($rule->getAttribute("int_in") == null || $rule->getAttribute("int_in") == $extIf);
			}
			
			$this->getElement("passwd")->setAttribute("type", "password");
			$this->getElement("confPasswd")->setAttribute("type", "password");
			
			foreach ($this->getElements() as $element)
			{
				if ($this->currentUser != null && $this->currentUser->getGroup() != "admins" && in_array("adminInput", $element->getClasses()))
					$element->setAttribute("disabled", "disabled");
			}
		}
		
		// Override
		public function id()
		{
			return "Access";
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
			return parent::javascript($parameters) . <<<END
			
			var addEditUserAction;
			var currentUser = "{$this->currentUser}";
			var currentGroup = "{$this->currentUser->getGroup()}";
			
			$(function()
			{
				$.initElwoodPopups();
				$(".accessInput").change(showSaveAccessButton);
			});
			
			function editUser()
			{
				var username = $(this).attr("id").replace(/-edit/, "");
				addEditUserAction = currentGroup == "admins" ? "edit" : "pw";
				addEditUserDlg(username);
			}
			
			function saveAccessSettings()
			{
				var saveButton = $("#saveAccessBtn");
				
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
						saveButton.hide();
						$("#accessMessages")
							.css("color", "green")
							.html("Access settings saved successfully")
							.show()
							.fadeOut(3000);
					}
				});
			}
			
			function addUser()
			{
				addEditUserAction = "add";
				addEditUserDlg(false);
			}
			
			function applyUserChange()
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
							.html	(
										"The following error(s) occured:" +
										"<ul><li>" + response.errors.join("</li><li>") + "</li><ul>"
									);
					}
					else
					{
						var action = params.parameters.action;
						var username = params.parameters.username;
						var groupname = params.parameters.groupname;
						var editButtonName = username + "-edit";
						
						if (action == "add")
						{
							// add new user to the UI
							$("#usersTable tbody").append	(
																"<tr class='user' id='user-" + username + "'>" +
																	"<td class='username-cell'>" + username + "</td>" +
																	"<td class='groupname-cell'>" + groupname + "</td>" +
																	"<td class='actions-cell'>" + "{$this->getElement("admin-edit")->cloneElementContent()}".replace(/@@@CLONED_ELEMENT@@@/g, editButtonName) + "</td>" +
																"</tr>"
															);
															
							$("#" + editButtonName).click(editUser);
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
			}
			
			function deleteUserConfirm()
			{
				$("#removeUserConfirm").html("Are you sure you want to remove user " + $("#username").val() + "?");
				$("#deleteUserPopup").openElwoodPopup();
			}
			
			function deleteUser()
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
			}
			
			function showSaveAccessButton()
			{
				if (!$("#saveAccessBtn").is(":visible"))
				{
					$("#saveAccessBtn")
						.html("Save")
						.removeAttr("disabled")
						.fadeIn();
				}
			}
			
			function addEditUserDlg(editUser)
			{
				$("#addEditUserMsgs").html("");
				$("#username").val(editUser ? editUser : "");
				$("#passwd").val("");
				$("#confPasswd").val("");
				
				if (currentGroup == "admins")
					$("#groupSelect").removeAttr("disabled");
				
				if (editUser)
				{
					$("#username").attr("disabled", "disabled");
					
					if (editUser != "admin" && currentGroup == "admins")
						$("#deleteUserBtn").removeAttr("disabled");
					else
					{
						$("#groupSelect").attr("disabled", "disabled");
						$("#deleteUserBtn").attr("disabled", "disabled");
					}
				}
				else
				{
					$("#deleteUserBtn").attr("disabled", "disabled");
					$("#username").removeAttr("disabled");
				}
										
				$("#groupSelect").val(editUser ? $("#user-" + editUser).children(".groupname-cell").html() : "users");
				$("#addEditUserPopup").openElwoodPopup();
			}
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			$userRows = "";
			
			foreach ($this->users as $user)
			{
				$editButton = $user . "-edit";				
				$userRows .=	"<tr class=\"user\" id=\"user-{$user}\">" .
									"<td class=\"username-cell\">$user</td>" .
									"<td class=\"groupname-cell\">" . $user->getGroup() . "</td>" .
									"<td class=\"actions-cell\">" . $this->getElement($editButton) . "</td>" .
								"</tr>";
			}
			
			return <<<END
			
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
						{$this->getElement("httpwan")}
					</td>
					<td>
						{$this->getElement("httplan")}
					</td>
					<td>
						{$this->getElement("httpport")}
					</td>
				</tr>
				<tr>
					<td>SSH</td>
					<td>
						{$this->getElement("sshwan")}
					</td>
					<td>
						{$this->getElement("sshlan")}
					</td>
					<td>
						{$this->getElement("sshport")}
					</td>
				</tr>
				<tr>
					<td>ICMP</td>
					<td>
						{$this->getElement("icmpwan")}
					</td>
					<td>
						{$this->getElement("icmplan")}
					</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			<div style="margin-top: 10px;">
				<span style="font-size: 12pt; font-weight: bold; color: red;">WARNING:</span>If no access type is checked, the only access is shell access through the console.
			</div>
			{$this->getElement("saveAccessBtn")}
			<div id="accessMessages"></div>
			</form>
			<br>

			<!-- users section -->
			<div class="section-header">Users</div>
			{$this->getElement("addUserBtn")}
			<br>
			<br>
			<form name="users">
				<table id="usersTable" class="access-table">
					<tr>
						<th>User</th>
						<th>Group</th>
						<th>Actions</th>
					</tr>
					$userRows
				</table>
			</form>
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
							{$this->getElement("username")}
						</td>
					</tr>
					<tr>
						<td class="tabInputLabel">Password:</td>
						<td class="tabInputValue">
							{$this->getElement("passwd")}
						</td>
					</tr>
					<tr>
						<td class="tabInputLabel">Confirm Password:</td>
						<td class="tabInputValue">
							{$this->getElement("confPasswd")}
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td class="tabInputLabel">Group</td>
						<td class="tabInputValue">
							{$this->getElement("groupSelect")}
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2">
							{$this->getElement("saveAddEditUserBtn")}
							{$this->getElement("cancelAddEditUserBtn")}
							&nbsp;&nbsp;
							{$this->getElement("deleteUserBtn")}
						</td>
					</tr>
				</table>
			</div>	

			<div id="deleteUserPopup" class="elwoodPopup">
				<div id="removeUserConfirm"></div>
				<br />
				{$this->getElement("rmUserYesBtn")}
				{$this->getElement("rmUserNoBtn")}
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
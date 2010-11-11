<?php
	require_once "accessControl.php";

	require_once "RouterSettings.class.php";
	require_once "PageElements.class.php";
	require_once "User.class.php";
	require_once "TempDatabase.class.php";

	$currentUser = User::getUser();
	$userGroup = !empty($currentUser) ? $currentUser->getGroup() : null;

	$httpPort = RouterSettings::getSettingValue("HTTP_PORT");
	$sshPort = RouterSettings::getSettingValue("SSH_PORT");

	$lanHttpEnabled = RouterSettings::getSettingValue("LAN_HTTP_ENABLED");
	$wanHttpEnabled = RouterSettings::getSettingValue("WAN_HTTP_ENABLED");
	$lanSshEnabled = RouterSettings::getSettingValue("LAN_SSH_ENABLED");
	$wanSshEnabled = RouterSettings::getSettingValue("WAN_SSH_ENABLED");
	$lanIcmpEnabled = RouterSettings::getSettingValue("LAN_ICMP_ENABLED");
	$wanIcmpEnabled = RouterSettings::getSettingValue("WAN_ICMP_ENABLED");

	$users = Database::executeSelect(new User());
?>

<html>
<head>
	<title>Remote Access Control</title>
	<link rel="StyleSheet" type="text/css" href="css/routerstyle.css">
	<link rel="StyleSheet" type="text/css" href="css/elwoodpopup.css">
	<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="js/jquery.elwoodpopup.js" type="text/javascript"></script>
	<script src="js/access.js.php" type="text/javascript"></script>
</head>

<body>
	<div id="container">
		<?=PageElements::titleOut("Access")?>
		<?=PageElements::navigationOut()?>
		
		<div id="content">
		
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
						<input class="accessInput" type="checkbox" id="httpwan" name="httpwan" value="httpwan" <?=$wanHttpEnabled ? "checked" : ""?>>
					</td>
					<td>
						<input class="accessInput" type="checkbox" id="httplan"name="httplan" value="httplan" <?=$lanHttpEnabled ? "checked" : ""?>>
					</td>
					<td>
						<input class="textfield accessInput" id="httpport" name="httpport" size="7" maxlength="5" value="<?=$httpPort?>">
					</td>
				</tr>
				<tr>
					<td>SSH</td>
					<td>
						<input class="accessInput" type="checkbox" id="sshwan" name="sshwan" value="sshwan" <?=$wanSshEnabled ? "checked" : ""?>>
					</td>
					<td>
						<input class="accessInput" type="checkbox" id="sshlan" name="sshlan" value="sshlan" <?=$lanSshEnabled ? "checked" : ""?>>
					</td>
					<td>
						<input class="textfield accessInput" id="sshport" name="sshport" size="7" maxlength="5" value="<?=$sshPort?>">
					</td>
				</tr>
				<tr>
					<td>ICMP</td>
					<td>
						<input class="accessInput" type="checkbox" id="icmpwan" name="icmpwan" value="icmpwan" <?=$wanIcmpEnabled ? "checked" : ""?>>
					</td>
					<td>
						<input class="accessInput" type="checkbox" id="icmplan" name="icmplan" value="icmplan" <?=$lanIcmpEnabled ? "checked" : ""?>>
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
				<?php
					foreach ($users as $user)
					{
						$username = $user->getAttribute("username");

						echo	"<tr class='user' id='user-$username'>" .
									"<td class='username-cell'>" . $username . "</td>" .
									"<td class='groupname-cell'>" . $user->getGroup() . "</td>" .
									"<td class='actions-cell'>&nbsp;</td>" .
								"</tr>";
					}
				?>
			</table>
			</form>
			<button type="button" id="saveUsersBtn" style="margin-top: 5px;">Save</button>
			<br />
			<div id="usersMessages"></div>
		</div>
	</div>

	<!-- Popups -->
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
</body>
</html>

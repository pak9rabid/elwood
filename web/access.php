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
	<link rel="StyleSheet" type="text/css" href="routerstyle.css">
	<script src="inc/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="inc/access.js" type="text/javascript"></script>
	<script type="text/javascript">
$(document).ready(function()
{
	// Initialize elements
	$("#saveAccessBtn").hide();
	$("#saveUsersBtn").hide();
	$("#messages").hide();

	// Register event handlers
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
		var $params =	{
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

		$.getJSON("ajax/ajaxRequest.php", $params, function(response)
		{
			if (response.errors.length > 0)
			{
				$("#messages")
					.css("color", "red")
					.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>")
					.show();
			}
			else
			{
				$("#saveAccessBtn").hide();
				$("#messages")
					.css("color", "green")
					.html("Access settings saved successfully")
					.show()
					.fadeOut(3000);
			}
		});
	});
});
	</script>
</head>

<body>
	<div id="container">
		<?=PageElements::titleOut("Access")?>
		<?=PageElements::navigationOut()?>
		<div id="content">
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
						<td><input class="accessInput" type="checkbox" id="httpwan" name="httpwan" value="httpwan" <?=$wanHttpEnabled ? "checked" : ""?>></td>
						<td><input class="accessInput" type="checkbox" id="httplan" name="httplan" value="httplan" <?=$lanHttpEnabled ? "checked" : ""?>></td>
						<td><input class="textfield accessInput" id="httpport" name="httpport" size="7" maxlength="5" value="<?=$httpPort?>"></td>
					</tr>
					<tr>
						<td>SSH</td>
						<td><input class="accessInput" type="checkbox" id="sshwan" name="sshwan" value="sshwan" <?=$wanSshEnabled ? "checked" : ""?>></td>
						<td><input class="accessInput" type="checkbox" id="sshlan" name="sshlan" value="sshlan" <?=$lanSshEnabled ? "checked" : ""?>></td>
						<td><input class="textfield accessInput" id="sshport" name="sshport" size="7" maxlength="5" value="<?=$sshPort?>"></td>
					</tr>
					<tr>
						<td>ICMP</td>
						<td><input class="accessInput" type="checkbox" id="icmpwan" name="icmpwan" value="icmpwan" <?=$wanIcmpEnabled ? "checked" : ""?>></td>
						<td><input class="accessInput" type="checkbox" id="icmplan" name="icmplan" value="icmplan" <?=$lanIcmpEnabled ? "checked" : ""?>></td>
						<td>&nbsp;</td>
					</tr>
				</table>
				<div style="margin-top: 10px;">
					<span style="font-size: 12pt; font-weight: bold; color: red;">WARNING:</span>
					If no access type is checked, the only access is shell access through the console.
				</div>
				<button id="saveAccessBtn" type="button" style="margin-top: 5px;">Save</button>
				<div id="messages"></div>
			</form>
			<br>
			<div class="section-header">Users</div>
			<form name="users">
				<table class="access-table">
					<tr>
						<th>User</th>
						<th>Group</th>
						<th>Actions</th>
					</tr>
<?php
	foreach ($users as $user)
	{		
		$username = $user->getAttribute("username");
		$passwordButton = "<button id='$username-passwd' type='button' title='Change password for $username'>Password</button>";
		$deleteButton = "<button id='$username-delete' type='button' title='Remove user $username'>Delete</button>";
		$selectBox = writeGroupSelect($user);
		
		if ($username == "admin")
			echo "<tr><td>$username</td><td>admins</td><td>$passwordButton</td></tr>";
		else
			echo "<tr><td>$username</td><td>$selectBox</td><td>$passwordButton&nbsp;$deleteButton</td></tr>";
	}
?>
				</table>
		</div>
	</div>
</body>
</html>

<?php
	##########################
	####### Functions ########
	##########################

	function writeGroupSelect(User $user)
	{	
		$username = $user->getAttribute("username");
		
		$selectBoxName = $user . "-gs";
		$output = "<select name='$selectBoxName'>"; 
                         
		if ($user->getGroup() == "admins")
		{
			$output .=	"<option value='admins' selected>admins" .
						"<option value='users'>users";
		}
		else
		{
			$output .=	"<option value='admins'>admins" .
						"<option value='users' selected>users";
		}

		$output .= "</select>";

		return $output;
	}
?>
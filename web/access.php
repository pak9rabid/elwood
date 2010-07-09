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
	<script language="JavaScript" src="inc/access.js" type="text/javascript"></script>
</head>

<body>
	<div id="container">
		<?=PageElements::titleOut("Access")?>
		<?=PageElements::navigationOut()?>
		<div id="content">
			<font size="5"><b><u>General Access</u></b></font>
			<br><br>
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
						<td><input type="checkbox" name="httpwan" value="1" <?=$wanHttpEnabled ? "checked" : ""?>></td>
						<td><input type="checkbox" name="httplan" value="1" <?=$lanHttpEnabled ? "checked" : ""?>></td>
						<td><input class="textfield" name="httpport" size="7" maxlength="5" value="<?=$httpPort?>"></td>
					</tr>
					<tr>
						<td>SSH</td>
						<td><input type="checkbox" name="sshwan" value="1" <?=$wanSshEnabled ? "checked" : ""?>></td>
						<td><input type="checkbox" name="sshlan" value="1" <?=$lanSshEnabled ? "checked" : ""?>></td>
						<td><input class="textfield" name="sshport" size="7" maxlength="5" value="<?=$sshPort?>"></td>
					</tr>
					<tr>
						<td>ICMP</td>
						<td><input type="checkbox" name="icmpwan" value="1" <?=$wanIcmpEnabled ? "checked" : ""?>></td>
						<td><input type="checkbox" name="icmplan" value="1" <?=$lanIcmpEnabled ? "checked" : ""?>></td>
						<td>&nbsp;</td>
					</tr>
				</table>
				<br>
				<input name="submit" type="submit" value="Change">&nbsp
				<input type="reset">
				<br>
				<font color="red" size="4"><u>WARNING:</u></font> If no access type is checked, the only access is shell access through the console.
			</form>
			<hr>
			<font size="5"><b><u>Web Users</u></b></font>
			<br><br>
			<form name="users">
				<table class="access-table">
					<tr>
						<th>Users</th>
						<th>Group</th>
						<th>Remove</th>
					</tr>
<?php
	foreach ($users as $user)
	{
		$username = $user->getAttribute("username");
		
		$selectBox = writeGroupSelect($user);
		
		if ($username == "admin")
			echo "<tr><td>$username</td><td>admins</td><td>&nbsp</td></tr>";
		else
		{
			$userVal = $username . "-rm";
			echo "<tr><td>$username</td><td>$selectBox</td><td><input type='checkbox' name='$userVal', value='1'></td></tr>";
		}
	}
?>
				</table>
				<br>
<?php
	if ($userGroup == "admins")
		echo "<b>Add User/Change Password</b>";
	else
		echo "<b>Change Password</b>";

	echo "<br>";

	# Check for errors and print appropriate error message
	if ($_GET['error'] == "pass1")
	{
		echo "<br>";
		echo "<font class='error-font'><b>Error: No password entered</b></font>";
	}
	else if ($_GET['error'] == "pass2")
	{
		echo "<br>";
		echo "<font class='error-font'><b>Error: Password does not match confirmed password</b></font>";
	}
	else if ($_GET['error'] == "username")
	{
		echo "<br>";
		echo "<font class='error-font'><b>Error: Username entered is not allowed</b></font>";
	}

echo <<<END
				<br>
				<table class="status-table">
END;

	if ($userGroup == "admins")
	{
echo <<<END
					<tr>
						<th>Username:</th>
						<td><input class="textfield" name="username" length="10" maxlength="20"></td>
					</tr>
					<tr>
						<th>Password:</th>
						<td><input class="textfield" name="password" type="password" length="10" maxlength="20"></td>
					</tr>
					<tr>
						<th>Confirm Password:</th>
						<td><input class="textfield" name="confirm" type="password" length="10" maxlength="20"></td>
					</tr>
END;
	}
	else
	{
echo <<<END
					<input type="hidden" name="username" value="$currentUser">
					<tr>
						<th>New Password:</th>
						<td><input class="textfield" name="password" type="password" length="10" maxlength="20"></td>
					</tr>
					<tr>
						<th>Confirm Password:</th>
						<td><input class="textfield" name="confirm" type="password" length="10" maxlength="20"></td>
					</tr>
END;
	}
echo <<<END
				</table>
				<br>
				<input name="submit" type="submit" value="Change">&nbsp<input name="reset" type="reset">
			</form>
END;
?>
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
		$user->setGroup();
		
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
<?php
	require_once "Database.class.php";
	require_once "DbQueryPreper.class.php";
	require_once "SessionUtils.class.php";
	require_once "User.class.php";
	
	session_start();
	
	if (isset($_REQUEST['logout']))
	{
		SessionUtils::logout();
		header("Location: ../status.php");
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<?php
	$user = SessionUtils::getUser();
	
	if (!isset($user))
	{
		if (empty($user) && !isset($_POST['username']))
		{?>
			<html>
			<head>
			<title>Login Page</title>
			</head>
		
			<body>
			<h1>Login Required</h1>
		
			<form method="post" action="<?=$_SERVER['PHP_SELF']?>">
			<table>
				<tr>
					<td>Username:</td>
					<td><input type="text" name="username" size="8" /></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" name="password" size="8" /></td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" value="Login" /></td>
				</tr>
			</table>
			</form>
		
			</body>
			</html>
			<?exit;
		}
	
		$username = $_POST['username'];
		$password = $_POST['password'];

		$userQuery = new User();
		$userQuery->setAttribute("username", $username);
		$userQuery->setAttribute("passwd", sha1($password));
		$userMatch = $userQuery->executeSelect();
	
		if (empty($userMatch))
		{
			unset($_SESSION['user']);
			?>
			<html>
			<head>
				<title>Access Denied</title>
			</head>
	
			<body>
				Invalid username or password.  <a href="<?=$_SERVER['PHP_SELF']?>">Click here</a> to try again.
			</body>
			</html>
			<?exit;
		}
		
		$user = $userMatch[0];
		$_SESSION['user'] = serialize($user);
	}
?>
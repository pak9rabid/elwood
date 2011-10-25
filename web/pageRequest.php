<?php
	require_once "classes/Page.class.php";
	require_once "classes/DefaultPage.class.php";
	require_once "classes/PageSections.class.php";
	require_once "classes/LoginPage.class.php";
	require_once "classes/RouterSettings.class.php";
	
	session_start();
	
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : null;
	
	if (empty($page))
		$pageClass = "DefaultPage";
	else if ($page != "Login" && !RouterSettings::getSettingValue("IS_INITIALIZED"))
		$pageClass = "SetupPage";
	else
		$pageClass = $page . "Page";
		
	@include_once "classes/$pageClass.class.php";
	
	if (!class_exists($pageClass))
		$pageClass = "DefaultPage";
		
	$pageObj = new $pageClass($_REQUEST);
	
	if (!($pageObj instanceof Page))
		$pageObj = new DefaultPage();
				
	if ($pageObj->isRestricted() && User::getUser() == null)
		$pageObj = new LoginPage();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
	<title><?=$pageObj->name()?></title>
	<link rel="stylesheet" type="text/css" href="css/routerstyle.css">
	<link rel="stylesheet" type="text/css" href="css/elwoodpopup.css">
	<script src="js/json2.min.js" type="text/javascript"></script>
	<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="js/jquery.elwoodpopup.js" type="text/javascript"></script>
	<?=$pageObj->head($_REQUEST)?>
	<style type="text/css">
		<?=$pageObj->style($_REQUEST)?>
	</style>
	<script type="text/javascript">
	<?=$pageObj->javascript($_REQUEST)?>
	</script>
</head>

<body>
	<div id="container">
		<div id="title">
			<?=PageSections::title($pageObj->name())?>
		</div>
		
		<div id="navigation">
			<?=PageSections::navigation($pageObj)?>
		</div>
		
		<div id="content">
			<?=$pageObj->content($_REQUEST)?>
			<div id="messages">
			</div>
		</div>
		<?=$pageObj->popups($_REQUEST)?>
	</div>
</body>
</html>
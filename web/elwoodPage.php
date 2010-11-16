<?php
	require_once "Page.class.php";
	require_once "DefaultPage.class.php";
	require_once "PageElements.class.php";
	
	$page = $_REQUEST['page'];
	
	if (empty($page))
		$pageClass = "DefaultPage";
	else
		$pageClass = $page . "Page";
		
	@include_once "$pageClass.class.php";
	
	if (!class_exists($pageClass))
		$pageClass = "DefaultPage";
		
	$pageObj = new $pageClass();
	
	if (!($pageObj instanceof Page))
		$pageObj = new DefaultPage();
		
	if ($pageObj->isRestricted())
		require_once "accessControl.php";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
	<title><?=$pageObj->name()?></title>
	<link rel="StyleSheet" type="text/css" href="css/routerstyle.css" />
	<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<?=$pageObj->head()?>
</head>

<body>
	<div id="container">
		<?=PageElements::title($pageObj->name())?>
		<?=PageElements::navigation()?>
		<div id="content">
			<?=$pageObj->content()?>
		</div>
		
		<?=$pageObj->popups()?>
	</div>
</body>
</html>
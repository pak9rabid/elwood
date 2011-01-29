<?php
	require_once "Page.class.php";
	require_once "DefaultPage.class.php";
	require_once "PageSections.class.php";
	
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
		require_once "inc/accessControl.php";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
	<title><?=$pageObj->name()?></title>
	<link rel="StyleSheet" type="text/css" href="css/routerstyle.css" />
	<link rel="StyleSheet" type="text/css" href="css/elwoodpopup.css" />
	<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="js/jquery.elwoodpopup.js" type="text/javascript"></script>
	<?=$pageObj->head()?>
	<style>
		<?=$pageObj->style()?>
	</style>
	<script type="text/javascript">
	<?=$pageObj->javascript()?>
	</script>
</head>

<body id="<?=$pageObj->id()?>">
	<div id="container">
		<div id="title">
			<?=PageSections::title($pageObj->name())?>
		</div>
		
		<div id="navigation">
			<?=PageSections::navigation($pageObj)?>
		</div>
		
		<div id="content">
			<?=$pageObj->content($_REQUEST)?>
		</div>
		<?=$pageObj->popups($_REQUEST)?>
	</div>
</body>
</html>
<?php
	session_start();
	header("Content-Type: application/json");
	
	if (!isset($_SESSION['user']))
	{
		echo json_encode(false);
		exit;
	}
?>
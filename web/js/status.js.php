<?php 
	require_once "RouterStats.class.php";
	header("Content-type: text/javascript");
?>

$(document).ready(function()
{	
	$("#uptime").countdown	(	{
									since: -<?=RouterStats::getUptime()?>,
									format: "dHMS",
									layout: "{d<}{dn} days and {d>}{hn}:{mnn}:{snn}"
								}
							);
});
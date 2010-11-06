<?php 
	require_once "NetworkInterface.class.php";
	header("Content-type: text/javascript");
?>

$(document).ready(function()
{
	// event handlers
	$(".wanInput").change(function()
	{
		if (!$("#saveWanSettingsBtn").is(":visible"))
		{
			$("#saveWanSettingsBtn")
				.html("Save")
				.removeAttr("disabled")
				.fadeIn();
		}
	});

	$("#ipTypeDhcp").click(function()
	{
		$(".staticIpSetting").each(function()
		{
			$(this).attr("disabled", "disabled");
		});

		$("#dnsTypeDhcp").removeAttr("disabled");
	});

	$("#ipTypeStatic").click(function()
	{
		$(".staticIpSetting").removeAttr("disabled");
		$("#dnsTypeDhcp").attr("disabled", "disabled");
		$("#dnsTypeStatic").click();
	});

	$("#dnsTypeDhcp").click(function()
	{
		$(".nameserverInput").each(function()
		{
			$(this).attr("disabled", "disabled");
		});

		$("#addNsBtn").attr("disabled", "disabled");
	});

	$("#dnsTypeStatic").click(function()
	{
		$(".nameserverInput").removeAttr("disabled");
		$("#addNsBtn").removeAttr("disabled");
	});

	$("#addNsBtn").click(function()
	{
		var i = $(".nameserverInput").length + 1;
		
		$("#dns-table > tbody").append	(
											"<tr>" +
												"<td>&nbsp;</td>" +
												"<td align='right'>Nameserver " + i + ":</td>" +
												"<td><input class='wanInput textfield nameserverInput' id='nameserver" + i + "' name='nameserver" + i + "' /></td>" +
											"</tr>"
										);
	});

	$("#saveWanSettingsBtn").click(function()
	{
		var saveButton = $(this);
		
		saveButton
			.html("Please wait...")
			.attr("disabled", "disabled");
			
		var nameservers = new Array();
		
		$(".nameserverInput").each(function()
		{
			var nameserver = $(this).val().replace(/^\s+|\s+$/g, "");
			
			if (nameserver.length > 0)
				nameservers.push(nameserver);
		});
		
		var params =	{
							handler: "SaveWanSettings",
							parameters:
							{
								ipType: $("#ipTypeDhcp").is(":checked") ? "dhcp" : "static",
								dnsType: $("#dnsTypeDhcp").is(":checked") ? "dhcp" : "static",
								ipAddress: $("#ipAddress").val(),
								netmask: $("#netmask").val(),
								gateway: $("#gateway").val(),
								nameservers: nameservers.join(","),
								mtu: $("#mtu").val()
							}
						};

		$.getJSON("ajax/ajaxRequest.php", params, function(response)
		{
			if (response.errors.length > 0)
			{
				$("#wanMessages")
					.css("color", "red")
					.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>")
					.show();
					
				saveButton
					.html("Save")
					.removeAttr("disabled");
			}
			else
			{
				$("#saveWanSettingsBtn").hide();
				$("#wanMessages")
					.css("color", "green")
					.html("WAN settings saved successfully")
					.show()
					.fadeOut(3000);
			}
		});
	});

	// initialize
	$("#saveWanSettingsBtn").hide();
	$("#wanMessages").hide();
	
	if (<?=NetworkInterface::getInstance("wan")->usesDhcp() ? "true" : "false"?>)
	{
		$("#ipTypeDhcp").click();
		$("#dnsTypeDhcp").click();
	}
	else
	{
		$("#ipTypeStatic").click();
		$("#dnsTypeStatic").click();
	}
});
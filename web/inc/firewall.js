$(document).ready(function()
{
	// Initialize elements
	$("#saveBtn").hide();
	$("#fwResults").hide();
	$("#hideshow").hide();
	$(".fwRuleDetails").hide();
	makeFirewallTableEditable();
	addRuleDetailsPopup();

	// Register event handlers
	$("#cancelBtn").click(function()
	{
		$("#hideshow").hide();
	});

	$("#deleteBtn").click(function()
	{
		$("#" + $("#ruleId").val()).remove();
		$("#" + $("#ruleId").val() + "details").remove();
		$("#hideshow").hide();
		showSaveButton();
	});

	$("#saveAsNewBtn").click(function()
	{
		$("#ruleId").val("");
		$("#saveRuleBtn").click();
	});
	
	$("#saveRuleBtn").click(function()
	{
		// Save rule
		var connStates = [];
		$(".connState").each(function()
		{
			if ($(this).attr("checked"))
				connStates.push($(this).val());
		});
		
		var ruleInfo =	{
							id:			$("#ruleId").val(),
							protocol:	$("#protocol").val(),
							icmp_type:	$("#icmpType").val(),
							src_addr:	$("#srcAddr").val(),
							sport:		$("#srcPort").val(),
							dst_addr:	$("#dstAddr").val(),
							dport:		$("#dstPort").val(),
							state:		connStates.join(","),
							fragmented:	$("#fragmented").val(),
							target:		$("#target").val()
						};
		
		$.getJSON("ajax/addEditFwFilterRule.php", ruleInfo, function(json)
		{
			if (json.result)
			{	
				if (ruleInfo.id.length > 0)
				{	
					$("#" + ruleInfo.id).replaceWith(json.html.row);
					$("#" + ruleInfo.id + "details").replaceWith(json.html.div);
				}
				else
				{
					$("#firewall-table tbody").append(json.html.row);
					$("#fwTable").append(json.html.div);
				}
				
				addRuleDetailsPopup();
				makeFirewallTableEditable();
				$(".fwRuleDetails").hide();
				$("#hideshow").hide();
				showSaveButton();
			}
			else
			{
				$("#fwAddEditFilterRuleMsgs").css("color", "red");
				var html =	"The following errors occured:" +
							"<ul>";

				for (i=0 ; i<json.errors.length ; i++)
					html += "<li>" + json.errors[i] + "</li>";

				html += "</ul>";

				$("#fwAddEditFilterRuleMsgs").html(html);
			}
		});
	});

	$("#changePolicyBtn").click(function()
	{
		$("#firewall-table tr[class *= 'fwPolicy']").each(function()
		{
					
						
			if ($(this).hasClass("fwPolicyDrop"))
			{
				$(this).removeClass("fwPolicyDrop");
				$(this).addClass("fwPolicyAccept");
			}
			else
			{
				$(this).removeClass("fwPolicyAccept");
				$(this).addClass("fwPolicyDrop");
			}
		});
		
		showSaveButton();
	});

	$("#addRuleBtn").click(function(){addEditFilterRuleDlg(false);});

	$("#saveBtn").click(function()
	{
		// Save firewall rules on the server
		reorderRules();
		
		var firewall =	{
							direction:	$("#dir").val(),
							policy:		$("#firewall-table tr[class *= 'fwPolicy']").attr("class").indexOf("Drop") != -1 ? "DROP" : "ACCEPT",
							rules: 		getClientRules()
						};
		
		$.post("ajax/applyFwFilterRules.php", firewall, function(json)
		{
			if (json)
			{
				$("#saveBtn").hide();
				$("#fwResults").css("color", "green");
				$("#fwResults").html("Firewall settings saved successfully");
				$("#fwResults").show();
				$("#fwResults").fadeOut(3000);
			}
			else
			{
				$("#fwResults").css("color", "red");
				$("#fwResults").html("There was an error saving your firewall settings...please try again");
				$("#fwResults").show();
			}
		});
	});
});

function getClientRules()
{
	var rules = [];

	$(".fwDetailsTable").each(function()
	{				
		var rule =	{
						src_addr:	$(this).children().children().children("[id $= 'src_addr']").html(),
						dst_addr:	$(this).children().children().children("[id $= 'dst_addr']").html(),
						state:		$(this).children().children().children("[id $= 'state']").html(),
						fragmented:	$(this).children().children().children("[id $= 'fragmented']").html(),
						protocol:	$(this).children().children().children("[id $= 'protocol']").html(),
						dport:		$(this).children().children().children("[id $= 'dport']").html(),
						sport:		$(this).children().children().children("[id $= 'sport']").html(),
						icmp_type:	$(this).children().children().children("[id $= 'icmp_type']").length > 0 ? $(this).children().children().children("[id $= 'icmp_type']").html() : "*",
						target:		$(this).children().children().children("[id $= 'target']").html()
					};
		
		rules.push(rule);
	});

	return rules;
}

function makeFirewallTableEditable()
{
	$("#firewall-table tr[class ^= 'fwRule']").each(function()
	{
		if ($(this).children("td:last").children("[id $= editRuleBtn]").length == 0)
			$(this).append("<td><button id=\"" + $(this).attr("id") + "editRuleBtn\" type=\"button\">Edit</button></td>");
	});
	
	$("button[id $= 'editRuleBtn']").each(function()
	{
		$(this).click(function(){addEditFilterRuleDlg($(this).parent().parent().attr("id"));});
	});

	// Initialize firewall table
	$("#firewall-table").tableDnD(
	{
		onDragClass: "tableRowMove",
		onDrop: function(table, row){showSaveButton();}
	});
}
	
function addEditFilterRuleDlg(ruleId)
{
	$("#saveAsNewBtn").attr("disabled", "disabled");
	$("#deleteBtn").attr("disabled", "disabled");
	$("#fwAddEditFilterRuleMsgs").html("");
	resetAddEditRuleForm();

	if (ruleId)
	{
		// Edit existing rule
		$("#saveAsNewBtn").removeAttr("disabled");
		$("#deleteBtn").removeAttr("disabled");

		// Set values
		$("#ruleId").val(ruleId);
		$("#protocol").val($("#" + ruleId + "protocol").html() == "*" ? "any" : $("#" + ruleId + "protocol").html());

		if ($("#" + ruleId + "icmp_type").length > 0)
			$("#icmpType").val($("#" + ruleId + "icmp_type").html() == "*" ? "any" : $("#" + ruleId + "icmp_type").html());
		
		$("#srcAddr").val($("#" + ruleId + "src_addr").html() == "*" ? "" : $("#" + ruleId + "src_addr").html());
		$("#srcPort").val($("#" + ruleId + "sport").html() == "*" ? "" : $("#" + ruleId + "sport").html());
		$("#dstAddr").val($("#" + ruleId + "dst_addr").html() == "*" ? "" : $("#" + ruleId + "dst_addr").html());
		$("#dstPort").val($("#" + ruleId + "dport").html() == "*" ? "" : $("#" + ruleId + "dport").html());	
		$(".connState").each(function()
		{
			var stateOption = $(this).val();
			var ruleState = $("#" + ruleId + "state").html();

			if (ruleState.indexOf(stateOption) != -1)
				$(this).attr("checked", "checked");
		});
		$("#fragmented").val($("#" + ruleId + "fragmented").html() == "*" ? "any" : $("#" + ruleId + "fragmented").html());
		$("#target").val($("#" + ruleId + "target").html());
	}
	else
		$("#ruleId").val("");

	$("#hideshow").show();
}

function showSaveButton()
{
	if (!$("#saveBtn").is(":visible"))
		$("#saveBtn").fadeIn();
}

function reorderRules()
{
	$($("#firewall-table tr[class ^= 'fwRule']").get().reverse()).each(function()
	{
		var ruleId = $(this).attr("id");
		$("#firewall-table").after($("#" + ruleId + "details"));
	});
}

function resetAddEditRuleForm()
{
	$("#addEditRuleForm").each(function()
	{
		this.reset();
	});
	
	// IE fails to reset these back to their default values, so we'll set them
	// manually
	$("#protocol").val("any");
	$("#icmpType").val("any");
	$("#fragmented").val("any");
	$("#target").val("DROP");
}

function addRuleDetailsPopup()
{
	$("#firewall-table tr[class ^= 'fwRule']")
		.mouseover(function(e)
		{
			// Display firewall rule details
			$("#" + $(this).attr("id") + "details").css({position: "absolute",
												 		left: e.pageX,
												 		top: $(this).position().top + $(this).height(),
												 		display: "inline"}).show();
		})
		.mouseout(function()
		{
			// Hide firewall rule details
			$("#" + $(this).attr("id") + "details").hide();
		});
}
var dndTable;
		
function showRule(event, row, ruleId)
{			
	if (document.isButtonDown)
		return;

	var event = event || window.event;
			
	var ruleDetails = document.getElementById(ruleId + "details");
	var pos = getElementPosition(row);
	var posLeft = event.pageX || event.clientX;
	var posTop = pos[1] + 25;
			
	ruleDetails.style.position = "absolute";
	ruleDetails.style.left = posLeft + "px";
	ruleDetails.style.top = posTop + "px";
	ruleDetails.style.display = "inline";
}

function hideRule(ruleId)
{
	document.getElementById(ruleId + "details").style.display = "none";
}

function getElementPosition(element)
{
	var curleft = curtop = 0;

	if (element.offsetParent)
	{
		do
		{
			curleft += element.offsetLeft;
			curtop += element.offsetTop;
		} while (element = element.offsetParent);
	}

	return [curleft, curtop];
}

function dndInit()
{
	dndTable = new TableDnD();
	dndTable.init(document.getElementById("firewall-table"));
}

var currenttable = null;

document.onmousemove = function(ev)
{
	if (currenttable && currenttable.dragObject)
	{
		ev   = ev || window.event;
		var mousePos = currenttable.mouseCoords(ev);
		var y = mousePos.y - currenttable.mouseOffset.y;
		
		if (y != currenttable.oldY)
		{
			// work out if we're going up or down...
			var movingDown = y > currenttable.oldY;
			
			// update the old values
			currenttable.oldY = y;

			if (currenttable.oldClass == null)
				currenttable.oldClass = currenttable.dragObject.className;
            
			// update the style to show we're dragging
			currenttable.dragObject.className = "tableRowMove";

			// If we're over a row then move the dragged row to there so that the user sees the
			// effect dynamically
			var currentRow = currenttable.findDropTargetRow(y);

			if (currentRow)
			{
				if (movingDown && currenttable.dragObject != currentRow)
					currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow.nextSibling);
				else if (! movingDown && currenttable.dragObject != currentRow)
					currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow);
			}
		}

		return false;
	}
};

// Similarly for the mouseup
document.onmouseup = function(ev)
{
	ev = ev || window.event;
	var evSrc = getEventSource(ev);
	
	// Unregister mouse down action
	document.isButtonDown = false;
	
	if (currenttable && currenttable.dragObject)
	{
		var droppedRow = currenttable.dragObject;

		// If we have a dragObject, then we need to release it,
		// The row will already have been moved to the right place so we just reset stuff
		if (currenttable.oldClass != null && currenttable.oldClass != "undefined")
		{
			droppedRow.className = currenttable.oldClass;
			currenttable.oldClass = null;
		}
        
		currenttable.dragObject   = null;
		// And then call the onDrop method in case anyone wants to do any post processing
		currenttable.onDrop(currenttable.table, droppedRow);
		currenttable = null; // let go of the table too
	}
};

document.onmousedown = function(ev)
{
	// Indicate that the button is pressed	
	document.isButtonDown = true;
};

/** get the source element from an event in a way that works for IE and Firefox and Safari
 * @param evt the source event for Firefox (but not IE--IE uses window.event) */
function getEventSource(evt)
{
	if (window.event)
	{
		evt = window.event; // For IE
		return evt.srcElement;
	} 
	else
		return evt.target; // For Firefox
}

/**
 * Encapsulate table Drag and Drop in a class. We'll have this as a Singleton
 * so we don't get scoping problems.
 */
function TableDnD()
{
	/** Keep hold of the current drag object if any */
	this.dragObject = null;

	/** The current mouse offset */
	this.mouseOffset = null;

	/** The current table */
	this.table = null;

	/** Remember the old value of Y so that we don't do too much processing */
	this.oldY = 0;

	/** Initialise the drag and drop by capturing mouse move events */
	this.init = function(table)
	{
		this.table = table;
		var rows = table.tBodies[0].rows; //getElementsByTagName("tr")

		for (var i=0; i<rows.length; i++)
		{
			// John Tarr: added to ignore rows that I've added the NoDnD attribute to (Category and Header rows)
			var nodrag = rows[i].getAttribute("NoDrag");

			if (nodrag == null || nodrag == "undefined") // There is no NoDnD attribute on rows I want to drag
				this.makeDraggable(rows[i]);
		}
	};

	/** This function is called when you drop a row, so redefine it in your code
	to do whatever you want, for example use Ajax to update the server */
	this.onDrop = function(table, droppedRow)
	{

		// Display buttons to save rule settings
		showSaveButton();
	};

	/** Get the position of an element by going up the DOM tree and adding up all the offsets */
	this.getPosition = function(e)
	{
		var left = 0;
		var top  = 0;

		/** Safari fix -- thanks to Luis Chato for this! */
		if (e.offsetHeight == 0)
		{
			/** Safari 2 doesn't correctly grab the offsetTop of a table row
			this is detailed here:
			http://jacob.peargrove.com/blog/2006/technical/table-row-offsettop-bug-in-safari/
			the solution is likewise noted there, grab the offset of a table cell in the row - the firstChild.
			note that firefox will return a text node as a first child, so designing a more thorough
			solution may need to take that into account, for now this seems to work in firefox, safari, ie */
			e = e.firstChild; // a table cell
		}

		while (e.offsetParent)
		{
			left += e.offsetLeft;
			top  += e.offsetTop;
			e = e.offsetParent;
		}

		left += e.offsetLeft;
		top += e.offsetTop;

		return {x:left, y:top};
	};

	/** Get the mouse coordinates from the event (allowing for browser differences) */
	this.mouseCoords = function(ev)
	{
		if (ev.pageX || ev.pageY)
			return {x:ev.pageX, y:ev.pageY};
		return	{
					x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
					y:ev.clientY + document.body.scrollTop  - document.body.clientTop
				};
	};

	/** Given a target element and a mouse event, get the mouse offset from that element.
	To do this we need the element's position and the mouse position */
	this.getMouseOffset = function(target, ev)
	{
		ev = ev || window.event;

		var docPos = this.getPosition(target);
		var mousePos  = this.mouseCoords(ev);
		return	{
					x:mousePos.x - docPos.x,
					y:mousePos.y - docPos.y
				};
	};

	/** Take an item and add an onmousedown method so that we can make it draggable */
	this.makeDraggable = function(item)
	{
		if (!item)
			return;

		var self = this; // Keep the context of the TableDnd inside the function

		item.onmousedown = function(ev)
		{
			ev = ev || window.event;
        	
			if (!isLeftMouseButton(ev))
				return;
        	        	        	
			// Need to check to see if we are an input or not, if we are an input, then
			// return true to allow normal processing
			var target = getEventSource(ev);
			
			if (target.tagName == 'INPUT' || target.tagName == 'SELECT')
				return true;

			currenttable = self;
			self.dragObject  = this;
			self.mouseOffset = self.getMouseOffset(this, ev);
            
			// Close any open popups
			hideRule(item.id);
            
			return false;
		};

		item.style.cursor = "move";
	};

	/** We're only worried about the y position really, because we can only move rows up and down */
	this.findDropTargetRow = function(y)
	{
		var rows = this.table.tBodies[0].rows;

		for (var i=0; i<rows.length; i++)
		{
			var row = rows[i];

			// John Tarr added to ignore rows that I've added the NoDnD attribute to (Header rows)
			var nodrop = row.getAttribute("NoDrop");

			if (nodrop == null || nodrop == "undefined") //There is no NoDnD attribute on rows I want to drag
			{
				var rowY    = this.getPosition(row).y;
				var rowHeight = parseInt(row.offsetHeight) / 2;

				if (row.offsetHeight == 0)
				{
					rowY = this.getPosition(row.firstChild).y;
					rowHeight = parseInt(row.firstChild.offsetHeight)/2;
				}

				// Because we always have to insert before, we need to offset the height a bit
				if ((y > rowY - rowHeight) && (y < (rowY + rowHeight)))
				{
					// that's the row we're over
					return row;
				}
			}
		}

		return null;
	};
}

function isLeftMouseButton(event)
{
	if (window.event)
	{
		// IE or Chrome
		if (window.event.button == 1)
			return true;
		
		if (event)
		{
			// Chrome
			if (event.button == 0)
				return true;
			
			return false;
		}

		return false;
	}
	
	// Mozilla
	if (event.button == 0)
		return true;

	return false;
}

function fade(element, opacity)
{
	if (element.style.opacity == null || element.style.opacity == "undefined")
		// We're not dealing with any browser that doesn't support
		// the CSS3 standard of opacity (IE, I'm looking at you)
		return;
		
	if (opacity == null || opacity == "undefined")
		opacity = 10;
	
	if (opacity > 0)
	{
		opacity -= 1;
		element.style.opacity = opacity / 10;
		
		var recurse = function()
		{
			fade(element, opacity);
		};
		
		setTimeout(recurse, 70);
	}
	else
		opacity = 10;
}

function addEditFilterRuleDlg(ruleId)
{
	document.getElementById("fwAddEditFilterRuleMsgs").innerHTML = "";
	document.addEditRuleForm.reset();
	
	if (ruleId)
	{
		// Editing an existing rule
		document.getElementById("saveAsNewBtn").disabled = false;
		document.getElementById("deleteBtn").disabled = false;
		
		var stateChangeFunc = function()
		{
			if (xhr.readyState != 4)
				return;
			
			if (xhr.status != 200)
				return;
			
			var response;
			
			if (window.JSON)
				response = JSON.parse(xhr.responseText);
			else
				response = eval("(" + xhr.responseText + ")");
			
			if (response)
			{
				document.addEditRuleForm.ruleId.value = ruleId;
				
				var formProtocol = document.addEditRuleForm.protocol;
				var formSrcAddr = document.addEditRuleForm.srcAddr;
				var formSrcPort = document.addEditRuleForm.srcPort;
				var formDstAddr = document.addEditRuleForm.dstAddr;
				var formDstPort = document.addEditRuleForm.dstPort;
				var formConnStates = document.addEditRuleForm.connState;
				var formFragmented = document.addEditRuleForm.fragmented;
				var formIcmpType = document.addEditRuleForm.icmpType;
				var formTarget = document.addEditRuleForm.target;
				
				// Set protocol
				for (i=0 ; i<formProtocol.options.length ; i++)
				{
					if (formProtocol.options[i].value == response.protocol)
					{
						formProtocol.options[i].selected = true;
						break;
					}
				}
				
				// Set source address
				formSrcAddr.value = response.src_addr == null ? "" : response.src_addr;
				
				// Set source port
				formSrcPort.value = response.sport == null ? "" : response.sport;
				
				// Set destination address
				formDstAddr.value = response.dst_addr == null ? "" : response.dst_addr;
				
				// Set destination port
				formDstPort.value = response.dport == null ? "" : response.dport;
				
				// Set connection state(s)
				if (response.state)
				{
					var connStates = response.state.split(",");
				
					for (i=0 ; i<formConnStates.length ; i++)
					{
						for (j=0 ; j<connStates.length ; j++)
						{
							if (formConnStates[i].value == connStates[j])
								formConnStates[i].checked = true;
						}
					}
				}
				
				// Set fragmented
				if (response.fragmented == "Y")
					formFragmented.options[1].selected = true;
				else if (response.fragmented == "N")
					formFragmented.options[2].selected = true;
				
				// Set ICMP type
				for (i=0 ; i<formIcmpType.options.length ; i++)
				{
					if (formIcmpType.options[i].value == response.icmp_type)
					{
						formIcmpType.options[i].selected = true;
						break;
					}
				}
				
				// Set target
				formTarget.value = response.target;
			}
		};
		
		sendAjaxRequest("ajax/getFilterRule.php?id=" + ruleId, stateChangeFunc, "GET");
	}
	else
		document.addEditRuleForm.ruleId.value = null;
	
	document.getElementById("hideshow").style.visibility = "visible";
}

function closeAddEditRule()
{
	document.getElementById("hideshow").style.visibility = "hidden";
	document.getElementById("saveAsNewBtn").disabled = true;
	document.getElementById("deleteBtn").disabled = true;
}

function getPageHeight()
{
	var body = document.body;
	var html = document.documentElement;

	return Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
}

function submitAddEditRule()
{
	var params = new Array();
	
	if (document.addEditRuleForm.ruleId.value && document.addEditRuleForm.ruleId.value != "null")
		params.push("ruleId=" + document.addEditRuleForm.ruleId.value);
	
	params.push("dir=" + document.addEditRuleForm.dir.value);
	params.push("protocol=" + document.addEditRuleForm.protocol.value);
	params.push("srcAddr=" + document.addEditRuleForm.srcAddr.value);
	params.push("srcPort=" + document.addEditRuleForm.srcPort.value);
	params.push("dstAddr=" + document.addEditRuleForm.dstAddr.value);
	params.push("dstPort=" + document.addEditRuleForm.dstPort.value);
	
	if (document.addEditRuleForm.connState)
	{
		for (i=0 ; i<document.addEditRuleForm.connState.length; i++)
		{
			if (document.addEditRuleForm.connState[i].checked)
				params.push("connState[]=" + document.addEditRuleForm.connState[i].value);
		}
	}
	
	params.push("fragmented=" + document.addEditRuleForm.fragmented.value);
	params.push("icmpType=" + document.addEditRuleForm.icmpType.value);
	params.push("target=" + document.addEditRuleForm.target.value);
	
	var postStr = params.join("&");
	
	var stateChangeFunc = function()
	{
		if (xhr.readyState != 4)
			return;
		
		if (xhr.status != 200)
			return;
		
		var response;
		
		if (window.JSON)
			response = JSON.parse(xhr.responseText);
		else
			response = eval("(" + xhr.responseText + ")");
		
		if (response.result)
		{
			// Add/edit was successful
			updateFilterTable(response.fwFilterTableHtml);
			showSaveButton();
			closeAddEditRule();
		}
		else
		{
			// Add/edit was unsuccessful
			var msgsPanel = document.getElementById("fwAddEditFilterRuleMsgs");
			msgsPanel.style.color = "red";
			
			var html =	"The following errors occured:" +
						"<ul>";
			
			for (i=0 ; i<response.errors.length ; i++)
				html += "<li>" + response.errors[i] + "</li>";
			
			html += "</ul>";
			
			msgsPanel.innerHTML = html;
		}
	};
	
	sendAjaxRequest("ajax/addEditFwFilterRule.php", stateChangeFunc, "POST", postStr);
}

function deleteRule(ruleId)
{
	var stateChangeFunc = function()
	{
		if (xhr.readyState != 4)
			return;
		
		if (xhr.status != 200)
			return;
		
		var response;
		
		if (window.JSON)
			response = JSON.parse(xhr.responseText);
		else
			response = eval("(" + xhr.responseText + ")");
		
		if (response.result)
		{
			updateFilterTable(response.fwFilterTableHtml);
			showSaveButton();
		}
		
		closeAddEditRule();
	};
	
	sendAjaxRequest("ajax/deleteFwFilterRule.php?ruleId=" + ruleId, stateChangeFunc, "GET");
}

function updateFilterTable(html)
{
	document.getElementById("fwTable").innerHTML = html;
	dndInit();
}

function showSaveButton()
{
	if (!document.getElementById("saveBtn"))
	{
		var element = document.getElementById("fwActions");
		element.innerHTML = "<input id=\"saveBtn\" type=\"button\" value=\"Save Changes\" onClick=\"saveRules()\" />";
		
		if (element.style.opacity != null && element.style.opacity != "undefined")
			element.style.opacity = 1;
	}
}
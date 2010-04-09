var xhr;

function getXmlHttpRequest()
{
	xmlhttp = false;
	
	if (window.XMLHttpRequest)
	{
		// Browsers that have a native XMLHttpRequest javascript object
		xmlhttp = new XMLHttpRequest();
	}
	else if (window.ActiveXObject)
	{
		// Browsers that support XMLHttpRequest via an ActiveX object only (IE)
		try
		{
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{}
		}
	}
	
	return xmlhttp;
}

function sendAjaxRequest(url, stateChangeFunc, method, postParameters)
{
	if (!xhr)
		xhr = getXmlHttpRequest();
	
	if (!xhr)
		return false;
	
	xhr.onreadystatechange = stateChangeFunc;
	xhr.open(method, url, true);
	
	if (method == "POST")
	{
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		
		if (postParameters != null && postParameters != "undefined")
			xhr.setRequestHeader("Content-length", postParameters.lenbth);
	}
	
	if (postParameters != null && postParameters != "undefined")
		xhr.send(postParameters);
	else
		xhr.send();
	
	return true;
}
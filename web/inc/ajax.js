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
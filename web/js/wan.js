function setInputsForUser(group,dhcpOn)
{
   if (group != "admins")
   {
      for (i=0 ; i<document.wanconfig.elements.length ; i++)
         document.wanconfig.elements[i].disabled = true;
   }
   else
   {
      initInputs(dhcpOn);
   }
}

function disableInputs()
{
   var disabledColor = "#E9E9E9";

   document.wanconfig.ipaddress.disabled = true;
   document.wanconfig.netmask.disabled   = true;
   document.wanconfig.gateway.disabled   = true;

   document.wanconfig.ipaddress.style.backgroundColor = disabledColor;
   document.wanconfig.netmask.style.backgroundColor   = disabledColor;
   document.wanconfig.gateway.style.backgroundColor   = disabledColor;
}

function enableInputs()
{
   var enabledColor = "#FFFFFF";

   document.wanconfig.ipaddress.disabled = false;
   document.wanconfig.netmask.disabled   = false;
   document.wanconfig.gateway.disabled   = false;

   document.wanconfig.ipaddress.style.backgroundColor = enabledColor;
   document.wanconfig.netmask.style.backgroundColor   = enabledColor;
   document.wanconfig.gateway.style.backgroundColor   = enabledColor;
}

function initInputs(dhcpOn)
{
   if (dhcpOn == true)
   {
      disableInputs();
   }
   else
   {
      enableInputs();
   }
}

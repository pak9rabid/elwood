function setInputsForUser(group,dhcpOn)
{
   if (group != "admins")
   {
      for (i=0 ; i<document.lanconfig.elements.length ; i++)
         document.lanconfig.elements[i].disabled = true;
   }
   else
   {
      initInputs(dhcpOn);
   }
}

function disableInputs()
{
   var disabledColor = "#E9E9E9";

   for (i=4 ; i<document.lanconfig.elements.length-5 ; i++)
   {
      document.lanconfig.elements[i].disabled = true;
      document.lanconfig.elements[i].style.backgroundColor = disabledColor;
   }
}

function enableInputs()
{
   var enabledColor = "#FFFFFF";

   for (i=4 ; i<document.lanconfig.elements.length-5 ; i++)
   {
      document.lanconfig.elements[i].disabled = false;
      document.lanconfig.elements[i].style.backgroundColor = enabledColor;
   }
}

function initInputs(dhcpOn)
{
   if (dhcpOn == true)
   {
      enableInputs();
   }
   else
   {
      disableInputs();
   }
}

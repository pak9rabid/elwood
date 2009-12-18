function disableAccessInputs()
{
   for (i=0 ; i<document.access_method.elements.length ; i++)
      document.access_method.elements[i].disabled = true;
}

function disableUserInputs()
{
  for (i=0 ; i<document.users.elements.length-5 ; i++)
     document.users.elements[i].disabled = true;
}

function setInputsForUser(group, http_wan, http_lan, ssh_wan, ssh_lan)
{
   // Set input selection for access methods
   if (http_wan == 1)
   {
      document.access_method.httpwan.checked = true;
   }

   if (http_lan == 1)
   {
      document.access_method.httplan.checked = true;
   }

   if (ssh_wan == 1)
   {
      document.access_method.sshwan.checked = true;
   }

   if (ssh_lan == 1)
   {
      document.access_method.sshlan.checked = true;
   }

   // Disable controls to make changes unless the user
   // is part of the 'admins' group
   if (group != "admins")
   {
      disableAccessInputs();
      disableUserInputs();
   }
}

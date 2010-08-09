function toggleOptions()
{
   if (document.getElementById("removable").style.display == "none")
      showOptions();
   else
      hideOptions();
}

function showOptions()
{
   document.getElementById("removable").style.display = "";
}

function hideOptions()
{
   document.getElementById("removable").style.display = "none";
}

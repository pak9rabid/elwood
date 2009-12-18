function popUp(url, winProperties)
{
   newWindow = window.open(url, name, winProperties);
   if (window.focus)
      { newWindow.focus() }
   return false;
}

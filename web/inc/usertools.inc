<?php
   ################################################
   # File:          usertools.inc                 #
   # Author:        Patrick Griffin               #
   # Last Modified: 9-30-2005                     #
   #                                              #
   # Description:   Provides necessary tools to   #
   #                determine which user is       #
   #                currently logged into the     #
   #                router.                       #
   ################################################

   # Include required files
   require_once "routersettings.inc";

   function getUser()
   # Preconditions:  None
   # Postconditions: Returns a string containing the current user
   #                 logged into the router web-interface, or 
   #                 -1 if no user is logged in
   {
      # Probe the system for the current user logged in
      $currentUser = $_SERVER['PHP_AUTH_USER'];

      if ($currentUser == "")
         return -1;
      else
         return trim($currentUser);
   }

   function getGroup($user)
   # Preconditions:  $user must be a valid user for the web-interface
   # Postconditions: Returns a string containing the group that $user
   #                 belongs to, or -1 if $user is not found
   {
      # Path to groups file
      $groupsFile = getHttpdDir() . "/groups";

      # Command to find the group the user is associated with
      $getGroupCmd = "cat $groupsFile | grep $user | cut -f1 -d':'";

      # Execute command and return the group, or return -1 if the user
      # does not exist
      $group = `$getGroupCmd`;

      if ($group == "")
         return -1;
      else
         return trim($group);
   }
?>

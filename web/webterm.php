<?php
   ################################################
   # File :       webterm.php                     #
   #                                              #
   # Author:      Patrick Griffin                 #
   #                                              #
   # Date:        1-1-2006                        #
   #                                              #
   # Description: Provides a web-based terminal   #
   #              emulator to execute Linux       #
   #              commands in.  Script only has   #
   #              the ability to run simple Linux #
   #              commands that are not           #
   #              interactive.                    #
   ################################################

   # Include required files
   require_once "formatting.inc";
   require_once "WebtermHistory.class.php";

   # Get the current user
   $currentUser = getUser();

   # Pull commands from form
   $cmd     = $_POST['cmd'];
   $prevCmd = $_POST['prev_cmd'];

   # Convert all single-quotes to double-quotes, if any, in the entered command
   $cmd = preg_replace("/\'/", "\"", $cmd);

   # Create list of previously executed commands
   $wtHistory = new WebtermHistory($currentUser);
   $prevCmdsList = array();

   foreach ($wtHistory->getHistory() as $dataHash)
      $prevCmdsList[] = $dataHash->getAttribute("COMMAND");

   # Append last entered command to webterm history and execute command if entered
   if ($cmd != "" || $prevCmd != "")
   {
      # Determine which command to execute (the new command or one of the previous
      # commands)
      if ($cmd != "")
         $executeThis = $cmd;
      else
         $executeThis = $prevCmd;

      # Append executed command to the end of the previous commands list
      # if it does not contain any html tags
      $test = strip_tags($executeThis);

      $containsTags = false;
 
      if ($test == $executeThis)
      {
         try
         {
            $wtHistory->addEntry($executeThis);
            $prevCmdsList[] = $executeThis;
         }
         catch (Exception $ex)
         {
            echo "Exception: " . $ex->getMessage();
         }
      }
      else
         $containsTags = true;

      $output = `$executeThis`;

      # Check to see if there are any </textarea> tags in the $output and
      # replace them with <\textarea> tags so it does not break the webpage
      $output = preg_replace("/<\/textarea>/", "<\\textarea>", $output);
   }

   # Flip the array around so that it reflects the order in which
   # each command was entered in the select list
   $prevCmdsList = array_reverse($prevCmdsList);
?>

<html>
   <head>
      <title>WebTerm</title>
      <link rel="StyleSheet" type="text/css" href="routerstyle.css">
   </head>

   <body onLoad="document.form.cmd.focus()">
      <div id="container">
         <div id="title">
            <?php printTitle("WebTerm"); ?>
         </div>
         <?php
            echo printNavigation();
         ?>
         <div id="content">
            <h4>Command Output</h4>
<textarea readonly>
<? echo $output; ?>
</textarea>
            <div id="cmd-input">
               <?php
                  # If previous command contained tags, print warning message
                  # that the command will not be stored in history
                  if ($containsTags)
                     echo "<font id='error-font'>Warning: Last command was not saved in history because it contained HTML tags</font>";
               ?>
               <form name="form" action="webterm.php" method="POST">
                  <table border="0" align="center">
                     <tr>
                        <th align="right">Linux Command:</th>
                        <td><input id="textfield" name="cmd" size="35" maxlength="255"></td>
                     </tr>
                     <tr>
                        <th align="right">Previous Commands:</th>
                        <td>
                           <select id="cmd-list" name="prev_cmd">
                              <?php
                                 # Print commands as options in the select box
                                 foreach ($prevCmdsList as $value)
                                    echo "<option $value>$value\n";
                              ?>
                           </select>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="2" align="center"><input type="submit" value="Execute"></td>
                     </tr>
                  </table>
               </form>
            </div>
         </div>
      </div>
   </body>
</html>

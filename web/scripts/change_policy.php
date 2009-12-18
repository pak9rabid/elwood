<html>
   <head>
      <title>Change Forwarding Policy</title>
      <link rel="StyleSheet" type="text/css" href="../fwdetail.css">
   </head>

   <body>
      <div id="content">
         <?php
            # Pull info from calling page
            $newPolicy = $_GET['policy'];

            # Display content
            echo "<b>Change policy to $newPolicy?</b><br>";
            echo "<a href='admin/change_firewall.php?action=policy&change=yes&policy=$newPolicy'>Yes</a>&nbsp&nbsp&nbsp&nbsp<a href='javascript:window.close()' onClick='javascript:window.close()'>No</a>";
         ?>
      </div>
   </body>
</html>

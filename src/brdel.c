#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include "config.h"

int main(int argc, char** argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // If minimum args are not met,
   // don't construct or execute command
   if (argc > 1)
   {
      // Construct command to remove bridge interface
      char brCmd[128] = BRCTL;
      strcat(brCmd, " delbr ");
      strcat(brCmd,  argv[1]);

      // Execute command
      system(brCmd);
   }
   else
      // Print usage synopsis
      printf("\nbrdel <bridge interface>\n\n");

   return 0;
}

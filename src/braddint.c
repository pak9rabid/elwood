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
   if (argc > 2)
   {
      // Construct command to add interface to a bridge
      char brCmd[128] = BRCTL;
      strcat(brCmd, " addif ");
      strcat(brCmd, argv[1]);
      strcat(brCmd, " ");
      strcat(brCmd, argv[2]);

      // Execute command
      system(brCmd);
   }
   else
      // Print usage synopsis
      printf("\nbraddint <bridge interface> <interface>\n\n");

   return 0;
}

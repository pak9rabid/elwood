#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include "config.h"

int main(int argc, char** argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // If minimum args are not met,
   // don't construct or command
   if (argc > 3)
   {
      // Construct command to add network route
      char routeCmd[128] = ROUTE;
      strcat(routeCmd, " add -net ");
      strcat(routeCmd, argv[1]);
      strcat(routeCmd, " netmask ");
      strcat(routeCmd, argv[2]);
      strcat(routeCmd, " ");
      strcat(routeCmd, argv[3]);

      // Execute command
      system(routeCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: routeadd <network> <netmask> <interface>\n\n");

   return 0;
}

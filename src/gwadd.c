#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include "config.h"

int main(int argc, char** argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // If minimum args are not met,
   // don't construct or execute ifconfig command
   if (argc > 2)
   {
      // Construct command to add default gateway
      char gwCmd[128] = ROUTE;
      strcat(gwCmd, " add default gw ");
      strcat(gwCmd, argv[1]);
      strcat(gwCmd, " ");
      strcat(gwCmd, argv[2]);

      // Execute command
      system(gwCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: gwadd <gateway ip> <interface>\n\n");

   return 0;
}

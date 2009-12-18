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
   if ( (argc > 1) &&
	(strcmp(argv[1], "start") == 0 || 
	 strcmp(argv[1], "stop") == 0 ||
	 strcmp(argv[1], "restart") == 0))
   {
      // Construct command to start/stop/restart the network
      char networkCmd[128] = "/etc/init.d/networking ";

      strcat(networkCmd, argv[1]);

      // Execute command
      system(networkCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: network <start|stop|restart>\n\n");

   return 0;
}

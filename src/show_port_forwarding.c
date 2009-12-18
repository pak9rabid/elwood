#include <stdio.h>
#include <unistd.h>
#include "config.h"

int main()
{
   char runCmd[128] = IPTABLES;
   strcat(runCmd, " -t nat -L PREROUTING -v -n");
   setreuid(geteuid(), geteuid());
   system(runCmd);

   return(0);
}

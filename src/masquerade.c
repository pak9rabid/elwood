#include <stdio.h>
#include <unistd.h>
#include "config.h"

int main()
{
   char cmd[128] = IPTABLES;
   strcat(cmd, " -t nat -A POSTROUTING -o eth0 -j MASQUERADE");
   setreuid(geteuid(), geteuid());
   system(cmd);

   return(0);
}

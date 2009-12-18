#ifndef __WEBUSERS_H__
#define __WEBUSERS_H__

#include <string>
#include <vector>

class webusers
{
   private:
      // Attributes
      std::string groupsFilePath;

      std::vector<std::string> admins;
      std::vector<std::string> users;

      // Helper functions
      const int findUser(std::string user, std::vector<std::string> group);

   public:
      // Constructors
      webusers();
      webusers(std::string filePathIn);

      // Accessors
      const std::string getGroup(std::string user);
      const std::vector<std::string> getAdmins();
      const std::vector<std::string> getUsers();
      const std::string getFilePath();
      const bool userExists(std::string user);

      // Modifiers
      void addUser(std::string user);
      void addUser(std::string user, std::string group);
      void changeGroup(std::string user);
      void changeGroup(std::string user, std::string group);
      void removeUser(std::string user);
      void setFilePath(std::string filePathIn);
      void writeGroupsFile();
      void writeGroupsFile(std::string filePathIn);
};

#endif

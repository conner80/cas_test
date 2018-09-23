@echo off

set login=root
set mysql="C:\Progra~1\MySQL\MySQL Server 5.6\bin\mysql.exe"
set inst="install.sql"
if not exist %mysql% goto err_mysql
if not exist %inst% goto err_no
%mysql% -u %login% -p < %inst%
echo Ok
exit

:err_mysql
echo MySQL location "%mysql%" isn't correct!
exit /B 1

:err_no
echo File %inst% doesn't exists!
exit /B 2
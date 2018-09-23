@echo off

if [%1]==[] goto err_empty
if not exist %1 goto err_no

set login=cas_admin
set mysql="C:\Progra~1\MySQL\MySQL Server 5.6\bin\mysql.exe"
if not exist %mysql% goto err_mysql
%mysql% -u %login% -p < %1
echo Ok
exit

:err_mysql
echo MySQL location "%mysql%" isn't correct!
exit /B 1

:err_empty
echo The file isn't specified!
exit /B 2

:err_no
echo File "%1" doesn't exists!
exit /B 3
@echo off

if [%1]==[] goto help
if [%2]==[] goto help

set login=cas_admin
set mysql="C:\Progra~1\MySQL\MySQL Server 5.6\bin\mysql.exe"
if not exist %mysql% goto err_mysql
set sql=adduser.sql
if not exist %sql% goto err_no
set para=%sql%_
echo USE cas; SET @p1='%1'; SET @p2='%2'; > %para%
type adduser.sql >> adduser.sql_
%mysql% -u %login% -p < %para%
del %para%
echo Ok
exit

:help
echo Usage:
echo adduser.bat user password
exit /B 1

:err_mysql
echo MySQL location "%mysql%" isn't correct!
exit /B 2

:err_no
echo File "%sql%" doesn't exists!
exit /B 3

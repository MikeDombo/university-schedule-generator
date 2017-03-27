@echo off
php C:\xampp\php\pear\phploc.phar --exclude="vendor" --exclude="importer/Classes" --log-xml="%~dp0%build/phploc.xml" .
php C:\xampp\php\pear\phpdox.phar -f %~dp0%phpdox.xml

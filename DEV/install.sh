#!/bin/sh
# todo: -fix the sql-server pw-stuff (idea: random-seed, printed in the end)
#	-install asterisk
#	-do checkup for md5/sha
#	-use ip if dns not configured
#	-check ssl-stuff (want to have userinput when the script just started)

echo "updating system"
	apt-get -qqq update
	apt-get -qqq upgrade

echo "installing software"
	apt-get -qq install vim apache2 mysql-common mysql-server php4-mysql \
		libapache2-mod-php4 subversion openssl ssl-cert ssh less
echo "getting sources"
	svn co svn://svn.cccv.de/engel-system

echo "setting up apache2"
	mkdir /var/www/http/
	mkdir /var/www/https/

	mkdir /etc/apache2/ssl/

	openssl req $@ -new -x509 -days 365 -nodes -out /etc/apache2/ssl/apache.pem -keyout /etc/apache2/apache.pem

	chmod 600 /etc/apache2/ssl/apache.pem

	cp `pwd`/engel-system/default-conf/etc/default /etc/apache2/sites-available/default
	cp `pwd`/engel-system/default-conf/etc/https /etc/apache2/sites-available/https
	
	echo "Listen 443" >> /etc/apache2/ports.conf

	a2enmod ssl
	a2ensite https
	/etc/init.d/apache2 restart

echo "setting up mysql"
	mysql -u root mysql -e "CREATE DATABASE tabel;"	

echo "setting sources in place"
	cp -r `pwd`/engel-system/www/* /var/www/http/
	cp -r `pwd`/engel-system/www-ssl/* /var/www/https/
	cp -r `pwd`/engel-system/default-conf/www-ssl/inc/* /var/www/https/inc/
	
	rm /var/www/https/inc/config.php
	#cat `pwd`/engel-system/default-conf/www-ssl/inc/config.php|sed s/SEDENGELURL/`cat /etc/hostname`.`dnsdomainname`/ >> /var/www/https/inc/config.php
	cat `pwd`/engel-system/default-conf/www-ssl/inc/config.php|sed s/SEDENGELURL/`cat /etc/hostname`.`dnsdomainname`/ |sed s/MD5SED/`openssl x509 -noout -fingerprint -md5 -in /etc/apache2/ssl/apache.pem|sed s/MD5\ Fingerprint\=//`/|sed s/SHA1SED/`openssl x509 -noout -fingerprint -sha1 -in /etc/apache2/ssl/apache.pem|sed s/SHA1\ Fingerprint\=//`/ >> /var/www/https/inc/config.php
	#openssl x509 -noout -fingerprint -sha1 -in /etc/apache2/ssl/apache.pem|sed s/SHA1\ Fingerprint\=//
	
	#openssl x509 -noout -fingerprint -md5 -in /etc/apache2/ssl/apache.pem|sed s/MD5\ Fingerprint\=//

        mysql tabel -u root < `pwd`/engel-system/DB/ChangeLog.sql
        mysql tabel -u root < `pwd`/engel-system/DB/Himmel.sql
        mysql tabel -u root < `pwd`/engel-system/DB/Messages.sql
        mysql tabel -u root < `pwd`/engel-system/DB/Sprache.sql
        mysql tabel -u root < `pwd`/engel-system/DB/User.sql
        mysql tabel -u root < `pwd`/engel-system/DB/UserCVS.sql
        mysql tabel -u root < `pwd`/engel-system/DB/UserPicture.sql

echo "cleaning up"
	rm -rf `pwd`/engel-system/
	mysql -u root mysql -e "UPDATE user SET Password=PASSWORD('changeme') WHERE user='root';"
	mysql -u root mysql -e "FLUSH PRIVILEGES;"

echo "final hints:"
echo "-reset passwort for sqluser, don't forget to change /var/www/https/inc/config_db.php"
echo "-change the adminpassword in the webfrontend"
echo "-the webfrontend user/pass combo is: admin:admin"
echo "-the sql-server uses root:changeme"
echo "-make sure \$url in /var/www/https/inc/config.php is correct"


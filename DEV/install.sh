#!/bin/bash
# todo:	-install asterisk
#	-use ip if dns not configured (dig +short @141.1.1.1)

echo "updating system"
	apt-get -qq update
	apt-get -qq upgrade

echo "installing software"
	apt-get -qq install vim apache2 mysql-common mysql-server php5-mysql \
		libapache2-mod-php5 subversion openssl ssl-cert ssh less makepasswd

echo "setting local vars"
	SQL_PASSWD=`makepasswd --chars=8 --noverbose`
	ADM_PASSWD=`makepasswd --chars=8 --noverbose`

	state=DE
	province=Berlin
	town=Berlin
	org="CCC e.V."
	section="Congress"
	adminmail="admin@`cat /etc/hostname`.`dnsdomainname`"

	FQDN=`cat /etc/hostname`.`dnsdomainname`

echo "getting sources"
	svn co svn://svn.cccv.de/engel-system

echo "setting up apache2"
	mkdir /var/www/http/
	mkdir /var/www/https/

	mkdir /etc/apache2/ssl/

	echo -ne $state'\n'$province'\n'$town'\n'$org'\n'$section'\n'$FQDN'\n'$adminmail'\n'|openssl req $@ -new -x509 -days 365 -nodes -out /etc/apache2/ssl/apache.pem -keyout /etc/apache2/apache.pem

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
	cat `pwd`/engel-system/default-conf/www-ssl/inc/config.php|sed s/SEDENGELURL/$FQND/ |sed s/MD5SED/`openssl x509 -noout -fingerprint -md5 -in /etc/apache2/ssl/apache.pem|sed s/MD5\ Fingerprint\=//`/|sed s/SHA1SED/`openssl x509 -noout -fingerprint -sha1 -in /etc/apache2/ssl/apache.pem|sed s/SHA1\ Fingerprint\=//`/ >> /var/www/https/inc/config.php
	
	rm /var/www/https/inc/config_db.php
        cat `pwd`/engel-system/default-conf/www-ssl/inc/config_db.php|sed s/changeme/$SQL_PASSWD/ >> /var/www/https/inc/config_db.php
	
	cp `pwd`/engel-system/DB/User.sql `pwd`/engel-system/DB/User.sql2
	rm `pwd`/engel-system/DB/User.sql
	
	cat `pwd`/engel-system/DB/User.sql2|sed s/21232f297a57a5a743894a0e4a801fc3/`echo -n $ADM_PASSWD|md5sum|sed s/\ \ \-//`/ >> `pwd`/engel-system/DB/User.sql

	mysql tabel -u root < `pwd`/engel-system/DB/ChangeLog.sql
        mysql tabel -u root < `pwd`/engel-system/DB/Himmel.sql
        mysql tabel -u root < `pwd`/engel-system/DB/Messages.sql
        mysql tabel -u root < `pwd`/engel-system/DB/Sprache.sql
        mysql tabel -u root < `pwd`/engel-system/DB/User.sql
        mysql tabel -u root < `pwd`/engel-system/DB/UserCVS.sql
        mysql tabel -u root < `pwd`/engel-system/DB/UserPicture.sql

echo "cleaning up"
	rm -rf `pwd`/engel-system/
	mysql -u root mysql -e "UPDATE user SET Password=PASSWORD('$SQL_PASSWD') WHERE user='root';"
	mysql -u root mysql -e "FLUSH PRIVILEGES;"
	
	echo "SQL-User: root" >> /root/cfg.info
	echo "SQL-Pass: $SQL_PASSWD" >> /root/cfg.info
	echo "Web-User: admin" >> /root/cfg.info
	echo "Web-Pass: $ADM_PASSWD" >> /root/cfg.info

echo "final hints:"
echo "-the webfrontend user/pass combo is: admin:$ADM_PASSWD"
echo "-the sql-server uses root:$SQL_PASSWD"
echo "-you can find further information and the passwords in /root/cfg.info"
echo "-make sure \$url in /var/www/https/inc/config.php is correct"


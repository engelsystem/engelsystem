# How do I deploy Engelsystem on HEROKU
### Steps

* We need to install heroku on our machine. Type the following in your linux terminal:
	* ```wget -O- https://toolbelt.heroku.com/install-ubuntu.sh | sh```
  This installs the Heroku Toolbelt on your machine to access heroku from the command line.
* Next we need to login to our heroku server (assuming that you have already created an account). Type the following in the terminal:
	* ```heroku login```
    * Enter your credentials and login.
* Once logged in we need to create a space on the heroku server for our application. This is done with the following command
	* ```heroku create```
* Prepare the app
    * ```git clone --recursive https://github.com/fossasia/engelsystem.git```
    * ```cd engelsystem/```
* Once the app is ready, we need to create a composer.json in order to be recognized as a PHP application.We need to declare all app dependencies in composer.json
* Now PHP app is detected, we need to create a heroku app
    * ```$ heroku create```
* After creating heroku app, we need to migrate the database.
    * ```$ heroku addons:create cleardb:ignite```
* Now retrieve your new ClearDB database URL by issuing the following command:
    * ```$ heroku config | grep CLEARDB_DATABASE_URL```
    * ```CLEARDB_DATABASE_URL: mysql://bda37eff166954:69445d28@us-cdbr-iron-east-04.cleardb.net/heroku_3c94174e0cc6cd8?reconnect=true```
* Now we need to import the tables to the heroku database
    * ```$mysql -u bda37eff166954 -h us-cdbr-iron-east-04.cleardb.net -p heroku_3c94174e0cc6cd8```
    * ```mysql> source [path to engelsystem]/engelsystem/db/install.sql;```
    * ```mysql> source [path to engelsystem]/engelsystem/db/update.sql;```
    * ```mysql> exit;```
* Defining a Procfile. Your Procfile will contain the below line
    *  ```web: vendor/bin/heroku-php-apache2 public/```
* Now since we have defined the composer.json, Procfile and migrated the database.
* Then we deploy the code to heroku.
	* ```git push heroku master``` or
    * ```git push heroku yourbranch:master``` if you are in a different branch than master
* Now the app will be successfully deployed on heroku and can be viewed online.

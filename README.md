# What is Engelsystem

Engelsystem is a volunteer management system for events.

# Installation of Engelsystem
[![Code Climate](https://codeclimate.com/github/fossasia/engelsystem/badges/gpa.svg)](https://codeclimate.com/github/fossasia/engelsystem)
[![Build Status](https://travis-ci.org/fossasia/engelsystem.svg?branch=documentation)](https://travis-ci.org/fossasia/engelsystem)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/d56c5bb224f24946965770230e7253c2)](https://www.codacy.com/app/dishant-khanna1807/engelsystem_2?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=fossasia/engelsystem&amp;utm_campaign=Badge_Grade)
[![CircleCI](https://circleci.com/gh/fossasia/engelsystem/tree/development.svg?style=svg)](https://circleci.com/gh/fossasia/engelsystem/tree/development)
[![Dependency Status](https://www.versioneye.com/user/projects/577c9495b50608003eee0161/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/577c9495b50608003eee0161)
[![Dependency Status](https://gemnasium.com/badges/github.com/fossasia/engelsystem.svg)](https://gemnasium.com/github.com/fossasia/engelsystem)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fossasia/engelsystem/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fossasia/engelsystem/?branch=master)
[![Issue Count](https://codeclimate.com/github/fossasia/engelsystem/badges/issue_count.svg)](https://codeclimate.com/github/fossasia/engelsystem)
## Requirements:
 * PHP 5.4.x (cgi-fcgi)
 * MySQL-Server 5.5.x
 * Webserver, i.e. lighttpd, nginx, or Apache

## Directions:
 * Clone the master branch with the submodules: `git clone --recursive https://github.com/fossasia/engelsystem.git`
 * Webserver must have write access to the 'import' directory and read access for all other directories
 * Webserver must be public.

 * Recommended: Directory Listing should be disabled.
 * There must a be MySQL database created with a user who has full rights to that database.
 * It must be created by the db/install.sql and db/update.sql files.
 * If necessary, create a config/config.php to override values from config/config.default.php.
 * In the browser, login with credentials admin:asdfasdf and change the password.

Engelsystem can now be used.

## Session Settings:
 * Make sure the config allows for sessions.
 * Both Apache and Nginx allow for different VirtualHost configurations.

## Report Bugs

https://github.com/fossasia/engelsystem/issues


# Features

### Admin Features

##### News
-   Under this user can view the news.
-   admin can create new news.

##### Meetings
-   Under this page admin/user can view the meetings and time and information regarding the meetings.

##### AngelTypes
-   Under this page user can view the list of angeltypes and users subscribed.
-   admin can view and edit the existing angeltypes and even can add new angeltypes.

##### Ask an archangel
-   Here user/admin can ask questions to all the angels.

##### Shifts Page
-   Views
    -   *Map view* - In the shifts page user/admin can view the shifts in map view where rooms and  shifts according to their timings.
    -   *Normal view*  - when user/admin undo the new style if possible checkbox.
-   Edit shifts 
    -   There is a edit image or pencil image on the shifts when we press it admin will be redirected to Edit shifts page where admin can update the information by selecting update shifts option
-   Create new shifts from exisitng shifts
    -   Once admin selects the edit option admin can also create new shifts from exisiting shift data by selecting create new shift option
-   Delete shifts
    -   There is an image of bin . Where admin can delete the shifts . On pressing the button admin will be redirected to delete shifts page where we need ot confirm before deleting

##### Sending Message
-    User/admin can send messages to other angels or group members or to only members of angeltypes
by clicking th message icon     

#### Admin Pages
-   Arrived Angels
    -   Under this page admin can view all the angels who have arrived .
    -   we can also search for users in the search box .
    -   Admin can also view the statistics of arrival and departure statistics.
-   Active Angels
    -   Under this page admin can view the active angels
    -   Shirt statistics where admin can be know about the number of shirts needed of each size.
-   All Angels
    -   Under this page admin all the users registered their email, phone number, arrived , DECT etc information
    -   Admin can also search for particular user information in the search box
-   Free Angels
    -   Under this page admin can select angeltype and search for angels under the angeltype
-   Create Shifts
    -   Here admin can create new shifts.
    -   Admin can select number of angeltype needed , rooms for shifts and start date and end date for shifts
-   Answerer question
    -   Here admin can answer all the questions asked by all the angels.
-   Shifttypes
    -   Here admin can create new shift types , edit old shift types and delete existing shifts.
-   Rooms
    -   Here admin can add new rooms , edit and delete already created rooms.
-   Group Rights
    -   Here admin can view the description of the rights and edit the groups.
-   Frab import
    -   Here admin can import shifts and shifts . Admin needs to enter the add minutes to start and add minutes to end and import a xcal file
    -   Admin can also export user data by clicking the export button .
-   Log
    -   Under this page , admin can view all the activities and their created and modified time and who has made the changes
    -   Admin can even search for the entries
-  Settings
    -   Under this page, admin can add information regarding the Event name, BuildUp date, Event Start data, Event end data, Teardown end data, Event Welcome message.
- Import and Export User Data
    -   Here admin can export all the user database.
    -   Import User data from a excel file.

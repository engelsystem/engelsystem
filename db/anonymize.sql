update User set Nick=concat('User',UID), Name=concat('Name',UID), Vorname=concat('Prename',UID), `Alter`=0, Telefon='', DECT='', Handy='', email=concat('engel', UID, '@engelsystem.de'), jabber='', Hometown='';
update Messages set Text=concat('Message', id);
update News set Betreff=concat('Subject', ID), Text=concat('News', ID);
update NewsComments set Text=concat('Comment', ID);
update Questions set Question=concat('Question', QID), Answer=concat('Answer', QID);
update ShiftEntry set Comment='', freeload_comment='';
update ShiftTypes set name=concat('Shifttype',id), description='Description';
update AngelTypes set name=concat('Angeltype',id), description=concat('Description of angeltype',id);
TRUNCATE TABLE LogEntries;


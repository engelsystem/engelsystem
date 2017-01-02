
Shifts = window.Shifts || {}

Shifts.db = {}
Shifts.db.init = (done) ->
    Shifts.log 'init db'
    alasql 'CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem;
    ATTACH INDEXEDDB DATABASE engelsystem;', ->
        alasql 'USE engelsystem', ->
            # note: primkey doesn't work, see https://github.com/agershun/alasql/issues/566
            # 'CREATE TABLE IF NOT EXISTS Shifts (SID, title, start, shift_end, PRIMARY KEY (SID))'
            # alasq.promise is ALSO b0rken, wtf. welcome to callback hell...
            alasql 'CREATE TABLE IF NOT EXISTS Shifts (SID, title, shift_start, shift_end)', ->
                alasql 'CREATE TABLE IF NOT EXISTS User (UID, nick)', ->
                    alasql 'CREATE TABLE IF NOT EXISTS options (option_key, option_value)', ->
                        done()

Shifts.fetcher = {}
Shifts.fetcher.start = ->
    statements = [
        'INSERT INTO Shifts (SID, title, shift_start, shift_end, user_id) VALUES (1, "Testschicht", 1483182000, 1483189200, 1)'
        'INSERT INTO Shifts (SID, title, shift_start, shift_end, user_id) VALUES (2, "Testschicht", 1483189200, 1483196400, 1)'
        'INSERT INTO Shifts (SID, title, shift_start, shift_end, user_id) VALUES (3, "Access control", 1483189200, 1483196400, 2)'

        'INSERT INTO User (UID, nick) VALUES (1, "angel_one")'
        'INSERT INTO User (UID, nick) VALUES (2, "angel_two")'
    ]

    for s in statements
        Shifts.log s
        alasql s

Shifts.init = ->
    Shifts.log 'init'
    Shifts.db.init ->
        Shifts.log 'db initialized'
        Shifts.fetcher.start()

# document ready
$ ->
    Shifts.init()

Shifts.log = (msg) ->
    console.info msg



#alasql 'CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem;
#ATTACH INDEXEDDB DATABASE engelsystem;', ->
#    alasql 'USE engelsystem', ->
#        alasql 'CREATE TABLE IF NOT EXISTS Shifts (id int(11) NOT NULL AUTO_INCREMENT, title, start, shift_end, PRIMARY KEY (id))', ->
#            alasql.promise [
#                'INSERT INTO Shifts (id, title, start, shift_end) VALUES (1, "Testschicht", 1483182000, 1483189200)'
#                'INSERT INTO Shifts (id, title, start, shift_end) VALUES (2, "Testschicht", 1483189200, 1483196400)'
#                'INSERT INTO Shifts (id, title, start, shift_end) VALUES (3, "Access control", 1483189200, 1483196400)'
#            ]
#            .then 'SELECT * FROM Shifts', (res) ->
#                console.debug res
#
##            alasql 'SELECT * FROM Shifts', (res) ->
##                console.debug res
#

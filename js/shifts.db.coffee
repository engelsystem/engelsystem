
Shifts.db =
    room_ids: []
    user_ids: []
    shift_ids: []
    shiftentry_ids: []
    shifttype_ids: []

    init: (done) ->
        Shifts.log 'init db'
        alasql 'CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem;
        ATTACH INDEXEDDB DATABASE engelsystem;', ->
            alasql 'USE engelsystem', ->
                # note: primary key doesn't work, see https://github.com/agershun/alasql/issues/566
                alasql 'CREATE TABLE IF NOT EXISTS Shifts (SID INT, title, shifttype_id INT, shift_start INT, shift_end INT, RID INT);
                CREATE TABLE IF NOT EXISTS User (UID INT, nick);
                CREATE TABLE IF NOT EXISTS Room (RID INT, Name);
                CREATE TABLE IF NOT EXISTS ShiftEntry (id INT, SID INT, TID INT, UID INT);
                CREATE TABLE IF NOT EXISTS ShiftTypes (id INT, name, angeltype_id INT);
                CREATE TABLE IF NOT EXISTS options (option_key, option_value);', ->
                    Shifts.db.populate_ids ->
                        done()

    populate_ids: (done) ->

        # rooms
        alasql "SELECT RID from Room", (res) ->
            for r in res
                Shifts.db.room_ids.push r.RID

            # users
            alasql "SELECT UID from User", (res) ->
                for u in res
                    Shifts.db.user_ids.push u.UID

                # shift types
                alasql "SELECT id from ShiftTypes", (res) ->
                    for s in res
                        Shifts.db.shifttype_ids.push s.id

                    # shifts
                    alasql "SELECT SID from Shifts", (res) ->
                        for s in res
                            Shifts.db.shift_ids.push s.SID

                        # shift entries
                        alasql "SELECT id from ShiftEntry", (res) ->
                            for s in res
                                Shifts.db.shiftentry_ids.push s.id

                            done()

    insert_room: (room, done) ->
        room_exists = Shifts.db.room_ids.indexOf(parseInt(room.RID, 10)) > -1
        if room_exists == false
            alasql "INSERT INTO Room (RID, Name) VALUES (#{room.RID}, '#{room.Name}')", ->
                Shifts.db.room_ids.push room.RID
                done()
        else
            done()

    insert_user: (user, done) ->
        user_exists = Shifts.db.user_ids.indexOf(parseInt(user.UID, 10)) > -1
        if user_exists == false
            alasql "INSERT INTO User (UID, Nick) VALUES (#{user.UID}, '#{user.Nick}')", ->
                Shifts.db.user_ids.push user.UID
                done()
        else
            done()

    insert_shift: (shift, done) ->
        # Debug note: Array.indexOf may need a polyfill for older browsers
        # https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/indexOf
        shift_exists = Shifts.db.shift_ids.indexOf(parseInt(shift.SID, 10)) > -1
        if shift_exists == false
            alasql "INSERT INTO Shifts (SID, title, shifttype_id, shift_start, shift_end, RID) VALUES (#{shift.SID}, '#{shift.title}', '#{shift.shifttype_id}', '#{shift.start}', '#{shift.end}', '#{shift.RID}')", ->
                Shifts.db.shift_ids.push shift.SID
                done()
        else
            done()

    insert_shiftentry: (shiftentry, done) ->
        shiftentry_exists = Shifts.db.shiftentry_ids.indexOf(parseInt(shiftentry.id, 10)) > -1
        if shiftentry_exists == false
            alasql "INSERT INTO ShiftEntry (id, SID, TID, UID) VALUES (#{shiftentry.id}, '#{shiftentry.SID}', '#{shiftentry.TID}', '#{shiftentry.UID}')", ->
                Shifts.db.shiftentry_ids.push shiftentry.id
                done()
        else
            done()

    insert_shifttype: (shifttype, done) ->
        shifttype.id = parseInt shifttype.id, 10
        shifttype_exists = Shifts.db.shifttype_ids.indexOf(shifttype.id) > -1
        if shifttype_exists == false
            alasql "INSERT INTO ShiftTypes (id, name) VALUES (#{shifttype.id}, '#{shifttype.name}')", ->
                Shifts.db.shifttype_ids.push shifttype.id
                done()
        else
            done()

    get_my_shifts: (done) ->
        #alasql "SELECT * FROM ShiftEntry LEFT JOIN User ON ShiftEntry.UID = User.UID LEFT JOIN Shifts ON ShiftEntry.SID = Shifts.SID", (res) ->
        rand = 1 + parseInt(Math.random() * 10, 10)
        rand = 2000

        start_time = moment(moment().format('YYYY-MM-DD')).format('X')
        start_time = parseInt start_time, 10
        start_time = start_time - Shifts.render.TIME_MARGIN
        end_time = start_time + 24*60*60

        alasql "SELECT Shifts.SID, Shifts.title as shift_title, Shifts.shifttype_id, Shifts.shift_start, Shifts.shift_end, Shifts.RID,
            ShiftTypes.name as shifttype_name,
            Room.Name as room_name
            FROM Shifts
            LEFT JOIN ShiftTypes ON ShiftTypes.id = Shifts.shifttype_id
            LEFT JOIN Room ON Room.RID = Shifts.RID
            WHERE Shifts.shift_start >= #{start_time} AND Shifts.shift_end <= #{end_time}
            LIMIT #{rand}", (res) ->
            done res

    get_rooms: (done) ->
        alasql "SELECT * FROM Room", (res) ->
            done res



Shifts.db =

    prefix: 'noname'
    websql: {} # this will be the db instance
    current_user: {} # stores user_id and user's angeltypes

    init: (done) ->

        # get db prefix
        try
            Shifts.db.prefix = '_' + Shifts.db.slugify( $('.footer').html().split('<br>')[0] )
        catch
            Shifts.db.prefix = 'noname'

        Shifts.log 'init db'
        Shifts.db.websql = openDatabase 'engelsystem' + Shifts.db.prefix, '1.0', '', 10*1024*1024
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'CREATE TABLE IF NOT EXISTS Shifts (SID unique, title, shifttype_id INT, start_time INT, end_time INT, RID INT)'
            t.executeSql 'CREATE TABLE IF NOT EXISTS User (UID unique, nick)'
            t.executeSql 'CREATE TABLE IF NOT EXISTS Room (RID unique, Name)'
            t.executeSql 'CREATE TABLE IF NOT EXISTS ShiftEntry (id unique, SID INT, TID INT, UID INT, freeloaded INT)'
            t.executeSql 'CREATE TABLE IF NOT EXISTS ShiftTypes (id unique, name, angeltype_id INT)'
            t.executeSql 'CREATE TABLE IF NOT EXISTS AngelTypes (id unique, name, restricted INT, no_self_signup INT)'
            t.executeSql 'CREATE TABLE IF NOT EXISTS NeededAngelTypes (id unique, room_id INT, shift_id INT, angel_type_id INT, angel_count INT)'
            t.executeSql 'CREATE TABLE IF NOT EXISTS options (option_key unique, option_value)'
            Shifts.db.populate_ids ->
                done()

    slugify: (text) ->
        return text.toString().toLowerCase()
        .replace(/^[\s|\-|_]+/, '')     # Trim start
        .replace(/[\s|\-|_]+$/, '')     # Trim end
        .replace(/\s+/g, '_')           # Replace spaces with _
        .replace(/__+/g, '_')           # Replace multiple _ with single _
        .replace(/[^\w\-]+/g, '')       # Remove all non-word chars

    object_to_array: (obj) ->
        arr = []
        for o in obj
            arr.push o
        return arr

    populate_ids: (done) ->
        # rooms
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'SELECT RID from Room', [], (t, res) ->
                for r in res.rows
                    # populate select filter
                    Shifts.interaction.selected_rooms.push r.RID

                # angel types
                t.executeSql 'SELECT id from AngelTypes', [], (t, res) ->
                    for a in res
                        # populate select filter
                        Shifts.interaction.selected_angeltypes.push a.id

                    # user
                    user_id = parseInt $('#shiftplan').data('user_id'), 10

                    # store user_id
                    Shifts.db.current_user.id = user_id

                    # store arrived status
                    t.executeSql 'SELECT UID FROM User WHERE UID = ?', [user_id], (t, res) ->
                        Shifts.db.current_user.arrived = res.rows.length > 0

                        # store angeltypes
                        Shifts.db.current_user.angeltypes = [4] #todo (provide it via html)

                        done()

    insert_room: (room, done) ->
        room.RID = parseInt(room.RID, 10)
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'INSERT INTO Room (RID, Name) VALUES (?, ?)', [room.RID, room.Name]
            # populate select filter
            Shifts.interaction.selected_rooms.push room.RID
            done()

    insert_user: (user, done) ->
        user.UID = parseInt(user.UID, 10)
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'INSERT INTO User (UID, Nick) VALUES (?, ?)', [user.UID, user.Nick]
            done()

    insert_shift: (shift, done) ->
        shift.SID = parseInt(shift.SID, 10)
        shift.RID = parseInt(shift.RID, 10)
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'INSERT INTO Shifts (SID, title, shifttype_id, start_time, end_time, RID) VALUES (?, ?, ?, ?, ?, ?)', [shift.SID, shift.title, shift.shifttype_id, shift.start, shift.end, shift.RID]
            done()

    insert_shiftentry: (shiftentry, done) ->
        shiftentry.id = parseInt shiftentry.id, 10
        shiftentry.SID = parseInt shiftentry.SID, 10
        shiftentry.TID = parseInt shiftentry.TID, 10
        shiftentry.UID = parseInt shiftentry.UID, 10
        shiftentry.freeloaded = parseInt shiftentry.freeloaded, 10
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'INSERT INTO ShiftEntry (id, SID, TID, UID, freeloaded) VALUES (?, ?, ?, ?, ?)', [shiftentry.id, shiftentry.SID, shiftentry.TID, shiftentry.UID, shiftentry.freeloaded], ->
            done()

    insert_shifttype: (shifttype, done) ->
        shifttype.id = parseInt shifttype.id, 10
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'INSERT INTO ShiftTypes (id, name) VALUES (?, ?)', [shifttype.id, shifttype.name]
            done()

    insert_angeltype: (angeltype, done) ->
        angeltype.id = parseInt angeltype.id, 10
        angeltype.restricted = parseInt angeltype.restricted, 10
        angeltype.no_self_signup = parseInt angeltype.no_self_signup, 10
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'INSERT INTO AngelTypes (id, name, restricted, no_self_signup) VALUES (?, ?, ?, ?)', [angeltype.id, angeltype.name, angeltype.restricted, angeltype.no_self_signup], ->
                # populate select filter
                Shifts.interaction.selected_angeltypes.push angeltype.id
            done()

    insert_needed_angeltype: (needed_angeltype, done) ->
        needed_angeltype.id = parseInt needed_angeltype.id, 10
        needed_angeltype.RID = parseInt(needed_angeltype.RID, 10) || null
        needed_angeltype.SID = parseInt(needed_angeltype.SID, 10) || null
        needed_angeltype.ATID = parseInt needed_angeltype.ATID, 10
        needed_angeltype.count = parseInt needed_angeltype.count, 10
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'INSERT INTO NeededAngelTypes (id, room_id, shift_id, angel_type_id, angel_count) VALUES (?, ?, ?, ?, ?)', [needed_angeltype.id, needed_angeltype.RID, needed_angeltype.SID, needed_angeltype.ATID, needed_angeltype.count], ->
            done()

    get_shifts: (filter_rooms, filter_angeltypes, done) ->
        filter_rooms_ids = filter_rooms.join ','
        filter_angeltypes_ids = filter_angeltypes.join ','
        start_time = Shifts.render.get_starttime()
        end_time = Shifts.render.get_endtime()

        Shifts.db.websql.transaction (t) ->
            # not as prepared statement because the "in (?)" hiccups
            t.executeSql "SELECT DISTINCT Shifts.SID, Shifts.title as shift_title, Shifts.shifttype_id, Shifts.start_time, Shifts.end_time, Shifts.RID,
            ShiftTypes.name as shifttype_name,
            Room.Name as room_name
            FROM NeededAngelTypes
            JOIN Shifts ON Shifts.SID = NeededAngelTypes.shift_id
            JOIN Room ON Room.RID = Shifts.RID
            JOIN ShiftTypes ON ShiftTypes.id = Shifts.shifttype_id
            WHERE NeededAngelTypes.angel_count > 0
            AND Shifts.start_time >= #{start_time} AND Shifts.end_time <= #{end_time}
            AND Shifts.RID IN (#{filter_rooms_ids})
            AND NeededAngelTypes.angel_type_id IN (#{filter_angeltypes_ids})
            ORDER BY Shifts.start_time, Shifts.SID", [], (t, res) ->
                r = Shifts.db.object_to_array res.rows
                done r

    get_angeltypes_needed: (done) ->
        start_time = Shifts.render.get_starttime()
        end_time = Shifts.render.get_endtime()

        Shifts.db.websql.transaction (t) ->
            t.executeSql 'SELECT DISTINCT NeededAngelTypes.shift_id, NeededAngelTypes.angel_type_id, NeededAngelTypes.angel_count, AngelTypes.name, AngelTypes.restricted, AngelTypes.no_self_signup
            FROM NeededAngelTypes
            JOIN Shifts ON NeededAngelTypes.shift_id = Shifts.SID
            JOIN AngelTypes ON NeededAngelTypes.angel_type_id = AngelTypes.id
            WHERE Shifts.start_time >= ? AND Shifts.end_time <= ?
            AND NeededAngelTypes.angel_count > 0
            ORDER BY NeededAngelTypes.shift_id', [start_time, end_time], (t, res) ->
                r = Shifts.db.object_to_array res.rows
                done r

    get_shiftentries: (done) ->
        start_time = Shifts.render.get_starttime()
        end_time = Shifts.render.get_endtime()

        Shifts.db.websql.transaction (t) ->
            t.executeSql 'SELECT DISTINCT ShiftEntry.SID, ShiftEntry.TID, ShiftEntry.UID, User.Nick as Nick, AngelTypes.name as at_name
            FROM ShiftEntry
            JOIN User ON ShiftEntry.UID = User.UID
            JOIN Shifts ON ShiftEntry.SID = Shifts.SID
            JOIN AngelTypes ON ShiftEntry.TID = AngelTypes.id
            WHERE Shifts.start_time >= ? AND Shifts.end_time <= ?
            ORDER BY ShiftEntry.SID', [start_time, end_time], (t, res) ->
                r = Shifts.db.object_to_array res.rows
                done r

    get_usershifts: (user_id, done) ->
        # optional (performance?): restrict to current dateselection
        #start_time = Shifts.render.get_starttime()
        #end_time = Shifts.render.get_endtime()

        Shifts.db.websql.transaction (t) ->
            t.executeSql 'SELECT DISTINCT ShiftEntry.SID, ShiftEntry.TID, Shifts.start_time, Shifts.end_time
            FROM ShiftEntry
            JOIN Shifts ON ShiftEntry.SID = Shifts.SID
            WHERE ShiftEntry.UID = ?
            ORDER BY ShiftEntry.SID', [user_id], (t, res) ->
                r = Shifts.db.object_to_array res.rows
                done r

    get_shift_range: (done) ->
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'SELECT start_time
            FROM Shifts
            ORDER BY start_time ASC
            LIMIT 1', [], (t, res) ->
                if res.rows.length > 0
                    start_time = res.rows[0].start_time
                    t.executeSql 'SELECT end_time
                    FROM Shifts
                    ORDER BY end_time DESC
                    LIMIT 1', [], (t, res) ->
                        end_time = res.rows[0].end_time
                        done [start_time, end_time]
                else
                    now = new Date()
                    done [now, now]

    get_rooms: (done) ->
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'SELECT * FROM Room ORDER BY Name', [], (t, res) ->
                r = Shifts.db.object_to_array res.rows
                done r

    get_angeltypes: (done) ->
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'SELECT * FROM AngelTypes ORDER BY name', [], (t, res) ->
                r = Shifts.db.object_to_array res.rows
                done r

    get_option: (key, done) ->
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'SELECT option_value FROM options WHERE option_key = ? LIMIT 1', [key], (t, res) ->
                try
                    done res.rows[0].option_value
                catch
                    done false

    set_option: (key, value, done) ->
        Shifts.db.websql.transaction (t) ->
            t.executeSql 'DELETE FROM options WHERE option_key = ?', [key], ->
                Shifts.db.websql.transaction (t2) ->
                    t2.executeSql 'INSERT INTO options (option_key, option_value) VALUES (?, ?)', [key, value], ->
                        done()


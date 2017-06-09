
Shifts.db =
    prefix: ''
    websql: {} # this will be the db instance

    init: (done) ->

        # get db prefix
        try
            Shifts.db.prefix = '_' + Shifts.db.slugify( $('.footer').html().split('<br>')[0] )
        catch
            Shifts.db.prefix = ''

        Shifts.log 'init db'
        Shifts.db.websql = openDatabase "engelsystem" + Shifts.db.prefix, "1.0", "", 10*1024*1024
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "CREATE TABLE IF NOT EXISTS Shifts (SID unique, title, shifttype_id INT, start_time INT, end_time INT, RID INT)"
            tx.executeSql "CREATE TABLE IF NOT EXISTS User (UID unique, nick)"
            tx.executeSql "CREATE TABLE IF NOT EXISTS Room (RID unique, Name)"
            tx.executeSql "CREATE TABLE IF NOT EXISTS ShiftEntry (id unique, SID INT, TID INT, UID INT)"
            tx.executeSql "CREATE TABLE IF NOT EXISTS ShiftTypes (id unique, name, angeltype_id INT)"
            tx.executeSql "CREATE TABLE IF NOT EXISTS AngelTypes (id unique, name)"
            tx.executeSql "CREATE TABLE IF NOT EXISTS NeededAngelTypes (id unique, room_id INT, shift_id INT, angel_type_id INT, angel_count INT)"
            tx.executeSql "CREATE TABLE IF NOT EXISTS options (option_key, option_value)"
            Shifts.db.populate_ids ->
                done()

    slugify: (text) ->
        return text.toString().toLowerCase()
        .replace(/^[\s|\-|_]+/, '')     # Trim start
        .replace(/[\s|\-|_]+$/, '')     # Trim end
        .replace(/\s+/g, '_')           # Replace spaces with _
        .replace(/__+/g, '_')           # Replace multiple _ with single _
        .replace(/[^\w\-]+/g, '')       # Remove all non-word chars

    populate_ids: (done) ->
        # rooms
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "SELECT RID from Room", [], (tx, res) ->
                for r in res.rows
                    # populate select filter
                    Shifts.interaction.selected_rooms.push r.RID

            # angel types
            tx.executeSql "SELECT id from AngelTypes", [], (tx, res) ->
                for a in res
                    # populate select filter
                    Shifts.interaction.selected_angeltypes.push a.id

            done()

    insert_room: (room, done) ->
        room.RID = parseInt(room.RID, 10)
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "INSERT INTO Room (RID, Name) VALUES (?, ?)", [room.RID, room.Name]
            # populate select filter
            Shifts.interaction.selected_rooms.push room.RID
            done()

    insert_user: (user, done) ->
        user.UID = parseInt(user.UID, 10)
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "INSERT INTO User (UID, Nick) VALUES (?, ?)", [user.UID, user.Nick]
            done()

    insert_shift: (shift, done) ->
        shift.SID = parseInt(shift.SID, 10)
        shift.RID = parseInt(shift.RID, 10)
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "INSERT INTO Shifts (SID, title, shifttype_id, start_time, end_time, RID) VALUES (?, ?, ?, ?, ?, ?)", [shift.SID, shift.title, shift.shifttype_id, shift.start, shift.end, shift.RID]
            done()

    insert_shiftentry: (shiftentry, done) ->
        shiftentry.id = parseInt shiftentry.id, 10
        shiftentry.SID = parseInt shiftentry.SID, 10
        shiftentry.TID = parseInt shiftentry.TID, 10
        shiftentry.UID = parseInt shiftentry.UID, 10
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "INSERT INTO ShiftEntry (id, SID, TID, UID) VALUES (?, ?, ?, ?)", [shiftentry.id, shiftentry.SID, shiftentry.TID, shiftentry.UID], ->
            done()

    insert_shifttype: (shifttype, done) ->
        shifttype.id = parseInt shifttype.id, 10
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "INSERT INTO ShiftTypes (id, name) VALUES (?, ?)", [shifttype.id, shifttype.name]
            done()

    insert_angeltype: (angeltype, done) ->
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "INSERT INTO AngelTypes (id, name) VALUES (?, ?)", [angeltype.id, angeltype.name], ->
                # populate select filter
                Shifts.interaction.selected_angeltypes.push angeltype.id
            # TODO (für alle inserts): timer, falls done nach Xms nicht ausgeführt wurde, ausführen.
            done()

    insert_needed_angeltype: (needed_angeltype, done) ->
        needed_angeltype.id = parseInt needed_angeltype.id, 10
        needed_angeltype.RID = parseInt(needed_angeltype.RID, 10) || null
        needed_angeltype.SID = parseInt(needed_angeltype.SID, 10) || null
        needed_angeltype.ATID = parseInt needed_angeltype.ATID, 10
        needed_angeltype.count = parseInt needed_angeltype.count, 10
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "INSERT INTO NeededAngelTypes (id, room_id, shift_id, angel_type_id, angel_count) VALUES (?, ?, ?, ?, ?)", [needed_angeltype.id, needed_angeltype.RID, needed_angeltype.SID, needed_angeltype.ATID, needed_angeltype.count], ->
            done()

    get_shifts: (filter_rooms, filter_angeltypes, done) ->
        filter_rooms_ids = filter_rooms.join ','
        filter_angeltypes_ids = filter_angeltypes.join ','
        start_time = Shifts.render.get_starttime()
        end_time = Shifts.render.get_endtime()

        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "SELECT DISTINCT Shifts.SID, Shifts.title as shift_title, Shifts.shifttype_id, Shifts.start_time, Shifts.end_time, Shifts.RID,
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
            ORDER BY Shifts.start_time, Shifts.SID", [], (tx, res) ->
                done res.rows

    get_angeltypes_needed: (done) ->
        start_time = Shifts.render.get_starttime()
        end_time = Shifts.render.get_endtime()

        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "SELECT DISTINCT NeededAngelTypes.shift_id, NeededAngelTypes.angel_type_id, NeededAngelTypes.angel_count, AngelTypes.name
            FROM NeededAngelTypes
            JOIN Shifts ON NeededAngelTypes.shift_id = Shifts.SID
            JOIN AngelTypes ON NeededAngelTypes.angel_type_id = AngelTypes.id
            WHERE Shifts.start_time >= #{start_time} AND Shifts.end_time <= #{end_time}
            AND NeededAngelTypes.angel_count > 0
            ORDER BY NeededAngelTypes.shift_id", [], (tx, res) ->
                done res.rows

    get_shiftentries: (done) ->
        start_time = Shifts.render.get_starttime()
        end_time = Shifts.render.get_endtime()

        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "SELECT DISTINCT ShiftEntry.SID, ShiftEntry.TID, ShiftEntry.UID, User.Nick, AngelTypes.name as at_name
            FROM ShiftEntry
            JOIN User ON ShiftEntry.UID = User.UID
            JOIN Shifts ON ShiftEntry.SID = Shifts.SID
            JOIN AngelTypes ON ShiftEntry.TID = AngelTypes.id
            WHERE Shifts.start_time >= #{start_time} AND Shifts.end_time <= #{end_time}
            ORDER BY ShiftEntry.SID", [], (tx, res) ->
                done res.rows

    get_usershifts: (user_id, done) ->
        # optional (performance?): restrict to current dateselection
        #start_time = Shifts.render.get_starttime()
        #end_time = Shifts.render.get_endtime()

        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "SELECT DISTINCT ShiftEntry.SID, ShiftEntry.TID, Shifts.start_time, Shifts.end_time
            FROM ShiftEntry
            JOIN Shifts ON ShiftEntry.SID = Shifts.SID
            WHERE ShiftEntry.UID = #{user_id}
            ORDER BY ShiftEntry.SID", [], (tx, res) ->
                done res.rows

    get_shift_range: (done) ->
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "SELECT start_time
            FROM Shifts
            ORDER BY start_time ASC
            LIMIT 1", [], (tx, res) ->
                if res.rows.length > 0
                    start_time = res.rows[0].start_time
                    tx.executeSql "SELECT end_time
                    FROM Shifts
                    ORDER BY end_time DESC
                    LIMIT 1", [], (tx, res) ->
                        end_time = res.rows[0].end_time
                        done [start_time, end_time]
                else
                    now = new Date()
                    done [now, now]

    get_rooms: (done) ->
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "SELECT * FROM Room ORDER BY Name", [], (tx, res) ->
                done res.rows

    get_angeltypes: (done) ->
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "SELECT * FROM AngelTypes ORDER BY name", [], (tx, res) ->
                done res.rows

    get_option: (key, done) ->
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "SELECT option_value FROM options WHERE option_key = ? LIMIT 1", [key], (tx, res) ->
                try
                    done res.rows[0].option_value
                catch
                    done false

    set_option: (key, value, done) ->
        Shifts.db.websql.transaction (tx) ->
            tx.executeSql "INSERT INTO options (option_key, option_value) VALUES (?, ?)", [key, value], ->
                done()
        #else
        #    alasql "UPDATE options SET option_value = ? WHERE option_key = ?", [value, key], ->
        #        done()


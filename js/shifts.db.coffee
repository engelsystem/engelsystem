
Shifts.db =
    room_ids: []
    user_ids: []
    shift_ids: []
    shiftentry_ids: []
    shifttype_ids: []
    angeltype_ids: []
    needed_angeltype_ids: []
    prefix: ''

    init: (done) ->

        # get db prefix
        try
            Shifts.db.prefix = '_' + Shifts.db.slugify( $('.footer').html().split('<br>')[0] )
        catch
            Shifts.db.prefix = ''

        Shifts.log 'init db'
        alasql 'CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem' + Shifts.db.prefix + ';
        ATTACH INDEXEDDB DATABASE engelsystem' + Shifts.db.prefix + ';', ->
            alasql 'USE engelsystem' + Shifts.db.prefix + ';', ->
                # note: primary key doesn't work, see https://github.com/agershun/alasql/issues/566
                alasql 'CREATE TABLE IF NOT EXISTS Shifts (SID INT, title, shifttype_id INT, start_time INT, end_time INT, RID INT);
                CREATE TABLE IF NOT EXISTS User (UID INT, nick);
                CREATE TABLE IF NOT EXISTS Room (RID INT, Name);
                CREATE TABLE IF NOT EXISTS ShiftEntry (id INT, SID INT, TID INT, UID INT);
                CREATE TABLE IF NOT EXISTS ShiftTypes (id INT, name, angeltype_id INT);
                CREATE TABLE IF NOT EXISTS AngelTypes (id INT, name);
                CREATE TABLE IF NOT EXISTS NeededAngelTypes (id INT, room_id INT, shift_id INT, angel_type_id INT, angel_count INT);
                CREATE TABLE IF NOT EXISTS options (option_key, option_value);', ->
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
        alasql "SELECT RID from Room", (res) ->
            for r in res
                Shifts.db.room_ids.push r.RID
                # populate select filter
                Shifts.interaction.selected_rooms.push r.RID

            # users
            alasql "SELECT UID from User", (res) ->
                for u in res
                    Shifts.db.user_ids.push u.UID

                # shift types
                alasql "SELECT id from ShiftTypes", (res) ->
                    for s in res
                        Shifts.db.shifttype_ids.push s.id

                    # angel types
                    alasql "SELECT id from AngelTypes", (res) ->
                        for a in res
                            Shifts.db.angeltype_ids.push a.id
                            # populate select filter
                            Shifts.interaction.selected_angeltypes.push a.id

                        # needed angel types
                        alasql "SELECT id from NeededAngelTypes", (res) ->
                            for a in res
                                Shifts.db.needed_angeltype_ids.push a.id

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
        room.RID = parseInt(room.RID, 10)
        room_exists = room.RID in Shifts.db.room_ids
        if room_exists == false
            alasql "INSERT INTO Room (RID, Name) VALUES (#{room.RID}, '#{room.Name}')", ->
                Shifts.db.room_ids.push room.RID
                # populate select filter
                Shifts.interaction.selected_rooms.push room.RID
                done()
        else
            done()

    insert_user: (user, done) ->
        user.UID = parseInt(user.UID, 10)
        user_exists = user.UID in Shifts.db.user_ids
        if user_exists == false
            alasql "INSERT INTO User (UID, Nick) VALUES (#{user.UID}, '#{user.Nick}')", ->
                Shifts.db.user_ids.push user.UID
                done()
        else
            done()

    insert_shift: (shift, done) ->
        shift.SID = parseInt(shift.SID, 10)
        shift.RID = parseInt(shift.RID, 10)
        shift_exists = shift.SID in Shifts.db.shift_ids
        if shift_exists == false
            alasql "INSERT INTO Shifts (SID, title, shifttype_id, start_time, end_time, RID) VALUES (#{shift.SID}, '#{shift.title}', '#{shift.shifttype_id}', '#{shift.start}', '#{shift.end}', #{shift.RID})", ->
                Shifts.db.shift_ids.push shift.SID
                done()
        else
            done()

    insert_shiftentry: (shiftentry, done) ->
        shiftentry.id = parseInt shiftentry.id, 10
        shiftentry.SID = parseInt shiftentry.SID, 10
        shiftentry.TID = parseInt shiftentry.TID, 10
        shiftentry.UID = parseInt shiftentry.UID, 10
        shiftentry_exists = shiftentry.id in Shifts.db.shiftentry_ids
        if shiftentry_exists == false
            alasql "INSERT INTO ShiftEntry (id, SID, TID, UID) VALUES (#{shiftentry.id}, #{shiftentry.SID}, #{shiftentry.TID}, #{shiftentry.UID})", ->
                Shifts.db.shiftentry_ids.push shiftentry.id
                done()
        else
            done()

    insert_shifttype: (shifttype, done) ->
        shifttype.id = parseInt shifttype.id, 10
        shifttype_exists = shifttype.id in Shifts.db.shifttype_ids
        if shifttype_exists == false
            alasql "INSERT INTO ShiftTypes (id, name) VALUES (#{shifttype.id}, '#{shifttype.name}')", ->
                Shifts.db.shifttype_ids.push shifttype.id
                done()
        else
            done()

    insert_angeltype: (angeltype, done) ->
        angeltype.id = parseInt angeltype.id, 10
        angeltype_exists = angeltype.id in Shifts.db.angeltype_ids
        if angeltype_exists == false
            alasql "INSERT INTO AngelTypes (id, name) VALUES (#{angeltype.id}, '#{angeltype.name}')", ->
                Shifts.db.angeltype_ids.push angeltype.id
                # populate select filter
                Shifts.interaction.selected_angeltypes.push angeltype.id
                done()
        else
            done()

    insert_needed_angeltype: (needed_angeltype, done) ->
        needed_angeltype.id = parseInt needed_angeltype.id, 10
        needed_angeltype.RID = parseInt(needed_angeltype.RID, 10) || null
        needed_angeltype.SID = parseInt(needed_angeltype.SID, 10) || null
        needed_angeltype.ATID = parseInt needed_angeltype.ATID, 10
        needed_angeltype.count = parseInt needed_angeltype.count, 10
        needed_angeltype_exists = needed_angeltype.id in Shifts.db.needed_angeltype_ids
        if needed_angeltype_exists == false
            alasql "INSERT INTO NeededAngelTypes (id, room_id, shift_id, angel_type_id, angel_count) VALUES (#{needed_angeltype.id}, #{needed_angeltype.RID}, #{needed_angeltype.SID}, #{needed_angeltype.ATID}, #{needed_angeltype.count})", ->
                Shifts.db.needed_angeltype_ids.push needed_angeltype.id
                done()
        else
            done()

    get_shifts: (filter_rooms, filter_angeltypes, done) ->
        filter_rooms_ids = filter_rooms.join ','
        filter_angeltypes_ids = filter_angeltypes.join ','
        start_time = Shifts.render.get_starttime()
        end_time = Shifts.render.get_endtime()

        alasql "SELECT DISTINCT Shifts.SID, Shifts.title as shift_title, Shifts.shifttype_id, Shifts.start_time, Shifts.end_time, Shifts.RID,
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
        ORDER BY Shifts.start_time, Shifts.SID", (res) ->
            done res

    get_rooms: (done) ->
        alasql "SELECT * FROM Room ORDER BY Name", (res) ->
            done res

    get_angeltypes: (done) ->
        alasql "SELECT * FROM AngelTypes ORDER BY name", (res) ->
            done res


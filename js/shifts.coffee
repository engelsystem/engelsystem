
Shifts = window.Shifts || {
    db:
        room_ids: []
        user_ids: []
        shift_ids: []

        init: (done) ->
            Shifts.log 'init db'
            alasql 'CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem;
            ATTACH INDEXEDDB DATABASE engelsystem;', ->
                alasql 'USE engelsystem', ->
                    # note: primkey doesn't work, see https://github.com/agershun/alasql/issues/566
                    # 'CREATE TABLE IF NOT EXISTS Shifts (SID, title, start, shift_end, PRIMARY KEY (SID))'
                    # alasq.promise is ALSO b0rken, wtf. welcome to callback hell...
                    alasql 'CREATE TABLE IF NOT EXISTS Shifts (SID, title, shift_start, shift_end)', ->
                        alasql 'CREATE TABLE IF NOT EXISTS User (UID, nick)', ->
                            alasql 'CREATE TABLE IF NOT EXISTS Room (RID, Name)', ->
                                alasql 'CREATE TABLE IF NOT EXISTS options (option_key, option_value)', ->
                                    Shifts.db.populate_ids ->
                                        done()

        populate_ids: (done) ->

            # Rooms
            alasql "SELECT RID from Room", (res) ->
                for r in res
                    Shifts.db.room_ids.push r.RID

                # Users
                alasql "SELECT UID from User", (res) ->
                    for u in res
                        Shifts.db.user_ids.push u.UID

                    # shifts
                    alasql "SELECT SID from Shifts", (res) ->
                        for s in res
                            Shifts.db.shift_ids.push s.SID

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
                alasql "INSERT INTO Shifts (SID, title, shift_start, shift_end) VALUES (#{shift.SID}, '#{shift.title}', '#{shift.start}', '#{shift.end}')", ->
                    Shifts.db.shift_ids.push shift.SID
                    done()
            else
                done()

    fetcher:
        start: ->
            url = '?p=shifts_json_export_websql'
            $.get url, (data) ->

                # insert rooms
                rooms = data.rooms
                Shifts.fetcher.process Shifts.db.insert_room, rooms, ->
                    Shifts.log 'processing rooms done'

                # insert users
                users = data.users
                Shifts.fetcher.process Shifts.db.insert_user, users, ->
                    Shifts.log 'processing users done'

                # insert shifts
                shifts = data.shifts
                Shifts.fetcher.process Shifts.db.insert_shift, shifts, ->
                    Shifts.log 'processing shifts done'

        process: (processing_func, items_to_process, done) ->
            item = items_to_process.shift()
            processing_func item, ->
                if items_to_process.length > 0
                    Shifts.fetcher.process processing_func, items_to_process, done
                else
                    done()

    init: ->
        Shifts.log 'init'
        Shifts.db.init ->
            Shifts.log 'db initialized'
            Shifts.fetcher.start()

    log: (msg) ->
        console.info msg

}



# document ready
$ ->
    Shifts.init()



Shifts = window.Shifts || {}

Shifts =
    db:
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
                                    done()

        insert_room: (room_id, name, done) ->
            alasql "SELECT RID from Room WHERE RID = #{room_id}", (res) ->
                try
                    room_exists = res[0].RID != room_id
                catch
                    room_exists = false

                if room_exists == false
                    alasql "INSERT INTO Room (RID, Name) VALUES (#{room_id}, '#{name}')", ->
                        done()
                else
                    done()

    fetcher:
        start: ->
            url = '?p=shifts_json_export_websql'
            $.get url, (data) ->
                rooms = data.rooms
                Shifts.fetcher.process rooms, ->
                    Shifts.log 'processing rooms done'

        process: (rooms, done) ->
            room = rooms.shift()
            Shifts.db.insert_room room.RID, room.Name, ->
                if rooms.length > 0
                    Shifts.fetcher.process rooms, done
                else
                    done()

    init: ->
        Shifts.log 'init'
        Shifts.db.init ->
            Shifts.log 'db initialized'
            Shifts.fetcher.start()



# document ready
$ ->
    Shifts.init()

Shifts.log = (msg) ->
    console.info msg


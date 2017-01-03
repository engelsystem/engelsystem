
Shifts = window.Shifts || {
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

        insert_room: (room, done) ->
            alasql "SELECT RID from Room WHERE RID = #{room.RID}", (res) ->
                try
                    room_exists = res[0].RID != room.RID
                catch
                    room_exists = false

                if room_exists == false
                    alasql "INSERT INTO Room (RID, Name) VALUES (#{room.RID}, '#{room.Name}')", ->
                        done()
                else
                    done()

    fetcher:
        start: ->
            url = '?p=shifts_json_export_websql'
            $.get url, (data) ->
                rooms = data.rooms
                Shifts.fetcher.process Shifts.db.insert_room, rooms, ->
                    Shifts.log 'processing rooms done'

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


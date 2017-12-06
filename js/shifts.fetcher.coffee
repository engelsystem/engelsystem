
Shifts.fetcher =

    total_objects_count: 0
    total_objects_count_since_start: 0
    remaining_objects_count: 0
    run_count: 0 # this is a rough evaluation how many fetcherruns happened.
                        # if > 0, decrease every second. stop fetcher if > 10 (runbuffersize)
                        # tl;dr: prevent hammering on the server if something in the fetcher fails and too many requests would happen
    runbuffersize: 10

    start: (display_status, done) ->
        if display_status
            Shifts.$shiftplan.html Shifts.templates.fetcher_status
        Shifts.fetcher.fetch_in_parts ->
            done()

        # background fetch every 5 mins,
        # to keep incremental updates low #user_experience
        setInterval ->
            Shifts.fetcher.fetch_in_parts ->
                #Shifts.render.shiftplan()
        , 5 * 60 * 1000

        # decrease run_count every second
        setInterval ->
            if Shifts.fetcher.run_count > 0
                Shifts.fetcher.run_count--
        , 1 * 1000

    fetch_in_parts: (done) ->
        latest_updates = []
        deleted_lastid = 0

        tables = ['Room', 'AngelTypes', 'ShiftTypes', 'User', 'Shifts', 'NeededAngelTypes', 'ShiftEntry']
        tables_to_process = tables.length

        for table in tables
            do (table) ->
                Shifts.db.get_option "#{table}_lastupdate", (res) ->
                    if res
                        r = res
                    else
                        r = 0

                    latest_updates.push(
                        table + '=' + r
                    )

                    tables_to_process--
                    if tables_to_process == 0

                        Shifts.db.get_option 'deleted_lastid', (res) ->
                            if res
                                deleted_lastid = res

                            start_filling(done)

        start_filling = (done) ->
            Shifts.$shiftplan.find('#fetcher_statustext').text Shifts._('Fetching data from server...')
            Shifts.$shiftplan.find('#remaining_objects').text ''
            url = '?p=shifts_json_export_websql&' + latest_updates.join('&') + '&deleted_lastid=' + deleted_lastid

            # only fetch if you didn't run for too often
            Shifts.fetcher.run_count++
            if Shifts.fetcher.run_count > Shifts.fetcher.runbuffersize then return done() else $.get url, (data) ->
                Shifts.fetcher.total_objects_count = 0
                Shifts.fetcher.total_objects_count += parseInt data.rooms_total, 10
                Shifts.fetcher.total_objects_count += parseInt data.angeltypes_total, 10
                Shifts.fetcher.total_objects_count += parseInt data.shift_types_total, 10
                Shifts.fetcher.total_objects_count += parseInt data.users_total, 10
                Shifts.fetcher.total_objects_count += parseInt data.shifts_total, 10
                Shifts.fetcher.total_objects_count += parseInt data.needed_angeltypes_total, 10
                Shifts.fetcher.total_objects_count += parseInt data.shift_entries_total, 10
                if data.deleted_entries_lastid != false
                    Shifts.fetcher.total_objects_count += 1

                Shifts.fetcher.remaining_objects_count = Shifts.fetcher.total_objects_count

                if Shifts.fetcher.total_objects_count_since_start == 0
                    # first fetch, for correct display of the status bar
                    Shifts.fetcher.total_objects_count_since_start = Shifts.fetcher.total_objects_count

                Shifts.$shiftplan.find('#fetcher_statustext').text Shifts._('Importing new objects into browser database.')
                Shifts.$shiftplan.find('#remaining_objects').text Shifts.fetcher.remaining_objects_count + ' ' + Shifts._('remaining...')
                Shifts.$shiftplan.find('#abort').on 'click', ->
                    document.cookie = 'websql=nope'
                    window.location.href = ''

                # populate start_time
                Shifts.db.get_option 'filter_start_time', (res) ->
                    if res
                        Shifts.render.START_TIME = parseInt res, 10

                    # populate selected_rooms
                    Shifts.db.get_option 'filter_selected_rooms', (res) ->
                        if res
                            Shifts.interaction.selected_rooms = []
                            for r in res.split(',')
                                Shifts.interaction.selected_rooms.push parseInt(r, 10)

                        # populate occupancy
                        Shifts.db.get_option 'filter_occupancy', (res) ->
                            if res
                                Shifts.interaction.occupancy = res

                            # populate rendering_time
                            Shifts.db.get_option 'rendering_time', (res) ->
                                if res
                                    Shifts.render.rendering_time = parseInt res, 10
                                else
                                    Shifts.render.rendering_time = 2000

                                # process deleted entries
                                deleted_entries = data.deleted_entries
                                deleted_entries_lastid = data.deleted_entries_lastid
                                Shifts.fetcher.process_deleted_entries deleted_entries, deleted_entries_lastid, ->

                                    # insert rooms
                                    rooms = data.rooms
                                    ids = []
                                    for r in rooms
                                        ids.push r.RID
                                    Shifts.db.delete_many_by_id 'Room', 'RID', ids, ->
                                        Shifts.fetcher.process Shifts.db.insert_room, rooms, ->
                                            if data.rooms_lastupdate
                                                Shifts.db.set_option 'Room_lastupdate', data.rooms_lastupdate, ->

                                            # insert angeltypes
                                            angeltypes = data.angeltypes
                                            ids = []
                                            for a in angeltypes
                                                ids.push a.id
                                            Shifts.db.delete_many_by_id 'AngelTypes', 'id', ids, ->
                                                Shifts.fetcher.process Shifts.db.insert_angeltype, angeltypes, ->
                                                    if data.angeltypes_lastupdate
                                                        Shifts.db.set_option 'AngelTypes_lastupdate', data.angeltypes_lastupdate, ->

                                                    # insert shift_types
                                                    shift_types = data.shift_types
                                                    ids = []
                                                    for s in shift_types
                                                        ids.push s.id
                                                    Shifts.db.delete_many_by_id 'ShiftTypes', 'id', ids, ->
                                                        Shifts.fetcher.process Shifts.db.insert_shifttype, shift_types, ->
                                                            if data.shift_types_lastupdate
                                                                Shifts.db.set_option 'ShiftTypes_lastupdate', data.shift_types_lastupdate, ->

                                                            # insert users
                                                            users = data.users
                                                            ids = []
                                                            for u in users
                                                                ids.push u.UID
                                                            Shifts.db.delete_many_by_id 'User', 'UID', ids, ->
                                                                Shifts.fetcher.process Shifts.db.insert_user, users, ->
                                                                    if data.users_lastupdate
                                                                        Shifts.db.set_option 'User_lastupdate', data.users_lastupdate, ->

                                                                    # insert shifts
                                                                    shifts = data.shifts
                                                                    ids = []
                                                                    for s in shifts
                                                                        ids.push s.SID
                                                                    Shifts.db.delete_many_by_id 'Shifts', 'SID', ids, ->
                                                                        Shifts.fetcher.process Shifts.db.insert_shift, shifts, ->
                                                                            if data.shifts_lastupdate
                                                                                Shifts.db.set_option 'Shifts_lastupdate', data.shifts_lastupdate, ->

                                                                            # insert needed_angeltypes
                                                                            needed_angeltypes = data.needed_angeltypes
                                                                            ids = []
                                                                            for n in needed_angeltypes
                                                                                ids.push n.id
                                                                            Shifts.db.delete_many_by_id 'NeededAngelTypes', 'id', ids, ->
                                                                                Shifts.fetcher.process Shifts.db.insert_needed_angeltype, needed_angeltypes, ->
                                                                                    if data.needed_angeltypes_lastupdate
                                                                                        Shifts.db.set_option 'NeededAngelTypes_lastupdate', data.needed_angeltypes_lastupdate, ->

                                                                                    # insert shift_entries
                                                                                    shift_entries = data.shift_entries
                                                                                    ids = []
                                                                                    for s in shift_entries
                                                                                        ids.push s.id
                                                                                    Shifts.db.delete_many_by_id 'ShiftEntry', 'id', ids, ->
                                                                                        Shifts.fetcher.process Shifts.db.insert_shiftentry, shift_entries, ->
                                                                                            if data.shift_entries_lastupdate
                                                                                                Shifts.db.set_option 'ShiftEntry_lastupdate', data.shift_entries_lastupdate, ->

                                                                                            if Shifts.fetcher.total_objects_count <= 0
                                                                                                done()
                                                                                            else
                                                                                                setTimeout ->
                                                                                                    Shifts.fetcher.fetch_in_parts done
                                                                                                , 20 # to give set_options some time

                                                                                            ###
                                                                                            # Hottest point in callback-hell
                                                                                            ###


    process: (processing_func, items_to_process, done) ->
        $ro = Shifts.$shiftplan.find('#remaining_objects')
        $pb = Shifts.$shiftplan.find('#progress_bar')
        if items_to_process.length > 0
            item = items_to_process.shift()

            # render status
            Shifts.fetcher.remaining_objects_count--
            if Shifts.fetcher.remaining_objects_count % 10 == 0
                percentage = 100 - Shifts.fetcher.remaining_objects_count / Shifts.fetcher.total_objects_count_since_start * 100
                $ro.text Shifts.fetcher.remaining_objects_count + ' ' + Shifts._('remaining...')
                $pb.text Math.round(percentage) + '%'
                $pb.width percentage + '%'

            processing_func item, ->
                Shifts.fetcher.process processing_func, items_to_process, done

        else
            done()

    process_deleted_entries: (deleted_entries, deleted_lastid, done) ->

        update_lastid = (done) ->
            if deleted_lastid != false
                Shifts.db.set_option 'deleted_lastid', deleted_lastid, ->
                    done()
            else
                done()

        if deleted_entries.length > 0
            e = deleted_entries.shift()

            switch e.tablename
                when 'room'
                    Shifts.db.delete_many_by_id 'Room', 'RID', e.entry_ids, ->
                        Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done
                when 'user'
                    Shifts.db.delete_many_by_id 'User', 'UID', e.entry_ids, ->
                        Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done
                when 'shifts'
                    Shifts.db.delete_many_by_id 'Shifts', 'SID', e.entry_ids, ->
                        Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done
                when 'shifts_psid'
                    Shifts.db.delete_many_by_id 'Shifts', 'PSID', e.entry_ids, ->
                        Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done
                when 'shiftentry'
                    Shifts.db.delete_many_by_id 'ShiftEntry', 'id', e.entry_ids, ->
                        Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done
                when 'shifttypes'
                    Shifts.db.delete_many_by_id 'ShiftTypes', 'id', e.entry_ids, ->
                        Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done
                when 'angeltypes'
                    Shifts.db.delete_many_by_id 'Angeltypes', 'id', e.entry_ids, ->
                        Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done
                when 'needed_angeltypes_shiftid'
                    Shifts.db.delete_many_by_id 'NeededAngelTypes', 'shift_id', e.entry_ids, ->
                        Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done
                when 'needed_angeltypes_roomid'
                    Shifts.db.delete_many_by_id 'NeededAngelTypes', 'room_id', e.entry_ids, ->
                        Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done
                else
                    # do nothing. i.e. first entry of the DeleteLog, to pass an id to clients
                    # prevents hanging of this process on unknown names
                    Shifts.fetcher.process_deleted_entries deleted_entries, deleted_lastid, done

        else
            update_lastid ->
                done()


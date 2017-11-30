
Shifts.fetcher =

    total_objects_count: 0
    total_objects_count_since_start: 0
    remaining_objects_count: 0

    start: (display_status, done) ->
        if display_status
            Shifts.$shiftplan.html Shifts.templates.fetcher_status
        Shifts.fetcher.fetch_in_parts ->
            done()

    fetch_in_parts: (done) ->
        table_mapping =
            Room: 'RID'
            AngelTypes: 'id'
            ShiftTypes: 'id'
            User: 'UID'
            Shifts: 'SID'
            NeededAngelTypes: 'id'
            ShiftEntry: 'id'

        latest_ids = []
        deleted_lastid = 0

        # determine object count in table_mapping
        tables_to_process = 0
        for i of table_mapping
            tables_to_process++

        for table, idname of table_mapping
            do (table, idname) ->
                Shifts.db.websql.transaction (tx) ->
                    tx.executeSql "SELECT #{idname} FROM #{table} ORDER BY #{idname} DESC LIMIT 1", [], (tx, res) ->
                        if res.rows.length > 0
                            r = res.rows[0][idname]
                        else
                            r = 0

                        latest_ids.push(
                            table + '=' + r
                        )

                        tables_to_process--
                        if tables_to_process == 0

                            Shifts.db.get_option 'deleted_lastid', (res) ->
                                if res
                                    deleted_lastid = res

                                start_filling(done)

        start_filling = (done) ->
            Shifts.$shiftplan.find('#fetcher_statustext').text 'Fetching data from server...'
            Shifts.$shiftplan.find('#remaining_objects').text ''
            url = '?p=shifts_json_export_websql&' + latest_ids.join('&') + '&deleted_lastid=' + deleted_lastid
            $.get url, (data) ->
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

                Shifts.$shiftplan.find('#fetcher_statustext').text 'Importing new objects into browser database.'
                Shifts.$shiftplan.find('#remaining_objects').text Shifts.fetcher.remaining_objects_count + ' remaining...'
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

                        # populate selected_angeltypes
                        Shifts.db.get_option 'filter_selected_angeltypes', (res) ->
                            if res
                                Shifts.interaction.selected_angeltypes = []
                                for a in res.split(',')
                                    Shifts.interaction.selected_angeltypes.push parseInt(a, 10)

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

                                    # insert rooms
                                    rooms = data.rooms
                                    Shifts.fetcher.process Shifts.db.insert_room, rooms, ->

                                        # insert angeltypes
                                        angeltypes = data.angeltypes
                                        Shifts.fetcher.process Shifts.db.insert_angeltype, angeltypes, ->

                                            # insert shift_types
                                            shift_types = data.shift_types
                                            Shifts.fetcher.process Shifts.db.insert_shifttype, shift_types, ->

                                                # insert users
                                                users = data.users
                                                Shifts.fetcher.process Shifts.db.insert_user, users, ->

                                                    # insert shifts
                                                    shifts = data.shifts
                                                    Shifts.fetcher.process Shifts.db.insert_shift, shifts, ->

                                                        # insert needed_angeltypes
                                                        needed_angeltypes = data.needed_angeltypes
                                                        Shifts.fetcher.process Shifts.db.insert_needed_angeltype, needed_angeltypes, ->

                                                            # insert shift_entries
                                                            shift_entries = data.shift_entries
                                                            Shifts.fetcher.process Shifts.db.insert_shiftentry, shift_entries, ->

                                                                # process deleted entries
                                                                deleted_entries = data.deleted_entries
                                                                deleted_entries_lastid = data.deleted_entries_lastid
                                                                Shifts.fetcher.process_deleted_entries deleted_entries, deleted_entries_lastid, ->

                                                                    if Shifts.fetcher.total_objects_count <= 0
                                                                        done()
                                                                    else
                                                                        Shifts.fetcher.fetch_in_parts done

    process: (processing_func, items_to_process, done) ->
        $ro = Shifts.$shiftplan.find('#remaining_objects')
        $pb = Shifts.$shiftplan.find('#progress_bar')
        if items_to_process.length > 0
            item = items_to_process.shift()

            # render status
            Shifts.fetcher.remaining_objects_count--
            if Shifts.fetcher.remaining_objects_count % 10 == 0
                percentage = 100 - Shifts.fetcher.remaining_objects_count / Shifts.fetcher.total_objects_count_since_start * 100
                $ro.text Shifts.fetcher.remaining_objects_count + ' remaining...'
                $pb.text Math.round(percentage) + '%'
                $pb.width percentage + '%'

            processing_func item, ->
                Shifts.fetcher.process processing_func, items_to_process, done

        else
            done()

    process_deleted_entries: (deleted_entries, deleted_lastid, done) ->
        # set option erst wenn alles prozessiert ist!
        #shifttype.id = parseInt shifttype.id, 10
        #Shifts.db.websql.transaction (t) ->
        #    t.executeSql 'INSERT INTO ShiftTypes (id, name) VALUES (?, ?)', [shifttype.id, shifttype.name]
        #    done()
        if deleted_lastid != false
            Shifts.db.set_option 'deleted_lastid', deleted_lastid, ->
                done()
        else
            done()


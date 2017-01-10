
Shifts.fetcher =

    start: (done) ->
        url = '?p=shifts_json_export_websql'
        $.get url, (data) ->

            # insert rooms
            rooms = data.rooms
            Shifts.$shiftplan.html 'fetching rooms...'
            Shifts.fetcher.process Shifts.db.insert_room, rooms, ->
                Shifts.log 'processing rooms done'

                # insert users
                users = data.users
                Shifts.$shiftplan.html 'fetching users...'
                Shifts.fetcher.process Shifts.db.insert_user, users, ->
                    Shifts.log 'processing users done'

                    # insert shift_types
                    shift_types = data.shift_types
                    Shifts.$shiftplan.html 'fetching shift_types...'
                    Shifts.fetcher.process Shifts.db.insert_shifttype, shift_types, ->
                        Shifts.log 'processing shift_types done'

                        # insert angeltypes
                        angeltypes = data.angeltypes
                        Shifts.$shiftplan.html 'fetching angeltypes...'
                        Shifts.fetcher.process Shifts.db.insert_angeltype, angeltypes, ->
                            Shifts.log 'processing angeltypes done'

                            # insert shifts
                            shifts = data.shifts
                            Shifts.$shiftplan.html 'fetching shifts...'
                            Shifts.fetcher.process Shifts.db.insert_shift, shifts, ->
                                Shifts.log 'processing shifts done'

                                # insert shift_entries
                                shift_entries = data.shift_entries
                                Shifts.$shiftplan.html 'fetching shift entries...'
                                Shifts.fetcher.process Shifts.db.insert_shiftentry, shift_entries, ->
                                    Shifts.log 'processing shift_entries done'

                                    Shifts.$shiftplan.html 'done.'
                                    done()

    process: (processing_func, items_to_process, done) ->
        if items_to_process.length > 0
            item = items_to_process.shift()
            processing_func item, ->
                Shifts.fetcher.process processing_func, items_to_process, done
        else
            done()


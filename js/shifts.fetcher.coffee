
Shifts.fetcher =

    total_process_count: 0
    remaining_process_count: 0

    start: (done) ->

        Shifts.$shiftplan.html 'Fetching data from server...'

        table_mapping =
            rooms: 'room_ids'
            angeltypes: 'angeltype_ids'
            shift_types: 'shifttype_ids'
            users: 'user_ids'
            shifts: 'shift_ids'
            needed_angeltypes: 'needed_angeltype_ids'
            shift_entries: 'shiftentry_ids'

        latest_ids = []
        for table, idsname of table_mapping
            idlist = Shifts.db[idsname]
            if idlist
                max_id = Math.max.apply(Math, idlist)
            else
                max_id = 0

            latest_ids.push(
                table + '=' + max_id
            )

        url = '?p=shifts_json_export_websql&' + latest_ids.join('&')
        $.get url, (data) ->
            Shifts.fetcher.total_process_count += data.rooms.length
            Shifts.fetcher.total_process_count += data.angeltypes.length
            Shifts.fetcher.total_process_count += data.shift_types.length
            Shifts.fetcher.total_process_count += data.users.length
            Shifts.fetcher.total_process_count += data.shifts.length
            Shifts.fetcher.total_process_count += data.needed_angeltypes.length
            Shifts.fetcher.total_process_count += data.shift_entries.length

            Shifts.fetcher.remaining_process_count = Shifts.fetcher.total_process_count

            Shifts.$shiftplan.html '
Importing new objects into browser database. <span id="remaining_objects"></span> remaining...
<div class="progress">
  <div id="progress_bar" class="progress-bar" style="width: 0%;">
    0%
  </div>
</div>'
            Shifts.$shiftplan.find('#remaining_objects').text Shifts.fetcher.remaining_process_count

            # populate start_time
            Shifts.db.get_option 'filter_start_time', (res) ->
                if res
                    Shifts.render.START_TIME = parseInt res, 10

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

                                            done()

    process: (processing_func, items_to_process, done) ->
        $ro = Shifts.$shiftplan.find('#remaining_objects')
        $pb = Shifts.$shiftplan.find('#progress_bar')
        if items_to_process.length > 0
            item = items_to_process.shift()

            # render status
            Shifts.fetcher.remaining_process_count--
            if Shifts.fetcher.remaining_process_count % 100 == 0
                percentage = 100 - Math.round(Shifts.fetcher.remaining_process_count / Shifts.fetcher.total_process_count * 100)
                $ro.text Shifts.fetcher.remaining_process_count
                $pb.text percentage + '%'
                $pb.width percentage + '%'

            processing_func item, ->
                Shifts.fetcher.process processing_func, items_to_process, done

        else
            done()


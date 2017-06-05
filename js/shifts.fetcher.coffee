
Shifts.fetcher =

    total_objects_count: 0
    total_objects_count_since_start: 0
    remaining_objects_count: 0

    start: (done) ->
        Shifts.$shiftplan.html '
<span id="fetcher_statustext">Fetching data from server...</span> <span id="remaining_objects"></span>
<div class="progress">
  <div id="progress_bar" class="progress-bar" style="width: 0%;">
    0%
  </div>
</div>
<a id="abort" href="" class="btn btn-default btn-xs">Abort and switch to legacy view</a>'
        Shifts.fetcher.fetch_in_parts ->
            done()

    fetch_in_parts: (done) ->
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
            if idlist.length > 0
                max_id = Math.max.apply(Math, idlist)
            else
                max_id = 0

            latest_ids.push(
                table + '=' + max_id
            )

        Shifts.$shiftplan.find('#fetcher_statustext').text 'Fetching data from server...'
        Shifts.$shiftplan.find('#remaining_objects').text ''
        url = '?p=shifts_json_export_websql&' + latest_ids.join('&')
        $.get url, (data) ->
            Shifts.fetcher.total_objects_count = 0
            Shifts.fetcher.total_objects_count += parseInt data.rooms_total, 10
            Shifts.fetcher.total_objects_count += parseInt data.angeltypes_total, 10
            Shifts.fetcher.total_objects_count += parseInt data.shift_types_total, 10
            Shifts.fetcher.total_objects_count += parseInt data.users_total, 10
            Shifts.fetcher.total_objects_count += parseInt data.shifts_total, 10
            Shifts.fetcher.total_objects_count += parseInt data.needed_angeltypes_total, 10
            Shifts.fetcher.total_objects_count += parseInt data.shift_entries_total, 10
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
            if Shifts.fetcher.remaining_objects_count % 100 == 0
                percentage = 100 - Shifts.fetcher.remaining_objects_count / Shifts.fetcher.total_objects_count_since_start * 100
                $ro.text Shifts.fetcher.remaining_objects_count + ' remaining...'
                $pb.text Math.round(percentage) + '%'
                $pb.width percentage + '%'

            processing_func item, ->
                Shifts.fetcher.process processing_func, items_to_process, done

        else
            done()


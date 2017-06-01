
Shifts.render =

    # 15m * 60s/m = 900s
    SECONDS_PER_ROW: 900

    # Height of a block in pixel.
    # Do not change - corresponds with theme/css
    BLOCK_HEIGHT: 30

    # Distance between two shifts in pixels
    MARGIN: 5

    # Seconds added to the start and end time
    TIME_MARGIN: 1800

    # holder for start time
    START_TIME: false

    tick: (time, label = false) ->

        daytime = "tick_bright"
        hour = moment.unix(time).format('H')
        if  hour > 19 or hour < 8
            daytime = "tick_dark"

        diffhour = if moment().isDST() then 22 else 23

        if time % (24*60*60) == diffhour*60*60
            if label
                return { tick_day: true, label: moment.unix(time).format('MM-DD HH:mm'), daytime: daytime }
            else
                return { tick_day: true, daytime: daytime }
        else if time % (60*60) == 0
            if label
                return { tick_hour: true, label: moment.unix(time).format('HH:mm'), daytime: daytime }
            else
                return { tick_hour: true, daytime: daytime }
        else
            return { tick: true, daytime: daytime }

    get_starttime: (margin = false) ->

        # if START_TIME is not set, set it to 0:00 at the current day and format as unix timestamp
        if not Shifts.render.START_TIME
            Shifts.render.START_TIME = parseInt moment(moment().format('YYYY-MM-DD HH:00')).format('X'), 10

        start_time = parseInt Shifts.render.START_TIME, 10
        if margin
            start_time = start_time - Shifts.render.TIME_MARGIN
        return start_time

    get_endtime: (margin = false) ->
        end_time = Shifts.render.get_starttime() + 24*60*60
        if margin
            end_time = end_time + Shifts.render.TIME_MARGIN
        return end_time

    shiftplan: ->
        user_id = parseInt $('#shiftplan').data('user_id'), 10
        Shifts.db.get_rooms (rooms) ->
            Shifts.db.get_angeltypes (angeltypes) ->
                selected_rooms = Shifts.interaction.selected_rooms
                selected_angeltypes = Shifts.interaction.selected_angeltypes
                Shifts.db.get_shifts selected_rooms, selected_angeltypes, (db_shifts) ->
                    Shifts.db.get_shiftentries (db_shiftentries) ->
                        Shifts.db.get_angeltypes_needed (db_angeltypes_needed) ->
                            Shifts.db.get_usershifts user_id, (db_usershifts) ->
                                Shifts.render.shiftplan_assemble rooms, angeltypes, db_shifts, db_angeltypes_needed, db_shiftentries, db_usershifts

    shiftplan_assemble: (rooms, angeltypes, db_shifts, db_angeltypes_needed, db_shiftentries, db_usershifts) ->
        lanes = {}
        shiftentries = {}
        needed_angeltypes = {}

        # build needed angeltypes
        for atn in db_angeltypes_needed
            needed_angeltypes[atn.shift_id + '-' + atn.angel_type_id] = atn.angel_count

        # build shiftentries object
        for se in db_shiftentries
            if typeof shiftentries[se.SID] == "undefined"
                shiftentries[se.SID] = []
                shiftentries[se.SID].push
                    TID: se.TID
                    at_name: se.at_name
                    angels: []
                    angels_needed: needed_angeltypes[se.SID + '-' + se.TID]

        # fill shiftentries with needed angeltypes
        for atn in db_angeltypes_needed
            if typeof shiftentries[atn.shift_id] == "undefined"
                shiftentries[atn.shift_id] = []
                shiftentries[atn.shift_id].push
                    TID: atn.angel_type_id
                    at_name: atn.name
                    angels: []
                    angels_needed: atn.angel_count
            else
                entry_exists = false
                for s of shiftentries[atn.shift_id]
                    if atn.angel_type_id == shiftentries[atn.shift_id][s].TID
                        entry_exists = true
                        break
                if not entry_exists
                    shiftentries[atn.shift_id].push
                        TID: atn.angel_type_id
                        at_name: atn.name
                        angels: []
                        angels_needed: atn.angel_count

        # fill it with angels
        for se in db_shiftentries
            for s of shiftentries[se.SID]
                if se.TID == shiftentries[se.SID][s].TID
                    shiftentries[se.SID][s].angels.push
                        UID: se.UID
                        Nick: se.Nick
                    shiftentries[se.SID][s].angels_needed--

        add_shift = (shift, room_id) ->
            # fix empty title
            if shift.shift_title == "null"
                shift.shift_title = null

            # start- and endtime
            shift.starttime = moment.unix(shift.start_time).format('HH:mm')
            shift.endtime = moment.unix(shift.end_time).format('HH:mm')

            # add shiftentries
            shift.angeltypes = shiftentries[shift.SID]

            # set state class
            shift.signup_state = calculate_signup_state(shift)
            shift.state_class = calculate_state_class(shift.signup_state)

            # calculate shift height
            blocks = Math.ceil(shift.end_time - shift.start_time) / Shifts.render.SECONDS_PER_ROW
            blocks = Math.max(1, blocks)
            height = blocks * Shifts.render.BLOCK_HEIGHT - Shifts.render.MARGIN
            shift.blocks = blocks
            shift.height = height

            # TODO: use checkboxe
            if false
                # show only free shifts
                if shift.signup_state not in ['free', 'collides', 'signed_up']
                    return true

            for lane_nr of lanes[room_id]
                if shift_fits(shift, room_id, lane_nr)
                    lanes[room_id][lane_nr].push shift
                    return true
            return false

        calculate_signup_state = (shift) ->
            # you cannot join if you already signed up for this shift
            for u in db_usershifts
                if u.SID == shift.SID
                    return "signed_up"

            # you can only join if the shift is in the future
            now_unix = moment().format('X')
            if shift.end_time < now_unix
                return "shift_ended"

            # you cannot join if the shift is full
            angels_needed = 0
            for at in shift.angeltypes
                angels_needed = angels_needed + at.angels_needed
            if angels_needed == 0
                return "occupied"

            # TODO:
            # you cannot join if the user is not of this angel type
            # you cannot join if you are not confirmed
            # you cannot join if angeltype has no self signup

            # you cannot join if user already joined a parallel or this shift
            for u in db_usershifts
                if u.SID != shift.SID
                    if not (shift.start_time >= u.end_time or shift.end_time <= u.start_time)
                        return "collides"

            # hooray, shift is free for you!
            return "free"

        calculate_state_class = (signup_state) ->
            switch signup_state
                when "shift_ended" then "default"
                when "signed_up" then "primary"
                when "free" then "danger"
                when "angeltype" then "warning"
                when "collides" then "warning"
                when "occupied" then "success"
                when "admin" then "success"

        shift_fits = (shift, room_id, lane_nr) ->
            for lane_shift in lanes[room_id][lane_nr]
                if not (shift.start_time >= lane_shift.end_time or shift.end_time <= lane_shift.start_time)
                    return false
            return true

        start_time = Shifts.render.get_starttime(true)
        end_time = Shifts.render.get_endtime(true)

        firstblock_starttime = end_time
        lastblock_endtime = start_time

        for shift in db_shifts

            # calculate first block start time
            if shift.start_time < firstblock_starttime
                firstblock_starttime = shift.start_time

            # calculate last block end time
            if shift.end_time > lastblock_endtime
                lastblock_endtime = shift.end_time

            room_id = shift.RID

            if typeof lanes[room_id] == "undefined"
                # initialize room
                lanes[room_id] = [[]] # lanes.roomid.lanenr.shifts

            shift_added = false
            for lane in lanes[room_id]
                shift_added = add_shift(shift, room_id)
                if shift_added
                    break

            if not shift_added
                #Shifts.log "lane is full, adding new one"
                lanes[room_id].push []
                highest_lane_nr = lanes[room_id].length - 1
                add_shift(shift, room_id)

        # build datastruct for the timelane
        time_slot = []
        if db_shifts.length > 0
            thistime = parseInt(firstblock_starttime, 10) - Shifts.render.TIME_MARGIN
            while thistime < parseInt(lastblock_endtime, 10) + Shifts.render.TIME_MARGIN
                time_slot.push Shifts.render.tick thistime, true
                thistime += Shifts.render.SECONDS_PER_ROW

        # build datastruct for mustache
        mustache_rooms = []
        for room_nr of rooms
            room_id = rooms[room_nr].RID
            mustache_rooms[room_nr] = {}
            mustache_rooms[room_nr].Name = rooms[room_nr].Name
            mustache_rooms[room_nr].lanes = []
            for lane_nr of lanes[room_id]
                mustache_rooms[room_nr].lanes[lane_nr] = {}
                mustache_rooms[room_nr].lanes[lane_nr].shifts = []

                rendered_until = firstblock_starttime - Shifts.render.TIME_MARGIN

                for shift_nr of lanes[room_id][lane_nr]

                    # render ticks
                    while rendered_until + Shifts.render.SECONDS_PER_ROW <= lanes[room_id][lane_nr][shift_nr].start_time
                        mustache_rooms[room_nr].lanes[lane_nr].shifts.push Shifts.render.tick(rendered_until, true)
                        rendered_until += Shifts.render.SECONDS_PER_ROW

                    # render shift
                    mustache_rooms[room_nr].lanes[lane_nr].shifts.push { shift: lanes[room_id][lane_nr][shift_nr] }
                    rendered_until += lanes[room_id][lane_nr][shift_nr].blocks * Shifts.render.SECONDS_PER_ROW

                # render ticks till end block.
                while rendered_until < parseInt(lastblock_endtime, 10) + Shifts.render.TIME_MARGIN
                    mustache_rooms[room_nr].lanes[lane_nr].shifts.push Shifts.render.tick(rendered_until, true)
                    rendered_until += Shifts.render.SECONDS_PER_ROW

        # check for selected rooms
        for room in rooms
            if room.RID in Shifts.interaction.selected_rooms
                room.selected = true

        # check for selected angeltypes
        for angeltype in angeltypes
            if angeltype.id in Shifts.interaction.selected_angeltypes
                angeltype.selected = true

        ## check for selected occupancy
        #occupancies = [ {value: "free"}, {value: "occupied"}]
        #
        #for occupancy in occupancies
        #    if occupancy.value in Shifts.interaction.occupancy
        #        occupancy.selected = true

        tpl = ''

        tpl += Mustache.render Shifts.templates.filter_form,
            rooms: rooms
            angeltypes: angeltypes
            #occupancies: occupancies

        tpl += Mustache.render Shifts.templates.shift_calendar,
            timelane_ticks: time_slot
            rooms: mustache_rooms

        Shifts.$shiftplan.html(tpl)

        $('#datetimepicker').datetimepicker
            value: moment.unix(Shifts.render.START_TIME).format('YYYY-MM-DD HH:mm')
            timepicker: true
            inline: true
            format: 'Y-m-d H:i'
            minDate: '-1970-01-05'
            maxDate: '+1970-01-03'
            onChangeDateTime: (dp, $input) ->
                stime = parseInt moment($input.val()).format('X'), 10
                Shifts.render.START_TIME = stime
                Shifts.db.set_option 'filter_start_time', stime, ->
                    Shifts.render.shiftplan()



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

    tick: (time, label = false) ->
        if time % (24*60*60) == 23*60*60
            if label
                return { tick_day: true, label: moment.unix(time).format('MM-DD HH:mm') }
            else
                return { tick_day: true }
        else if time % (60*60) == 0
            if label
                return { tick_hour: true, label: moment.unix(time).format('HH:mm') }
            else
                return { tick_hour: true }
        else
            return { tick: true }

    get_starttime: (margin = false) ->
        start_time = moment(moment().format('YYYY-MM-DD')).format('X')
        start_time = parseInt start_time, 10
        if margin
            start_time = start_time - Shifts.render.TIME_MARGIN
        return start_time

    get_endtime: (margin = false) ->
        end_time = Shifts.render.get_starttime() + 24*60*60
        if margin
            end_time = end_time + Shifts.render.TIME_MARGIN
        return end_time

    timelane: ->
        time_slot = []
        start_time = Shifts.render.get_starttime(true)
        end_time = Shifts.render.get_endtime(true)
        thistime = start_time
        while thistime < end_time
            time_slot.push Shifts.render.tick thistime, true
            thistime += Shifts.render.SECONDS_PER_ROW

        return time_slot

    shiftplan: ->
        Shifts.db.get_rooms (rooms) ->
            Shifts.db.get_angeltypes (angeltypes) ->

                selected_rooms = Shifts.interaction.selected_rooms
                selected_angeltypes = Shifts.interaction.selected_angeltypes

                Shifts.db.get_shifts selected_rooms, selected_angeltypes, (db_shifts) ->

                    lanes = {}

                    add_shift = (shift, room_id) ->
                        # fix empty title
                        if shift.shift_title == "null"
                            shift.shift_title = null

                        # start- and endtime
                        shift.starttime = moment.unix(shift.start_time).format('HH:mm')
                        shift.endtime = moment.unix(shift.end_time).format('HH:mm')

                        # calculate shift height
                        blocks = Math.ceil(shift.end_time - shift.start_time) / Shifts.render.SECONDS_PER_ROW
                        blocks = Math.max(1, blocks)
                        height = blocks * Shifts.render.BLOCK_HEIGHT - Shifts.render.MARGIN
                        shift.blocks = blocks
                        shift.height = height

                        for lane_nr of lanes[room_id]
                            if shift_fits(shift, room_id, lane_nr)
                                lanes[room_id][lane_nr].push shift
                                return true
                        return false

                    shift_fits = (shift, room_id, lane_nr) ->
                        for lane_shift in lanes[room_id][lane_nr]
                            if not (shift.start_time >= lane_shift.end_time or shift.end_time <= lane_shift.start_time)
                                return false
                        return true

                    # temporary
                    start_time = Shifts.render.get_starttime(true)
                    end_time = Shifts.render.get_endtime(true)
                    # /temporary

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
                            while rendered_until < lastblock_endtime
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

                    tpl = ''

                    tpl += Mustache.render Shifts.templates.filter_form,
                        rooms: rooms
                        angeltypes: angeltypes

                    tpl += Mustache.render Shifts.templates.shift_calendar,
                        timelane_ticks: Shifts.render.timelane()
                        rooms: mustache_rooms

                    Shifts.$shiftplan.html(tpl)


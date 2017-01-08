
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

    timelane: ->
        time_slot = []
        start_time = moment(moment().format('YYYY-MM-DD')).format('X')
        start_time = parseInt start_time, 10
        start_time = start_time - Shifts.render.TIME_MARGIN
        for i in [0..100]
            thistime = start_time + i * Shifts.render.SECONDS_PER_ROW
            time_slot.push Shifts.render.tick thistime, true

        return time_slot

    shiftplan: ->
        Shifts.db.get_rooms (rooms) ->
            Shifts.db.get_my_shifts (db_shifts) ->

                lanes = {}

                add_shift = (shift, room_id) ->

                    # calculate shift height
                    blocks = Math.ceil(shift.shift_end - shift.shift_start) / Shifts.render.SECONDS_PER_ROW
                    blocks = Math.max(1, blocks)
                    height = blocks * Shifts.render.BLOCK_HEIGHT - Shifts.render.MARGIN
                    shift.height = height

                    for lane_nr of lanes[room_id]
                        if shift_fits(shift, room_id, lane_nr)
                            lanes[room_id][lane_nr].push shift
                            return true
                    return false

                shift_fits = (shift, room_id, lane_nr) ->
                    for lane_shift in lanes[room_id][lane_nr]
                        if not (shift.shift_start >= lane_shift.shift_end or shift.shift_end <= lane_shift.shift_start)
                            return false
                    return true

                for shift in db_shifts
                    room_id = shift.RID

                    if typeof lanes[room_id] == "undefined"
                        # initialize room with one lane
                        lanes[room_id] = [[]] # lanes.roomid.lanenr.shifts

                    shift_added = false
                    for lane in lanes[room_id]
                        shift_added = add_shift(shift, room_id)
                        if shift_added
                            break

                    if not shift_added
                        Shifts.log "lane is full, adding new one"
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
                        for shift_nr of lanes[room_id][lane_nr]
                            mustache_rooms[room_nr].lanes[lane_nr].shifts[shift_nr] =
                                shift: lanes[room_id][lane_nr][shift_nr]

                Shifts.log mustache_rooms

                tpl = ''
                tpl += Mustache.render Shifts.templates.filter_form
                tpl += Mustache.render Shifts.templates.shift_calendar,
                    rooms: mustache_rooms
                    timelane_ticks: Shifts.render.timelane()

                Shifts.$shiftplan.html(tpl)


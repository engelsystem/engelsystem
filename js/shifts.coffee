
Shifts = window.Shifts || {
    db:
        room_ids: []
        user_ids: []
        shift_ids: []
        shiftentry_ids: []
        shifttype_ids: []

        init: (done) ->
            Shifts.log 'init db'
            alasql 'CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem;
            ATTACH INDEXEDDB DATABASE engelsystem;', ->
                alasql 'USE engelsystem', ->
                    # note: primary key doesn't work, see https://github.com/agershun/alasql/issues/566
                    alasql 'CREATE TABLE IF NOT EXISTS Shifts (SID INT, title, shifttype_id INT, shift_start INT, shift_end INT, RID INT);
                    CREATE TABLE IF NOT EXISTS User (UID INT, nick);
                    CREATE TABLE IF NOT EXISTS Room (RID INT, Name);
                    CREATE TABLE IF NOT EXISTS ShiftEntry (id INT, SID INT, TID INT, UID INT);
                    CREATE TABLE IF NOT EXISTS ShiftTypes (id INT, name, angeltype_id INT);
                    CREATE TABLE IF NOT EXISTS options (option_key, option_value);', ->
                        Shifts.db.populate_ids ->
                            done()

        populate_ids: (done) ->

            # rooms
            alasql "SELECT RID from Room", (res) ->
                for r in res
                    Shifts.db.room_ids.push r.RID

                # users
                alasql "SELECT UID from User", (res) ->
                    for u in res
                        Shifts.db.user_ids.push u.UID

                    # shift types
                    alasql "SELECT id from ShiftTypes", (res) ->
                        for s in res
                            Shifts.db.shifttype_ids.push s.id

                        # shifts
                        alasql "SELECT SID from Shifts", (res) ->
                            for s in res
                                Shifts.db.shift_ids.push s.SID

                            # shift entries
                            alasql "SELECT id from ShiftEntry", (res) ->
                                for s in res
                                    Shifts.db.shiftentry_ids.push s.id

                                done()

        insert_room: (room, done) ->
            room_exists = Shifts.db.room_ids.indexOf(parseInt(room.RID, 10)) > -1
            if room_exists == false
                alasql "INSERT INTO Room (RID, Name) VALUES (#{room.RID}, '#{room.Name}')", ->
                    Shifts.db.room_ids.push room.RID
                    done()
            else
                done()

        insert_user: (user, done) ->
            user_exists = Shifts.db.user_ids.indexOf(parseInt(user.UID, 10)) > -1
            if user_exists == false
                alasql "INSERT INTO User (UID, Nick) VALUES (#{user.UID}, '#{user.Nick}')", ->
                    Shifts.db.user_ids.push user.UID
                    done()
            else
                done()

        insert_shift: (shift, done) ->
            # Debug note: Array.indexOf may need a polyfill for older browsers
            # https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/indexOf
            shift_exists = Shifts.db.shift_ids.indexOf(parseInt(shift.SID, 10)) > -1
            if shift_exists == false
                alasql "INSERT INTO Shifts (SID, title, shifttype_id, shift_start, shift_end, RID) VALUES (#{shift.SID}, '#{shift.title}', '#{shift.shifttype_id}', '#{shift.start}', '#{shift.end}', '#{shift.RID}')", ->
                    Shifts.db.shift_ids.push shift.SID
                    done()
            else
                done()

        insert_shiftentry: (shiftentry, done) ->
            shiftentry_exists = Shifts.db.shiftentry_ids.indexOf(parseInt(shiftentry.id, 10)) > -1
            if shiftentry_exists == false
                alasql "INSERT INTO ShiftEntry (id, SID, TID, UID) VALUES (#{shiftentry.id}, '#{shiftentry.SID}', '#{shiftentry.TID}', '#{shiftentry.UID}')", ->
                    Shifts.db.shiftentry_ids.push shiftentry.id
                    done()
            else
                done()

        insert_shifttype: (shifttype, done) ->
            shifttype.id = parseInt shifttype.id, 10
            shifttype_exists = Shifts.db.shifttype_ids.indexOf(shifttype.id) > -1
            if shifttype_exists == false
                alasql "INSERT INTO ShiftTypes (id, name) VALUES (#{shifttype.id}, '#{shifttype.name}')", ->
                    Shifts.db.shifttype_ids.push shifttype.id
                    done()
            else
                done()

        get_my_shifts: (done) ->
            #alasql "SELECT * FROM ShiftEntry LEFT JOIN User ON ShiftEntry.UID = User.UID LEFT JOIN Shifts ON ShiftEntry.SID = Shifts.SID", (res) ->
            rand = 1 + parseInt(Math.random() * 10, 10)
            rand = 100
            alasql "SELECT Shifts.SID, Shifts.title as shift_title, Shifts.shifttype_id, Shifts.shift_start, Shifts.shift_end, Shifts.RID,
                ShiftTypes.name as shifttype_name,
                Room.Name as room_name
                FROM Shifts
                LEFT JOIN ShiftTypes ON ShiftTypes.id = Shifts.shifttype_id
                LEFT JOIN Room ON Room.RID = Shifts.RID
                LIMIT #{rand}", (res) ->
                done res

        get_rooms: (done) ->
            alasql "SELECT * FROM Room", (res) ->
                done res

    fetcher:
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

    render:

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

                    Shifts.log lanes

                    tpl = ''
                    tpl += Mustache.render Shifts.template.filter_form
                    tpl += Mustache.render Shifts.template.shift_calendar,
                        lanes: lanes
                        timelane_ticks: Shifts.render.timelane()

                    Shifts.$shiftplan.html(tpl)

    init: ->
        Shifts.$shiftplan = $('#shiftplan')
        if Shifts.$shiftplan.length > 0
            Shifts.log 'shifts init'
            Shifts.db.init ->
                Shifts.log 'db initialized'
                Shifts.fetcher.start ->
                    Shifts.log 'fetch complete.'
                    Shifts.render.shiftplan()

            $('body').on 'click', '#filterbutton', ->
                Shifts.render.shiftplan()
                return false

    log: (msg) ->
        console.info msg

    template:
        filter_form: '
<form class="form-inline" action="" method="get">
  <input type="hidden" name="p" value="user_shifts">
  <div class="row">
    <div class="col-md-6">
      <h1>%title%</h1>
      <div class="form-group">%start_select%</div>
      <div class="form-group">
        <div class="input-group">
          <input class="form-control" type="text" id="start_time" name="start_time" size="5" pattern="^\d{1,2}:\d{2}$" placeholder="HH:MM" maxlength="5" value="%start_time%">
          <div class="input-group-btn">
            <button class="btn btn-default" title="Now" type="button" onclick="">
              <span class="glyphicon glyphicon-time"></span>
            </button>
          </div>
        </div>
      </div>
      &#8211;
      <div class="form-group">%end_select%</div>
      <div class="form-group">
        <div class="input-group">
          <input class="form-control" type="text" id="end_time" name="end_time" size="5" pattern="^\d{1,2}:\d{2}$" placeholder="HH:MM" maxlength="5" value="%end_time%">
          <div class="input-group-btn">
            <button class="btn btn-default" title="Now" type="button" onclick="">
              <span class="glyphicon glyphicon-time"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-2">%room_select%</div>
    <div class="col-md-2">%type_select%</div>
    <div class="col-md-2">%filled_select%</div>
  </div>
	<div class="row">
		<div class="col-md-6">
        <div>%task_notice%</div>
        <input id="filterbutton" class="btn btn-primary" type="submit" style="width: 75%; margin-bottom: 20px" value="%filter%">
		</div>
	</div>
</form>'

        shift_calendar: '
<div class="shift-calendar">

  <div class="lane time">
    <div class="header">Time</div>
    {{#timelane_ticks}}
        {{#tick}}
            <div class="tick"></div>
        {{/tick}}
        {{#tick_hour}}
            <div class="tick hour">{{label}}</div>
        {{/tick_hour}}
        {{#tick_day}}
            <div class="tick day">{{label}}</div>
        {{/tick_day}}
    {{/timelane_ticks}}
  </div>

{{#lanes}}
  <div class="lane">
    <div class="header">
      <a href="?p=rooms&action=view&room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{Name}}</a>
    </div>
    {{#shifts}}
        {{#tick}}
            <div class="tick"></div>
        {{/tick}}
        {{#tick_hour}}
            <div class="tick hour">{{text}}</div>
        {{/tick_hour}}
        {{#tick_day}}
            <div class="tick day">{{text}}</div>
        {{/tick_day}}
        {{#shift}}
            <div class="shift panel panel-success" style="height: 235px;">
              <div class="panel-heading">
                <a href="?p=shifts&amp;action=view&amp;shift_id=2696">00:00 ‐ 02:00 — {{name}}</a>
                <div class="pull-right">
                  <div class="btn-group">
                    <a href="?p=user_shifts&amp;edit_shift=2696" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span></a>
                    <a href="?p=user_shifts&amp;delete_shift=2696" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-trash"></span></a>
                  </div>
                </div>
              </div>
              <div class="panel-body">
                <span class="glyphicon glyphicon-info-sign"></span> {{title}}<br />
                <a href="?p=rooms&amp;action=view&amp;room_id=42"><span class="glyphicon glyphicon-map-marker"></span> Bottle Sorting (Hall H)</a>
              </div>
              <ul class="list-group">
                <li class="list-group-item"><strong><a href="?p=angeltypes&amp;action=view&amp;angeltype_id=104575">Angel</a>:</strong>
                  <span style=""><a class="" href="?p=users&amp;action=view&amp;user_id=1755"><span class="icon-icon_angel"></span> Pantomime</a></span>,
                  <span style=""><a class="" href="?p=users&amp;action=view&amp;user_id=50"><span class="icon-icon_angel"></span> sandzwerg</a></span>
                </li>
                <li class="list-group-item">
                  <a href="?p=user_shifts&amp;shift_id=2696&amp;type_id=104575" class="btn btn-default btn-xs">Neue Engel hinzufügen</a>
                </li>
              </ul>
              <div class="shift-spacer"></div>
            </div>
        {{/shift}}
    {{/shifts}}
  </div>
{{/lanes}}
</div>'

}



# document ready
$ ->
    Shifts.init()


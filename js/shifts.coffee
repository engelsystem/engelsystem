
Shifts = window.Shifts || {
    db:
        room_ids: []
        user_ids: []
        shift_ids: []
        shiftentry_ids: []

        init: (done) ->
            Shifts.log 'init db'
            alasql 'CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem;
            ATTACH INDEXEDDB DATABASE engelsystem;', ->
                alasql 'USE engelsystem', ->
                    # note: primary key doesn't work, see https://github.com/agershun/alasql/issues/566
                    alasql 'CREATE TABLE IF NOT EXISTS Shifts (SID, title, shift_start, shift_end);
                    CREATE TABLE IF NOT EXISTS User (UID, nick);
                    CREATE TABLE IF NOT EXISTS Room (RID, Name);
                    CREATE TABLE IF NOT EXISTS ShiftEntry (id, SID, TID, UID);
                    CREATE TABLE IF NOT EXISTS options (option_key, option_value);', ->
                        Shifts.db.populate_ids ->
                            done()

        populate_ids: (done) ->

            # Rooms
            alasql "SELECT RID from Room", (res) ->
                for r in res
                    Shifts.db.room_ids.push r.RID

                # Users
                alasql "SELECT UID from User", (res) ->
                    for u in res
                        Shifts.db.user_ids.push u.UID

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
                alasql "INSERT INTO Shifts (SID, title, shift_start, shift_end) VALUES (#{shift.SID}, '#{shift.title}', '#{shift.start}', '#{shift.end}')", ->
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

        get_my_shifts: (done) ->
            alasql "SELECT * FROM ShiftEntry LEFT JOIN User ON ShiftEntry.UID = User.UID LEFT JOIN Shifts ON ShiftEntry.SID = Shifts.SID", (res) ->
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
        timelane_ticks: [
            { hour: false }
            { hour: false }
            { hour: false }
            { hour: true, time: "6:00" }
            { hour: false }
            { hour: false }
            { hour: false }
            { hour: true, time: "7:00" }
            { hour: false }
            { hour: false }
            { hour: false }
            { hour: true, time: "8:00" }
            { hour: false }
            { hour: false }
            { hour: false }
            { hour: true, time: "9:00" }
            { hour: false }
            { hour: false }
            { hour: false }
            { hour: true, time: "10:00" }
        ]
        shifts: [
            { title: "Schicht 1" }
            { title: "Schicht 2" }
        ]
        shiftplan: ->
            Shifts.db.get_rooms (rooms) ->
                Shifts.db.get_my_shifts (shifts) ->
                    data = {}
                    for room in rooms
                        data[room.RID] = room
                        data[room.RID].shifts = shifts
                    tpl = Mustache.render Shifts.template.shift,
                        lanes: rooms
                        shifts: shifts
                        timelane_ticks: Shifts.render.timelane_ticks
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

    log: (msg) ->
        console.info msg

    template:
        shift: '
<div class="shift-calendar">
  <div class="lane time">
    <div class="header">Time</div>
    {{#timelane_ticks}}
        {{#hour}}
            <div class="tick hour">{{time}}</div>
        {{/hour}}
        {{^hour}}
            <div class="tick"></div>
        {{/hour}}
    {{/timelane_ticks}}
    <div class="tick day">2016-12-27 00:00</div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick hour">11:00</div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick hour">12:00</div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick hour">13:00</div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
  </div>
  <div class="lane">
    <div class="header">
      <span class="glyphicon glyphicon-map-marker"></span> Bottle Sorting (Hall H)
    </div>
    <div class="tick day"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    {{#shifts}}
    <div class="shift panel panel-success" style="height: 160px;">
      <div class="panel-heading">
        <a href="?p=shifts&amp;action=view&amp;shift_id=2696">00:00 ‐ 02:00 — {{title}}</a>
        <div class="pull-right">
          <div class="btn-group">
            <a href="?p=user_shifts&amp;edit_shift=2696" class="btn btn-default btn-xs"> <span class="glyphicon glyphicon-edit"></span>
            </a> <a href="?p=user_shifts&amp;delete_shift=2696" class="btn btn-default btn-xs"> <span class="glyphicon glyphicon-trash"></span>
            </a>
          </div>
        </div>
      </div>
      <div class="panel-body">
        <span class="glyphicon glyphicon-info-sign"></span> Bottle Collection Quick Response Team<br> <a href="?p=rooms&amp;action=view&amp;room_id=42"> <span class="glyphicon glyphicon-map-marker"></span> Bottle Sorting (Hall H)
        </a>
      </div>
      <ul class="list-group">
        <li class="list-group-item"><strong><a href="?p=angeltypes&amp;action=view&amp;angeltype_id=104575">Angel</a>:</strong> <span style=""><a class="" href="?p=users&amp;action=view&amp;user_id=1755"><span class="icon-icon_angel"></span> Pantomime</a></span>, <span style=""><a
            class="" href="?p=users&amp;action=view&amp;user_id=50"><span class="icon-icon_angel"></span> sandzwerg</a></span></li>
        <li class="list-group-item"><a href="?p=user_shifts&amp;shift_id=2696&amp;type_id=104575" class="btn btn-default btn-xs">Neue Engel hinzufügen</a></li>
      </ul>
      <div class="shift-spacer"></div>
    </div>
    {{/shifts}}
    <div class="tick hour"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
  </div>
  <div class="lane">
    <div class="header"></div>
    <div class="tick day"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick hour"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick hour"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick hour"></div>
    <div class="tick"></div>
    <div class="tick"></div>
    <div class="tick"></div>
  </div>
    {{#lanes}}
  <div class="lane">
    <div class="header">
      <span class="glyphicon glyphicon-map-marker"></span> {{Name}}
    </div>
  </div>
    {{/lanes}}
</div>'

}



# document ready
$ ->
    Shifts.init()


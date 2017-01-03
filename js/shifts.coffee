
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
                    # note: primkey doesn't work, see https://github.com/agershun/alasql/issues/566
                    # 'CREATE TABLE IF NOT EXISTS Shifts (SID, title, start, shift_end, PRIMARY KEY (SID))'
                    # alasq.promise is ALSO b0rken, wtf. welcome to callback hell...
                    alasql 'CREATE TABLE IF NOT EXISTS Shifts (SID, title, shift_start, shift_end)', ->
                        alasql 'CREATE TABLE IF NOT EXISTS User (UID, nick)', ->
                            alasql 'CREATE TABLE IF NOT EXISTS Room (RID, Name)', ->
                                alasql 'CREATE TABLE IF NOT EXISTS ShiftEntry (id, SID, TID, UID)', ->
                                    alasql 'CREATE TABLE IF NOT EXISTS options (option_key, option_value)', ->
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

        shiftplan: (shifts) ->
            Shifts.$shiftplan.html Shifts.render.calendar(shifts)

        calendar: (shifts) ->
            return '<div class="shift-calendar">' + Shifts.render.lanes(shifts) + '</div>'

        lanes: (shifts) ->
            lanes = []
            Shifts.db.get_rooms (rooms) ->
                Shifts.log rooms
                return '<div class="lane time">blubb</div>'

    init: ->
        Shifts.$shiftplan = $('#shiftplan')
        if Shifts.$shiftplan.length > 0
            Shifts.log 'shifts init'
            Shifts.db.init ->
                Shifts.log 'db initialized'
                Shifts.fetcher.start ->
                    Shifts.log 'fetch complete.'

                    Shifts.db.get_rooms (rooms) ->
                        Shifts.db.get_my_shifts (shifts) ->
                            data = {}
                            for room in rooms
                                data[room.RID] = room
                                data[room.RID].shifts = shifts
                            Shifts.log data
                            Shifts.$shiftplan.html(Shifts.template.shift)

    log: (msg) ->
        console.info msg

    template:

        shift: '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<link rel="stylesheet" type="text/css" href="css/theme3.css" />
<style>
</style>
</head>
<body>
  <div class="shift-calendar">
    <div class="lane time">
      <div class="header">Time</div>
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
      <div class="shift panel panel-success" style="height: 160px;">
        <div class="panel-heading">
          <a href="?p=shifts&amp;action=view&amp;shift_id=2696">00:00 ‐ 02:00 — Bottle Collection</a>
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
  </div>
</body>
</html>'

}



# document ready
$ ->
    Shifts.init()


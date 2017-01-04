var Shifts;

Shifts = window.Shifts || {
  db: {
    room_ids: [],
    user_ids: [],
    shift_ids: [],
    shiftentry_ids: [],
    init: function(done) {
      Shifts.log('init db');
      return alasql('CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem; ATTACH INDEXEDDB DATABASE engelsystem;', function() {
        return alasql('USE engelsystem', function() {
          return alasql('CREATE TABLE IF NOT EXISTS Shifts (SID, title, shift_start, shift_end); CREATE TABLE IF NOT EXISTS User (UID, nick); CREATE TABLE IF NOT EXISTS Room (RID, Name); CREATE TABLE IF NOT EXISTS ShiftEntry (id, SID, TID, UID); CREATE TABLE IF NOT EXISTS options (option_key, option_value);', function() {
            return Shifts.db.populate_ids(function() {
              return done();
            });
          });
        });
      });
    },
    populate_ids: function(done) {
      return alasql("SELECT RID from Room", function(res) {
        var i, len, r;
        for (i = 0, len = res.length; i < len; i++) {
          r = res[i];
          Shifts.db.room_ids.push(r.RID);
        }
        return alasql("SELECT UID from User", function(res) {
          var j, len1, u;
          for (j = 0, len1 = res.length; j < len1; j++) {
            u = res[j];
            Shifts.db.user_ids.push(u.UID);
          }
          return alasql("SELECT SID from Shifts", function(res) {
            var k, len2, s;
            for (k = 0, len2 = res.length; k < len2; k++) {
              s = res[k];
              Shifts.db.shift_ids.push(s.SID);
            }
            return alasql("SELECT id from ShiftEntry", function(res) {
              var l, len3;
              for (l = 0, len3 = res.length; l < len3; l++) {
                s = res[l];
                Shifts.db.shiftentry_ids.push(s.id);
              }
              return done();
            });
          });
        });
      });
    },
    insert_room: function(room, done) {
      var room_exists;
      room_exists = Shifts.db.room_ids.indexOf(parseInt(room.RID, 10)) > -1;
      if (room_exists === false) {
        return alasql("INSERT INTO Room (RID, Name) VALUES (" + room.RID + ", '" + room.Name + "')", function() {
          Shifts.db.room_ids.push(room.RID);
          return done();
        });
      } else {
        return done();
      }
    },
    insert_user: function(user, done) {
      var user_exists;
      user_exists = Shifts.db.user_ids.indexOf(parseInt(user.UID, 10)) > -1;
      if (user_exists === false) {
        return alasql("INSERT INTO User (UID, Nick) VALUES (" + user.UID + ", '" + user.Nick + "')", function() {
          Shifts.db.user_ids.push(user.UID);
          return done();
        });
      } else {
        return done();
      }
    },
    insert_shift: function(shift, done) {
      var shift_exists;
      shift_exists = Shifts.db.shift_ids.indexOf(parseInt(shift.SID, 10)) > -1;
      if (shift_exists === false) {
        return alasql("INSERT INTO Shifts (SID, title, shift_start, shift_end) VALUES (" + shift.SID + ", '" + shift.title + "', '" + shift.start + "', '" + shift.end + "')", function() {
          Shifts.db.shift_ids.push(shift.SID);
          return done();
        });
      } else {
        return done();
      }
    },
    insert_shiftentry: function(shiftentry, done) {
      var shiftentry_exists;
      shiftentry_exists = Shifts.db.shiftentry_ids.indexOf(parseInt(shiftentry.id, 10)) > -1;
      if (shiftentry_exists === false) {
        return alasql("INSERT INTO ShiftEntry (id, SID, TID, UID) VALUES (" + shiftentry.id + ", '" + shiftentry.SID + "', '" + shiftentry.TID + "', '" + shiftentry.UID + "')", function() {
          Shifts.db.shiftentry_ids.push(shiftentry.id);
          return done();
        });
      } else {
        return done();
      }
    },
    get_my_shifts: function(done) {
      return alasql("SELECT * FROM ShiftEntry LEFT JOIN User ON ShiftEntry.UID = User.UID LEFT JOIN Shifts ON ShiftEntry.SID = Shifts.SID", function(res) {
        return done(res);
      });
    },
    get_rooms: function(done) {
      return alasql("SELECT * FROM Room", function(res) {
        return done(res);
      });
    }
  },
  fetcher: {
    start: function(done) {
      var url;
      url = '?p=shifts_json_export_websql';
      return $.get(url, function(data) {
        var rooms;
        rooms = data.rooms;
        Shifts.$shiftplan.html('fetching rooms...');
        return Shifts.fetcher.process(Shifts.db.insert_room, rooms, function() {
          var users;
          Shifts.log('processing rooms done');
          users = data.users;
          Shifts.$shiftplan.html('fetching users...');
          return Shifts.fetcher.process(Shifts.db.insert_user, users, function() {
            var shifts;
            Shifts.log('processing users done');
            shifts = data.shifts;
            Shifts.$shiftplan.html('fetching shifts...');
            return Shifts.fetcher.process(Shifts.db.insert_shift, shifts, function() {
              var shift_entries;
              Shifts.log('processing shifts done');
              shift_entries = data.shift_entries;
              Shifts.$shiftplan.html('fetching shift entries...');
              return Shifts.fetcher.process(Shifts.db.insert_shiftentry, shift_entries, function() {
                Shifts.log('processing shift_entries done');
                Shifts.$shiftplan.html('done.');
                return done();
              });
            });
          });
        });
      });
    },
    process: function(processing_func, items_to_process, done) {
      var item;
      if (items_to_process.length > 0) {
        item = items_to_process.shift();
        return processing_func(item, function() {
          return Shifts.fetcher.process(processing_func, items_to_process, done);
        });
      } else {
        return done();
      }
    }
  },
  render: {
    timelane_ticks: [
      {
        hour: false
      }, {
        hour: false
      }, {
        hour: false
      }, {
        hour: true,
        time: "6:00"
      }, {
        hour: false
      }, {
        hour: false
      }, {
        hour: false
      }, {
        hour: true,
        time: "7:00"
      }, {
        hour: false
      }, {
        hour: false
      }, {
        hour: false
      }, {
        hour: true,
        time: "8:00"
      }, {
        hour: false
      }, {
        hour: false
      }, {
        hour: false
      }, {
        hour: true,
        time: "9:00"
      }, {
        hour: false
      }, {
        hour: false
      }, {
        hour: false
      }, {
        hour: true,
        time: "10:00"
      }
    ],
    shifts: [
      {
        title: "Schicht 1"
      }, {
        title: "Schicht 2"
      }
    ],
    shiftplan: function() {
      return Shifts.db.get_rooms(function(rooms) {
        return Shifts.db.get_my_shifts(function(shifts) {
          var data, i, len, room, tpl;
          data = {};
          for (i = 0, len = rooms.length; i < len; i++) {
            room = rooms[i];
            data[room.RID] = room;
            data[room.RID].shifts = shifts;
          }
          tpl = Mustache.render(Shifts.template.shift, {
            lanes: rooms,
            shifts: shifts,
            timelane_ticks: Shifts.render.timelane_ticks
          });
          return Shifts.$shiftplan.html(tpl);
        });
      });
    }
  },
  init: function() {
    Shifts.$shiftplan = $('#shiftplan');
    if (Shifts.$shiftplan.length > 0) {
      Shifts.log('shifts init');
      return Shifts.db.init(function() {
        Shifts.log('db initialized');
        return Shifts.fetcher.start(function() {
          Shifts.log('fetch complete.');
          return Shifts.render.shiftplan();
        });
      });
    }
  },
  log: function(msg) {
    return console.info(msg);
  },
  template: {
    shift: '<div class="shift-calendar"> <div class="lane time"> <div class="header">Time</div> {{#timelane_ticks}} {{#hour}} <div class="tick hour">{{time}}</div> {{/hour}} {{^hour}} <div class="tick"></div> {{/hour}} {{/timelane_ticks}} <div class="tick day">2016-12-27 00:00</div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick hour">11:00</div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick hour">12:00</div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick hour">13:00</div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> </div> <div class="lane"> <div class="header"> <span class="glyphicon glyphicon-map-marker"></span> Bottle Sorting (Hall H) </div> <div class="tick day"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> {{#shifts}} <div class="shift panel panel-success" style="height: 160px;"> <div class="panel-heading"> <a href="?p=shifts&amp;action=view&amp;shift_id=2696">00:00 ‐ 02:00 — {{title}}</a> <div class="pull-right"> <div class="btn-group"> <a href="?p=user_shifts&amp;edit_shift=2696" class="btn btn-default btn-xs"> <span class="glyphicon glyphicon-edit"></span> </a> <a href="?p=user_shifts&amp;delete_shift=2696" class="btn btn-default btn-xs"> <span class="glyphicon glyphicon-trash"></span> </a> </div> </div> </div> <div class="panel-body"> <span class="glyphicon glyphicon-info-sign"></span> Bottle Collection Quick Response Team<br> <a href="?p=rooms&amp;action=view&amp;room_id=42"> <span class="glyphicon glyphicon-map-marker"></span> Bottle Sorting (Hall H) </a> </div> <ul class="list-group"> <li class="list-group-item"><strong><a href="?p=angeltypes&amp;action=view&amp;angeltype_id=104575">Angel</a>:</strong> <span style=""><a class="" href="?p=users&amp;action=view&amp;user_id=1755"><span class="icon-icon_angel"></span> Pantomime</a></span>, <span style=""><a class="" href="?p=users&amp;action=view&amp;user_id=50"><span class="icon-icon_angel"></span> sandzwerg</a></span></li> <li class="list-group-item"><a href="?p=user_shifts&amp;shift_id=2696&amp;type_id=104575" class="btn btn-default btn-xs">Neue Engel hinzufügen</a></li> </ul> <div class="shift-spacer"></div> </div> {{/shifts}} <div class="tick hour"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> </div> <div class="lane"> <div class="header"></div> <div class="tick day"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick hour"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick hour"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick hour"></div> <div class="tick"></div> <div class="tick"></div> <div class="tick"></div> </div> {{#lanes}} <div class="lane"> <div class="header"> <span class="glyphicon glyphicon-map-marker"></span> {{Name}} </div> </div> {{/lanes}} </div>'
  }
};

$(function() {
  return Shifts.init();
});

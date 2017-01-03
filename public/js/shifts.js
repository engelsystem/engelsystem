var Shifts;

Shifts = window.Shifts || {
  db: {
    room_ids: [],
    user_ids: [],
    shift_ids: [],
    init: function(done) {
      Shifts.log('init db');
      return alasql('CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem; ATTACH INDEXEDDB DATABASE engelsystem;', function() {
        return alasql('USE engelsystem', function() {
          return alasql('CREATE TABLE IF NOT EXISTS Shifts (SID, title, shift_start, shift_end)', function() {
            return alasql('CREATE TABLE IF NOT EXISTS User (UID, nick)', function() {
              return alasql('CREATE TABLE IF NOT EXISTS Room (RID, Name)', function() {
                return alasql('CREATE TABLE IF NOT EXISTS options (option_key, option_value)', function() {
                  return Shifts.db.populate_ids(function() {
                    return done();
                  });
                });
              });
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
            return done();
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
    }
  },
  fetcher: {
    start: function() {
      var url;
      url = '?p=shifts_json_export_websql';
      return $.get(url, function(data) {
        var rooms, shifts, users;
        rooms = data.rooms;
        Shifts.fetcher.process(Shifts.db.insert_room, rooms, function() {
          return Shifts.log('processing rooms done');
        });
        users = data.users;
        Shifts.fetcher.process(Shifts.db.insert_user, users, function() {
          return Shifts.log('processing users done');
        });
        shifts = data.shifts;
        return Shifts.fetcher.process(Shifts.db.insert_shift, shifts, function() {
          return Shifts.log('processing shifts done');
        });
      });
    },
    process: function(processing_func, items_to_process, done) {
      var item;
      item = items_to_process.shift();
      return processing_func(item, function() {
        if (items_to_process.length > 0) {
          return Shifts.fetcher.process(processing_func, items_to_process, done);
        } else {
          return done();
        }
      });
    }
  },
  init: function() {
    Shifts.log('init');
    return Shifts.db.init(function() {
      Shifts.log('db initialized');
      return Shifts.fetcher.start();
    });
  },
  log: function(msg) {
    return console.info(msg);
  }
};

$(function() {
  return Shifts.init();
});

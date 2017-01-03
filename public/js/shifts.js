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
      return alasql("SELECT SID from Shifts", function(res) {
        var i, len, s;
        for (i = 0, len = res.length; i < len; i++) {
          s = res[i];
          Shifts.db.shift_ids.push(s.SID);
        }
        return done();
      });
    },
    insert_room: function(room, done) {
      return alasql("SELECT RID from Room WHERE RID = " + room.RID, function(res) {
        var error, room_exists;
        try {
          room_exists = res[0].RID !== room.RID;
        } catch (error) {
          room_exists = false;
        }
        if (room_exists === false) {
          return alasql("INSERT INTO Room (RID, Name) VALUES (" + room.RID + ", '" + room.Name + "')", function() {
            return done();
          });
        } else {
          return done();
        }
      });
    },
    insert_user: function(user, done) {
      return alasql("SELECT UID from User WHERE UID = " + user.UID, function(res) {
        var error, user_exists;
        try {
          user_exists = res[0].UID !== user.UID;
        } catch (error) {
          user_exists = false;
        }
        if (user_exists === false) {
          return alasql("INSERT INTO User (UID, Nick) VALUES (" + user.UID + ", '" + user.Nick + "')", function() {
            return done();
          });
        } else {
          return done();
        }
      });
    },
    insert_shift: function(shift, done) {
      var shift_exists;
      Shifts.log("processing shift");
      shift_exists = Shifts.db.shift_ids.indexOf(parseInt(shift.SID, 10)) > -1;
      if (shift_exists === false) {
        return alasql("INSERT INTO Shifts (SID, title, shift_start, shift_end) VALUES (" + shift.SID + ", '" + shift.title + "', '" + shift.start + "', '" + shift.end + "')", function() {
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
        Shifts.log(Shifts.db.shift_ids);
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

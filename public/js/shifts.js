var Shifts;

Shifts = window.Shifts || {
  db: {
    init: function(done) {
      Shifts.log('init db');
      return alasql('CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem; ATTACH INDEXEDDB DATABASE engelsystem;', function() {
        return alasql('USE engelsystem', function() {
          return alasql('CREATE TABLE IF NOT EXISTS Shifts (SID, title, shift_start, shift_end)', function() {
            return alasql('CREATE TABLE IF NOT EXISTS User (UID, nick)', function() {
              return alasql('CREATE TABLE IF NOT EXISTS Room (RID, Name)', function() {
                return alasql('CREATE TABLE IF NOT EXISTS options (option_key, option_value)', function() {
                  return done();
                });
              });
            });
          });
        });
      });
    },
    insert_room: function(room_id, name, done) {
      return alasql("SELECT RID from Room WHERE RID = " + room_id, function(res) {
        var error, room_exists;
        try {
          room_exists = res[0].RID !== room_id;
        } catch (error) {
          room_exists = false;
        }
        if (room_exists === false) {
          return alasql("INSERT INTO Room (RID, Name) VALUES (" + room_id + ", '" + name + "')", function() {
            return done();
          });
        } else {
          return done();
        }
      });
    }
  },
  fetcher: {
    start: function() {
      var url;
      url = '?p=shifts_json_export_websql';
      return $.get(url, function(data) {
        var rooms;
        rooms = data.rooms;
        return Shifts.fetcher.process(rooms, function() {
          return Shifts.log('processing rooms done');
        });
      });
    },
    process: function(rooms, done) {
      var room;
      room = rooms.shift();
      return Shifts.db.insert_room(room.RID, room.Name, function() {
        if (rooms.length > 0) {
          return Shifts.fetcher.process(rooms, done);
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

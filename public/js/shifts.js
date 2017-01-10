var Shifts;

Shifts = window.Shifts || {};

Shifts.init = function() {
  Shifts.$shiftplan = $('#shiftplan');
  if (Shifts.$shiftplan.length > 0) {
    Shifts.log('shifts init');
    Shifts.db.init(function() {
      Shifts.log('db initialized');
      return Shifts.fetcher.start(function() {
        Shifts.log('fetch complete.');
        return Shifts.render.shiftplan();
      });
    });
    return $('body').on('click', '#filterbutton', function() {
      Shifts.render.shiftplan();
      return false;
    });
  }
};

Shifts.log = function(msg) {
  return console.info(msg);
};

$(function() {
  return Shifts.init();
});

Shifts.db = {
  room_ids: [],
  user_ids: [],
  shift_ids: [],
  shiftentry_ids: [],
  shifttype_ids: [],
  angeltype_ids: [],
  init: function(done) {
    Shifts.log('init db');
    return alasql('CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem; ATTACH INDEXEDDB DATABASE engelsystem;', function() {
      return alasql('USE engelsystem', function() {
        return alasql('CREATE TABLE IF NOT EXISTS Shifts (SID INT, title, shifttype_id INT, start_time INT, end_time INT, RID INT); CREATE TABLE IF NOT EXISTS User (UID INT, nick); CREATE TABLE IF NOT EXISTS Room (RID INT, Name); CREATE TABLE IF NOT EXISTS ShiftEntry (id INT, SID INT, TID INT, UID INT); CREATE TABLE IF NOT EXISTS ShiftTypes (id INT, name, angeltype_id INT); CREATE TABLE IF NOT EXISTS AngelTypes (id INT, name); CREATE TABLE IF NOT EXISTS options (option_key, option_value);', function() {
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
        return alasql("SELECT id from ShiftTypes", function(res) {
          var k, len2, s;
          for (k = 0, len2 = res.length; k < len2; k++) {
            s = res[k];
            Shifts.db.shifttype_ids.push(s.id);
          }
          return alasql("SELECT id from AngelTypes", function(res) {
            var l, len3;
            for (l = 0, len3 = res.length; l < len3; l++) {
              s = res[l];
              Shifts.db.angeltype_ids.push(s.id);
            }
            return alasql("SELECT SID from Shifts", function(res) {
              var len4, m;
              for (m = 0, len4 = res.length; m < len4; m++) {
                s = res[m];
                Shifts.db.shift_ids.push(s.SID);
              }
              return alasql("SELECT id from ShiftEntry", function(res) {
                var len5, n;
                for (n = 0, len5 = res.length; n < len5; n++) {
                  s = res[n];
                  Shifts.db.shiftentry_ids.push(s.id);
                }
                return done();
              });
            });
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
      return alasql("INSERT INTO Shifts (SID, title, shifttype_id, start_time, end_time, RID) VALUES (" + shift.SID + ", '" + shift.title + "', '" + shift.shifttype_id + "', '" + shift.start + "', '" + shift.end + "', '" + shift.RID + "')", function() {
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
  insert_shifttype: function(shifttype, done) {
    var shifttype_exists;
    shifttype.id = parseInt(shifttype.id, 10);
    shifttype_exists = Shifts.db.shifttype_ids.indexOf(shifttype.id) > -1;
    if (shifttype_exists === false) {
      return alasql("INSERT INTO ShiftTypes (id, name) VALUES (" + shifttype.id + ", '" + shifttype.name + "')", function() {
        Shifts.db.shifttype_ids.push(shifttype.id);
        return done();
      });
    } else {
      return done();
    }
  },
  insert_angeltype: function(angeltype, done) {
    var angeltype_exists;
    angeltype.id = parseInt(angeltype.id, 10);
    angeltype_exists = Shifts.db.angeltype_ids.indexOf(angeltype.id) > -1;
    if (angeltype_exists === false) {
      return alasql("INSERT INTO AngelTypes (id, name) VALUES (" + angeltype.id + ", '" + angeltype.name + "')", function() {
        Shifts.db.angeltype_ids.push(angeltype.id);
        return done();
      });
    } else {
      return done();
    }
  },
  get_my_shifts: function(done) {
    var end_time, rand, start_time;
    rand = 1 + parseInt(Math.random() * 10, 10);
    rand = 2000;
    start_time = Shifts.render.get_starttime();
    end_time = Shifts.render.get_endtime();
    return alasql("SELECT Shifts.SID, Shifts.title as shift_title, Shifts.shifttype_id, Shifts.start_time, Shifts.end_time, Shifts.RID, ShiftTypes.name as shifttype_name, Room.Name as room_name FROM Shifts LEFT JOIN ShiftTypes ON ShiftTypes.id = Shifts.shifttype_id LEFT JOIN Room ON Room.RID = Shifts.RID WHERE Shifts.start_time >= " + start_time + " AND Shifts.end_time <= " + end_time + " ORDER BY Shifts.start_time LIMIT " + rand, function(res) {
      return done(res);
    });
  },
  get_rooms: function(done) {
    return alasql("SELECT * FROM Room ORDER BY Name", function(res) {
      return done(res);
    });
  },
  get_angeltypes: function(done) {
    return alasql("SELECT * FROM AngelTypes ORDER BY name", function(res) {
      return done(res);
    });
  }
};

Shifts.fetcher = {
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
          var shift_types;
          Shifts.log('processing users done');
          shift_types = data.shift_types;
          Shifts.$shiftplan.html('fetching shift_types...');
          return Shifts.fetcher.process(Shifts.db.insert_shifttype, shift_types, function() {
            var angeltypes;
            Shifts.log('processing shift_types done');
            angeltypes = data.angeltypes;
            Shifts.$shiftplan.html('fetching angeltypes...');
            return Shifts.fetcher.process(Shifts.db.insert_angeltype, angeltypes, function() {
              var shifts;
              Shifts.log('processing angeltypes done');
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
};

Shifts.render = {
  SECONDS_PER_ROW: 900,
  BLOCK_HEIGHT: 30,
  MARGIN: 5,
  TIME_MARGIN: 1800,
  tick: function(time, label) {
    if (label == null) {
      label = false;
    }
    if (time % (24 * 60 * 60) === 23 * 60 * 60) {
      if (label) {
        return {
          tick_day: true,
          label: moment.unix(time).format('MM-DD HH:mm')
        };
      } else {
        return {
          tick_day: true
        };
      }
    } else if (time % (60 * 60) === 0) {
      if (label) {
        return {
          tick_hour: true,
          label: moment.unix(time).format('HH:mm')
        };
      } else {
        return {
          tick_hour: true
        };
      }
    } else {
      return {
        tick: true
      };
    }
  },
  get_starttime: function(margin) {
    var start_time;
    if (margin == null) {
      margin = false;
    }
    start_time = moment(moment().format('YYYY-MM-DD')).format('X');
    start_time = parseInt(start_time, 10);
    if (margin) {
      start_time = start_time - Shifts.render.TIME_MARGIN;
    }
    return start_time;
  },
  get_endtime: function(margin) {
    var end_time;
    if (margin == null) {
      margin = false;
    }
    end_time = Shifts.render.get_starttime() + 24 * 60 * 60;
    if (margin) {
      end_time = end_time + Shifts.render.TIME_MARGIN;
    }
    return end_time;
  },
  timelane: function() {
    var end_time, start_time, thistime, time_slot;
    time_slot = [];
    start_time = Shifts.render.get_starttime(true);
    end_time = Shifts.render.get_endtime(true);
    thistime = start_time;
    while (thistime < end_time) {
      time_slot.push(Shifts.render.tick(thistime, true));
      thistime += Shifts.render.SECONDS_PER_ROW;
    }
    return time_slot;
  },
  shiftplan: function() {
    return Shifts.db.get_rooms(function(rooms) {
      return Shifts.db.get_angeltypes(function(angeltypes) {
        return Shifts.db.get_my_shifts(function(db_shifts) {
          var add_shift, end_time, firstblock_starttime, highest_lane_nr, i, j, lane, lane_nr, lanes, lastblock_endtime, len, len1, mustache_rooms, ref, rendered_until, room_id, room_nr, shift, shift_added, shift_fits, shift_nr, start_time, tpl;
          lanes = {};
          add_shift = function(shift, room_id) {
            var blocks, height, lane_nr;
            if (shift.shift_title === "null") {
              shift.shift_title = null;
            }
            shift.starttime = moment.unix(shift.start_time).format('HH:mm');
            shift.endtime = moment.unix(shift.end_time).format('HH:mm');
            blocks = Math.ceil(shift.end_time - shift.start_time) / Shifts.render.SECONDS_PER_ROW;
            blocks = Math.max(1, blocks);
            height = blocks * Shifts.render.BLOCK_HEIGHT - Shifts.render.MARGIN;
            shift.blocks = blocks;
            shift.height = height;
            for (lane_nr in lanes[room_id]) {
              if (shift_fits(shift, room_id, lane_nr)) {
                lanes[room_id][lane_nr].push(shift);
                return true;
              }
            }
            return false;
          };
          shift_fits = function(shift, room_id, lane_nr) {
            var i, lane_shift, len, ref;
            ref = lanes[room_id][lane_nr];
            for (i = 0, len = ref.length; i < len; i++) {
              lane_shift = ref[i];
              if (!(shift.start_time >= lane_shift.end_time || shift.end_time <= lane_shift.start_time)) {
                return false;
              }
            }
            return true;
          };
          start_time = Shifts.render.get_starttime(true);
          end_time = Shifts.render.get_endtime(true);
          firstblock_starttime = end_time;
          lastblock_endtime = start_time;
          for (i = 0, len = db_shifts.length; i < len; i++) {
            shift = db_shifts[i];
            if (shift.start_time < firstblock_starttime) {
              firstblock_starttime = shift.start_time;
            }
            if (shift.end_time > lastblock_endtime) {
              lastblock_endtime = shift.end_time;
            }
            room_id = shift.RID;
            if (typeof lanes[room_id] === "undefined") {
              lanes[room_id] = [[]];
            }
            shift_added = false;
            ref = lanes[room_id];
            for (j = 0, len1 = ref.length; j < len1; j++) {
              lane = ref[j];
              shift_added = add_shift(shift, room_id);
              if (shift_added) {
                break;
              }
            }
            if (!shift_added) {
              Shifts.log("lane is full, adding new one");
              lanes[room_id].push([]);
              highest_lane_nr = lanes[room_id].length - 1;
              add_shift(shift, room_id);
            }
          }
          mustache_rooms = [];
          for (room_nr in rooms) {
            room_id = rooms[room_nr].RID;
            mustache_rooms[room_nr] = {};
            mustache_rooms[room_nr].Name = rooms[room_nr].Name;
            mustache_rooms[room_nr].lanes = [];
            for (lane_nr in lanes[room_id]) {
              mustache_rooms[room_nr].lanes[lane_nr] = {};
              mustache_rooms[room_nr].lanes[lane_nr].shifts = [];
              rendered_until = firstblock_starttime - Shifts.render.TIME_MARGIN;
              for (shift_nr in lanes[room_id][lane_nr]) {
                while (rendered_until + Shifts.render.SECONDS_PER_ROW <= lanes[room_id][lane_nr][shift_nr].start_time) {
                  mustache_rooms[room_nr].lanes[lane_nr].shifts.push(Shifts.render.tick(rendered_until, true));
                  rendered_until += Shifts.render.SECONDS_PER_ROW;
                }
                mustache_rooms[room_nr].lanes[lane_nr].shifts.push({
                  shift: lanes[room_id][lane_nr][shift_nr]
                });
                rendered_until += lanes[room_id][lane_nr][shift_nr].blocks * Shifts.render.SECONDS_PER_ROW;
              }
              while (rendered_until < lastblock_endtime) {
                mustache_rooms[room_nr].lanes[lane_nr].shifts.push(Shifts.render.tick(rendered_until, true));
                rendered_until += Shifts.render.SECONDS_PER_ROW;
              }
            }
          }
          tpl = '';
          tpl += Mustache.render(Shifts.templates.filter_form, {
            rooms: rooms,
            angeltypes: angeltypes
          });
          tpl += Mustache.render(Shifts.templates.shift_calendar, {
            timelane_ticks: Shifts.render.timelane(),
            rooms: mustache_rooms
          });
          return Shifts.$shiftplan.html(tpl);
        });
      });
    });
  }
};

Shifts.templates = {
  filter_form: '<form class="form-inline" action="" method="get"> <input type="hidden" name="p" value="user_shifts"> <div class="row"> <div class="col-md-6"> <h1>Shifts</h1> <div class="form-group"> <select class="form-control" id="start_day" name="start_day"> <option value="2017-01-08">2017-01-08</option> <option value="2017-01-09">2017-01-09</option> <option value="2017-01-10" selected="selected">2017-01-10</option> <option value="2017-01-11">2017-01-11</option> <option value="2017-01-12">2017-01-12</option> <option value="2017-01-13">2017-01-13</option> <option value="2017-01-14">2017-01-14</option> <option value="2017-01-15">2017-01-15</option> <option value="2017-01-16">2017-01-16</option> </select> </div> <div class="form-group"> <div class="input-group"> <input class="form-control" type="text" id="start_time" name="start_time" size="5" pattern="^\d{1,2}:\d{2}$" placeholder="HH:MM" maxlength="5" value="00:00"> <div class="input-group-btn"> <button class="btn btn-default" title="Now" type="button"> <span class="glyphicon glyphicon-time"></span> </button> </div> </div> </div> &#8211; <div class="form-group"> <select class="form-control" id="start_day" name="start_day"> <option value="2017-01-08">2017-01-08</option> <option value="2017-01-09">2017-01-09</option> <option value="2017-01-10">2017-01-10</option> <option value="2017-01-11" selected="selected">2017-01-11</option> <option value="2017-01-12">2017-01-12</option> <option value="2017-01-13">2017-01-13</option> <option value="2017-01-14">2017-01-14</option> <option value="2017-01-15">2017-01-15</option> <option value="2017-01-16">2017-01-16</option> </select> </div> <div class="form-group"> <div class="input-group"> <input class="form-control" type="text" id="end_time" name="end_time" size="5" pattern="^\d{1,2}:\d{2}$" placeholder="HH:MM" maxlength="5" value="00:00"> <div class="input-group-btn"> <button class="btn btn-default" title="Now" type="button"> <span class="glyphicon glyphicon-time"></span> </button> </div> </div> </div> </div> <div class="col-md-2"> <div id="selection_types" class="selection types"> <h4>Rooms</h4> {{#rooms}} <div class="checkbox"><label><input type="checkbox" name="rooms[]" value="{{RID}}" checked="checked"> {{Name}}</label></div><br /> {{/rooms}} <div class="form-group"> <div class="btn-group"> <a href="" class="btn btn-default ">All</a> <a href="" class="btn btn-default ">None</a> </div> </div> </div> </div> <div class="col-md-2"> <div id="selection_types" class="selection types"> <h4>Angeltypes<sup>1</sup></h4> {{#angeltypes}} <div class="checkbox"><label><input type="checkbox" name="types[]" value="{{id}}" checked="checked"> {{name}}</label></div><br /> {{/angeltypes}} <div class="form-group"> <div class="btn-group"> <a href="" class="btn btn-default ">All</a> <a href="" class="btn btn-default ">None</a> </div> </div> </div> </div> <div class="col-md-2"> <div id="selection_filled" class="selection filled"> <h4>Occupancy</h4> <div class="checkbox"><label><input type="checkbox" name="filled[]" value="1" checked="checked"> occupied</label></div><br /> <div class="checkbox"><label><input type="checkbox" name="filled[]" value="0" checked="checked"> free</label></div><br /> <div class="form-group"> <div class="btn-group"> <a href="" class="btn btn-default ">All</a> <a href="" class="btn btn-default ">None</a> </div> </div> </div> </div> </div> <div class="row"> <div class="col-md-6"> <div><sup>1</sup>The tasks shown here are influenced by the angeltypes you joined already! <a href="?p=angeltypes&amp;action=about">Description of the jobs.</a></div> <input id="filterbutton" class="btn btn-primary" type="submit" style="width: 75%; margin-bottom: 20px" value="Filter"> </div> </div> </form>',
  shift_calendar: '<div class="shift-calendar"> <div class="lane time"> <div class="header">Time</div> {{#timelane_ticks}} {{#tick}} <div class="tick"></div> {{/tick}} {{#tick_hour}} <div class="tick hour">{{label}}</div> {{/tick_hour}} {{#tick_day}} <div class="tick day">{{label}}</div> {{/tick_day}} {{/timelane_ticks}} </div> {{#rooms}} {{#lanes}} <div class="lane"> <div class="header"> <a href="?p=rooms&action=view&room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{Name}}</a> </div> {{#shifts}} {{#tick}} <div class="tick"></div> {{/tick}} {{#tick_hour}} <div class="tick hour">{{text}}</div> {{/tick_hour}} {{#tick_day}} <div class="tick day">{{text}}</div> {{/tick_day}} {{#shift}} <div class="shift panel panel-success" style="height: {{height}}px;"> <div class="panel-heading"> <a href="?p=shifts&amp;action=view&amp;shift_id=2696">{{starttime}} ‐ {{endtime}} — {{shifttype_name}}</a> <div class="pull-right"> <div class="btn-group"> <a href="?p=user_shifts&amp;edit_shift=2696" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span></a> <a href="?p=user_shifts&amp;delete_shift=2696" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-trash"></span></a> </div> </div> </div> <div class="panel-body"> {{#shift_title}}<span class="glyphicon glyphicon-info-sign"></span> {{shift_title}}<br />{{/shift_title}} <a href="?p=rooms&amp;action=view&amp;room_id=42"><span class="glyphicon glyphicon-map-marker"></span> {{room_name}}</a> </div> <ul class="list-group"> <li class="list-group-item"><strong><a href="?p=angeltypes&amp;action=view&amp;angeltype_id=104575">Angel</a>:</strong> <span style=""><a class="" href="?p=users&amp;action=view&amp;user_id=1755"><span class="icon-icon_angel"></span> Pantomime</a></span>, <span style=""><a class="" href="?p=users&amp;action=view&amp;user_id=50"><span class="icon-icon_angel"></span> sandzwerg</a></span> </li> <li class="list-group-item"> <a href="?p=user_shifts&amp;shift_id=2696&amp;type_id=104575" class="btn btn-default btn-xs">Neue Engel hinzufügen</a> </li> </ul> <div class="shift-spacer"></div> </div> {{/shift}} {{/shifts}} </div> {{/lanes}} {{/rooms}} </div>'
};

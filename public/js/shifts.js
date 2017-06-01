var Shifts,
  indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

Shifts = window.Shifts || {};

Shifts.init = function() {
  Shifts.$shiftplan = $('#shiftplan');
  if (Shifts.$shiftplan.length > 0) {
    Shifts.log('shifts init');
    return Shifts.db.init(function() {
      Shifts.log('db initialized');
      return Shifts.fetcher.start(function() {
        Shifts.log('fetch complete.');
        Shifts.render.shiftplan();
        return Shifts.interaction.init();
      });
    });
  }
};

Shifts.log = function(msg) {
  return console.log(msg);
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
  needed_angeltype_ids: [],
  option_keys: [],
  prefix: '',
  init: function(done) {
    try {
      Shifts.db.prefix = '_' + Shifts.db.slugify($('.footer').html().split('<br>')[0]);
    } catch (error) {
      Shifts.db.prefix = '';
    }
    Shifts.log('init db');
    return alasql('CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem' + Shifts.db.prefix + '; ATTACH INDEXEDDB DATABASE engelsystem' + Shifts.db.prefix + ';', function() {
      return alasql('USE engelsystem' + Shifts.db.prefix + ';', function() {
        return alasql('CREATE TABLE IF NOT EXISTS Shifts (SID INT, title, shifttype_id INT, start_time INT, end_time INT, RID INT); CREATE TABLE IF NOT EXISTS User (UID INT, nick); CREATE TABLE IF NOT EXISTS Room (RID INT, Name); CREATE TABLE IF NOT EXISTS ShiftEntry (id INT, SID INT, TID INT, UID INT); CREATE TABLE IF NOT EXISTS ShiftTypes (id INT, name, angeltype_id INT); CREATE TABLE IF NOT EXISTS AngelTypes (id INT, name); CREATE TABLE IF NOT EXISTS NeededAngelTypes (id INT, room_id INT, shift_id INT, angel_type_id INT, angel_count INT); CREATE TABLE IF NOT EXISTS options (option_key, option_value);', function() {
          return Shifts.db.populate_ids(function() {
            return done();
          });
        });
      });
    });
  },
  slugify: function(text) {
    return text.toString().toLowerCase().replace(/^[\s|\-|_]+/, '').replace(/[\s|\-|_]+$/, '').replace(/\s+/g, '_').replace(/__+/g, '_').replace(/[^\w\-]+/g, '');
  },
  populate_ids: function(done) {
    return alasql("SELECT RID from Room", function(res) {
      var i, len, r;
      for (i = 0, len = res.length; i < len; i++) {
        r = res[i];
        Shifts.db.room_ids.push(r.RID);
        Shifts.interaction.selected_rooms.push(r.RID);
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
            var a, l, len3;
            for (l = 0, len3 = res.length; l < len3; l++) {
              a = res[l];
              Shifts.db.angeltype_ids.push(a.id);
              Shifts.interaction.selected_angeltypes.push(a.id);
            }
            return alasql("SELECT id from NeededAngelTypes", function(res) {
              var len4, m;
              for (m = 0, len4 = res.length; m < len4; m++) {
                a = res[m];
                Shifts.db.needed_angeltype_ids.push(a.id);
              }
              return alasql("SELECT SID from Shifts", function(res) {
                var len5, n;
                for (n = 0, len5 = res.length; n < len5; n++) {
                  s = res[n];
                  Shifts.db.shift_ids.push(s.SID);
                }
                return alasql("SELECT id from ShiftEntry", function(res) {
                  var len6, p;
                  for (p = 0, len6 = res.length; p < len6; p++) {
                    s = res[p];
                    Shifts.db.shiftentry_ids.push(s.id);
                  }
                  return alasql("SELECT option_key from options", function(res) {
                    var len7, o, q;
                    for (q = 0, len7 = res.length; q < len7; q++) {
                      o = res[q];
                      Shifts.db.option_keys.push(o.option_key);
                    }
                    return done();
                  });
                });
              });
            });
          });
        });
      });
    });
  },
  insert_room: function(room, done) {
    var ref, room_exists;
    room.RID = parseInt(room.RID, 10);
    room_exists = (ref = room.RID, indexOf.call(Shifts.db.room_ids, ref) >= 0);
    if (room_exists === false) {
      return alasql("INSERT INTO Room (RID, Name) VALUES (" + room.RID + ", '" + room.Name + "')", function() {
        Shifts.db.room_ids.push(room.RID);
        Shifts.interaction.selected_rooms.push(room.RID);
        return done();
      });
    } else {
      return done();
    }
  },
  insert_user: function(user, done) {
    var ref, user_exists;
    user.UID = parseInt(user.UID, 10);
    user_exists = (ref = user.UID, indexOf.call(Shifts.db.user_ids, ref) >= 0);
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
    var ref, shift_exists;
    shift.SID = parseInt(shift.SID, 10);
    shift.RID = parseInt(shift.RID, 10);
    shift_exists = (ref = shift.SID, indexOf.call(Shifts.db.shift_ids, ref) >= 0);
    if (shift_exists === false) {
      return alasql("INSERT INTO Shifts (SID, title, shifttype_id, start_time, end_time, RID) VALUES (" + shift.SID + ", '" + shift.title + "', '" + shift.shifttype_id + "', '" + shift.start + "', '" + shift.end + "', " + shift.RID + ")", function() {
        Shifts.db.shift_ids.push(shift.SID);
        return done();
      });
    } else {
      return done();
    }
  },
  insert_shiftentry: function(shiftentry, done) {
    var ref, shiftentry_exists;
    shiftentry.id = parseInt(shiftentry.id, 10);
    shiftentry.SID = parseInt(shiftentry.SID, 10);
    shiftentry.TID = parseInt(shiftentry.TID, 10);
    shiftentry.UID = parseInt(shiftentry.UID, 10);
    shiftentry_exists = (ref = shiftentry.id, indexOf.call(Shifts.db.shiftentry_ids, ref) >= 0);
    if (shiftentry_exists === false) {
      return alasql("INSERT INTO ShiftEntry (id, SID, TID, UID) VALUES (" + shiftentry.id + ", " + shiftentry.SID + ", " + shiftentry.TID + ", " + shiftentry.UID + ")", function() {
        Shifts.db.shiftentry_ids.push(shiftentry.id);
        return done();
      });
    } else {
      return done();
    }
  },
  insert_shifttype: function(shifttype, done) {
    var ref, shifttype_exists;
    shifttype.id = parseInt(shifttype.id, 10);
    shifttype_exists = (ref = shifttype.id, indexOf.call(Shifts.db.shifttype_ids, ref) >= 0);
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
    var angeltype_exists, ref;
    angeltype.id = parseInt(angeltype.id, 10);
    angeltype_exists = (ref = angeltype.id, indexOf.call(Shifts.db.angeltype_ids, ref) >= 0);
    if (angeltype_exists === false) {
      return alasql("INSERT INTO AngelTypes (id, name) VALUES (" + angeltype.id + ", '" + angeltype.name + "')", function() {
        Shifts.db.angeltype_ids.push(angeltype.id);
        Shifts.interaction.selected_angeltypes.push(angeltype.id);
        return done();
      });
    } else {
      return done();
    }
  },
  insert_needed_angeltype: function(needed_angeltype, done) {
    var needed_angeltype_exists, ref;
    needed_angeltype.id = parseInt(needed_angeltype.id, 10);
    needed_angeltype.RID = parseInt(needed_angeltype.RID, 10) || null;
    needed_angeltype.SID = parseInt(needed_angeltype.SID, 10) || null;
    needed_angeltype.ATID = parseInt(needed_angeltype.ATID, 10);
    needed_angeltype.count = parseInt(needed_angeltype.count, 10);
    needed_angeltype_exists = (ref = needed_angeltype.id, indexOf.call(Shifts.db.needed_angeltype_ids, ref) >= 0);
    if (needed_angeltype_exists === false) {
      return alasql("INSERT INTO NeededAngelTypes (id, room_id, shift_id, angel_type_id, angel_count) VALUES (" + needed_angeltype.id + ", " + needed_angeltype.RID + ", " + needed_angeltype.SID + ", " + needed_angeltype.ATID + ", " + needed_angeltype.count + ")", function() {
        Shifts.db.needed_angeltype_ids.push(needed_angeltype.id);
        return done();
      });
    } else {
      return done();
    }
  },
  get_shifts: function(filter_rooms, filter_angeltypes, done) {
    var end_time, filter_angeltypes_ids, filter_rooms_ids, start_time;
    filter_rooms_ids = filter_rooms.join(',');
    filter_angeltypes_ids = filter_angeltypes.join(',');
    start_time = Shifts.render.get_starttime();
    end_time = Shifts.render.get_endtime();
    return alasql("SELECT DISTINCT Shifts.SID, Shifts.title as shift_title, Shifts.shifttype_id, Shifts.start_time, Shifts.end_time, Shifts.RID, ShiftTypes.name as shifttype_name, Room.Name as room_name FROM NeededAngelTypes JOIN Shifts ON Shifts.SID = NeededAngelTypes.shift_id JOIN Room ON Room.RID = Shifts.RID JOIN ShiftTypes ON ShiftTypes.id = Shifts.shifttype_id WHERE NeededAngelTypes.angel_count > 0 AND Shifts.start_time >= " + start_time + " AND Shifts.end_time <= " + end_time + " AND Shifts.RID IN (" + filter_rooms_ids + ") AND NeededAngelTypes.angel_type_id IN (" + filter_angeltypes_ids + ") ORDER BY Shifts.start_time, Shifts.SID", function(res) {
      return done(res);
    });
  },
  get_shiftentries: function(filter_rooms, filter_angeltypes, done) {
    var end_time, filter_angeltypes_ids, filter_rooms_ids, start_time;
    filter_rooms_ids = filter_rooms.join(',');
    filter_angeltypes_ids = filter_angeltypes.join(',');
    start_time = Shifts.render.get_starttime();
    end_time = Shifts.render.get_endtime();
    return alasql("SELECT DISTINCT ShiftEntry.SID, ShiftEntry.TID, ShiftEntry.UID, User.Nick, AngelTypes.name as at_name FROM ShiftEntry JOIN User ON ShiftEntry.UID = User.UID JOIN Shifts ON ShiftEntry.SID = Shifts.SID JOIN AngelTypes ON ShiftEntry.TID = AngelTypes.id WHERE Shifts.start_time >= " + start_time + " AND Shifts.end_time <= " + end_time + " AND Shifts.RID IN (" + filter_rooms_ids + ") AND ShiftEntry.TID IN (" + filter_angeltypes_ids + ") ORDER BY ShiftEntry.SID", function(res) {
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
  },
  get_option: function(key, done) {
    return alasql("SELECT * FROM options WHERE option_key = '" + key + "' LIMIT 1", function(res) {
      try {
        return done(res[0].option_value);
      } catch (error) {
        return done(false);
      }
    });
  },
  set_option: function(key, value, done) {
    var option_key_exists;
    option_key_exists = indexOf.call(Shifts.db.option_keys, key) >= 0;
    if (option_key_exists === false) {
      return alasql("INSERT INTO options (option_key, option_value) VALUES ('" + key + "', '" + value + "')", function() {
        Shifts.db.option_keys.push(key);
        return done();
      });
    } else {
      return alasql("UPDATE options SET option_value = '" + value + "' WHERE option_key = '" + key + "'", function() {
        return done();
      });
    }
  }
};

Shifts.fetcher = {
  start: function(done) {
    var url;
    url = '?p=shifts_json_export_websql';
    return $.get(url, function(data) {
      return Shifts.db.get_option('filter_start_time', function(res) {
        var rooms;
        if (res) {
          Shifts.render.START_TIME = parseInt(res, 10);
        }
        rooms = data.rooms;
        Shifts.$shiftplan.html('fetching rooms...');
        return Shifts.fetcher.process(Shifts.db.insert_room, rooms, function() {
          var angeltypes;
          Shifts.log('processing rooms done');
          angeltypes = data.angeltypes;
          Shifts.$shiftplan.html('fetching angeltypes...');
          return Shifts.fetcher.process(Shifts.db.insert_angeltype, angeltypes, function() {
            var shift_types;
            Shifts.log('processing angeltypes done');
            shift_types = data.shift_types;
            Shifts.$shiftplan.html('fetching shift_types...');
            return Shifts.fetcher.process(Shifts.db.insert_shifttype, shift_types, function() {
              var users;
              Shifts.log('processing shift_types done');
              users = data.users;
              Shifts.$shiftplan.html('fetching users...');
              return Shifts.fetcher.process(Shifts.db.insert_user, users, function() {
                var shifts;
                Shifts.log('processing users done');
                shifts = data.shifts;
                Shifts.$shiftplan.html('fetching shifts...');
                return Shifts.fetcher.process(Shifts.db.insert_shift, shifts, function() {
                  var needed_angeltypes;
                  Shifts.log('processing shifts done');
                  needed_angeltypes = data.needed_angeltypes;
                  Shifts.$shiftplan.html('fetching needed_angeltypes...');
                  return Shifts.fetcher.process(Shifts.db.insert_needed_angeltype, needed_angeltypes, function() {
                    var shift_entries;
                    Shifts.log('processing needed_angeltypes done');
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

Shifts.interaction = {
  selected_rooms: [],
  selected_angeltypes: [],
  selected_occupancy: [],
  datepicker_interval: false,
  init: function() {
    Shifts.interaction.selected_occupancy.push('1');
    Shifts.interaction.selected_occupancy.push('0');
    Shifts.interaction.on_filter_change();
    Shifts.interaction.on_mass_select();
    return Shifts.interaction.on_filter_click();
  },
  on_filter_change: function() {
    Shifts.$shiftplan.on('change', '#selection_rooms input', function() {
      var i, len, ref, room;
      Shifts.interaction.selected_rooms = [];
      ref = $('#selection_rooms input');
      for (i = 0, len = ref.length; i < len; i++) {
        room = ref[i];
        if (room.checked) {
          Shifts.interaction.selected_rooms.push(parseInt(room.value, 10));
        }
      }
      return Shifts.render.shiftplan();
    });
    return Shifts.$shiftplan.on('change', '#selection_types input', function() {
      var i, len, ref, type;
      Shifts.interaction.selected_angeltypes = [];
      ref = $('#selection_types input');
      for (i = 0, len = ref.length; i < len; i++) {
        type = ref[i];
        if (type.checked) {
          Shifts.interaction.selected_angeltypes.push(parseInt(type.value, 10));
        }
      }
      return Shifts.render.shiftplan();
    });
  },
  on_mass_select: function() {
    return Shifts.$shiftplan.on('click', '.mass-select a', function(ev) {
      var i, j, k, len, len1, len2, occupancy, ref, ref1, ref2, room, type;
      if ($(this).parents('#selection_rooms').length) {
        if ($(ev.target).attr('href') === '#all') {
          ref = $('#selection_rooms input');
          for (i = 0, len = ref.length; i < len; i++) {
            room = ref[i];
            Shifts.interaction.selected_rooms.push(parseInt(room.value, 10));
          }
        }
        if ($(ev.target).attr('href') === '#none') {
          Shifts.interaction.selected_rooms = [];
        }
      }
      if ($(this).parents('#selection_types').length) {
        Shifts.log('dagg');
        if ($(ev.target).attr('href') === '#all') {
          ref1 = $('#selection_types input');
          for (j = 0, len1 = ref1.length; j < len1; j++) {
            type = ref1[j];
            Shifts.interaction.selected_angeltypes.push(parseInt(type.value, 10));
          }
        }
        if ($(ev.target).attr('href') === '#none') {
          Shifts.interaction.selected_angeltypes = [];
        }
      }
      if ($(this).parents('#selection_filled').length) {
        if ($(ev.target).attr('href') === '#all') {
          ref2 = $('#selection_filled input');
          for (k = 0, len2 = ref2.length; k < len2; k++) {
            occupancy = ref2[k];
            Shifts.interaction.selected_occupancy.push(parseInt(occupancy.value, 10));
          }
        }
        if ($(ev.target).attr('href') === '#none') {
          Shifts.interaction.selected_occupancy = [];
        }
      }
      Shifts.render.shiftplan();
      return false;
    });
  },
  on_filter_click: function() {
    return Shifts.$shiftplan.on('click', '#filterbutton', function() {
      Shifts.render.shiftplan();
      return false;
    });
  }
};

Shifts.render = {
  SECONDS_PER_ROW: 900,
  BLOCK_HEIGHT: 30,
  MARGIN: 5,
  TIME_MARGIN: 1800,
  START_TIME: false,
  tick: function(time, label) {
    var daytime, diffhour, hour;
    if (label == null) {
      label = false;
    }
    daytime = "tick_bright";
    hour = moment.unix(time).format('H');
    if (hour > 19 || hour < 8) {
      daytime = "tick_dark";
    }
    diffhour = moment().isDST() ? 22 : 23;
    if (time % (24 * 60 * 60) === diffhour * 60 * 60) {
      if (label) {
        return {
          tick_day: true,
          label: moment.unix(time).format('MM-DD HH:mm'),
          daytime: daytime
        };
      } else {
        return {
          tick_day: true,
          daytime: daytime
        };
      }
    } else if (time % (60 * 60) === 0) {
      if (label) {
        return {
          tick_hour: true,
          label: moment.unix(time).format('HH:mm'),
          daytime: daytime
        };
      } else {
        return {
          tick_hour: true,
          daytime: daytime
        };
      }
    } else {
      return {
        tick: true,
        daytime: daytime
      };
    }
  },
  get_starttime: function(margin) {
    var start_time;
    if (margin == null) {
      margin = false;
    }
    if (!Shifts.render.START_TIME) {
      Shifts.render.START_TIME = parseInt(moment(moment().format('YYYY-MM-DD HH:00')).format('X'), 10);
    }
    start_time = parseInt(Shifts.render.START_TIME, 10);
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
  calculate_signup_state: function(shift) {
    var now_unix;
    now_unix = moment().format('X');
    if (shift.start_time > now_unix) {
      return "success";
    }
    return "default";
  },
  shiftplan: function() {
    return Shifts.db.get_rooms(function(rooms) {
      return Shifts.db.get_angeltypes(function(angeltypes) {
        var selected_angeltypes, selected_rooms;
        selected_rooms = Shifts.interaction.selected_rooms;
        selected_angeltypes = Shifts.interaction.selected_angeltypes;
        return Shifts.db.get_shifts(selected_rooms, selected_angeltypes, function(db_shifts) {
          return Shifts.db.get_shiftentries(selected_rooms, selected_angeltypes, function(db_shiftentries) {
            return Shifts.render.shiftplan_assemble(rooms, angeltypes, db_shifts, db_shiftentries);
          });
        });
      });
    });
  },
  shiftplan_assemble: function(rooms, angeltypes, db_shifts, db_shiftentries) {
    var add_shift, angeltype, end_time, firstblock_starttime, highest_lane_nr, i, j, k, l, lane, lane_nr, lanes, lastblock_endtime, len, len1, len2, len3, len4, len5, m, mustache_rooms, n, ref, ref1, ref2, rendered_until, room, room_id, room_nr, s, se, shift, shift_added, shift_fits, shift_nr, shiftentries, start_time, thistime, time_slot, tpl;
    lanes = {};
    shiftentries = {};
    for (i = 0, len = db_shiftentries.length; i < len; i++) {
      se = db_shiftentries[i];
      if (typeof shiftentries[se.SID] === "undefined") {
        shiftentries[se.SID] = [];
        shiftentries[se.SID].push({
          TID: se.TID,
          at_name: se.at_name,
          angels: []
        });
      }
    }
    for (j = 0, len1 = db_shiftentries.length; j < len1; j++) {
      se = db_shiftentries[j];
      for (s in shiftentries[se.SID]) {
        if (se.TID === shiftentries[se.SID][s].TID) {
          shiftentries[se.SID][s].angels.push({
            UID: se.UID,
            Nick: se.Nick
          });
        }
      }
    }
    add_shift = function(shift, room_id) {
      var blocks, height, lane_nr;
      if (shift.shift_title === "null") {
        shift.shift_title = null;
      }
      shift.starttime = moment.unix(shift.start_time).format('HH:mm');
      shift.endtime = moment.unix(shift.end_time).format('HH:mm');
      shift.state_class = Shifts.render.calculate_signup_state(shift);
      shift.angeltypes = shiftentries[shift.SID];
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
      var k, lane_shift, len2, ref;
      ref = lanes[room_id][lane_nr];
      for (k = 0, len2 = ref.length; k < len2; k++) {
        lane_shift = ref[k];
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
    for (k = 0, len2 = db_shifts.length; k < len2; k++) {
      shift = db_shifts[k];
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
      for (l = 0, len3 = ref.length; l < len3; l++) {
        lane = ref[l];
        shift_added = add_shift(shift, room_id);
        if (shift_added) {
          break;
        }
      }
      if (!shift_added) {
        lanes[room_id].push([]);
        highest_lane_nr = lanes[room_id].length - 1;
        add_shift(shift, room_id);
      }
    }
    time_slot = [];
    if (db_shifts.length > 0) {
      thistime = parseInt(firstblock_starttime, 10) - Shifts.render.TIME_MARGIN;
      while (thistime < parseInt(lastblock_endtime, 10) + Shifts.render.TIME_MARGIN) {
        time_slot.push(Shifts.render.tick(thistime, true));
        thistime += Shifts.render.SECONDS_PER_ROW;
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
        while (rendered_until < parseInt(lastblock_endtime, 10) + Shifts.render.TIME_MARGIN) {
          mustache_rooms[room_nr].lanes[lane_nr].shifts.push(Shifts.render.tick(rendered_until, true));
          rendered_until += Shifts.render.SECONDS_PER_ROW;
        }
      }
    }
    for (m = 0, len4 = rooms.length; m < len4; m++) {
      room = rooms[m];
      if (ref1 = room.RID, indexOf.call(Shifts.interaction.selected_rooms, ref1) >= 0) {
        room.selected = true;
      }
    }
    for (n = 0, len5 = angeltypes.length; n < len5; n++) {
      angeltype = angeltypes[n];
      if (ref2 = angeltype.id, indexOf.call(Shifts.interaction.selected_angeltypes, ref2) >= 0) {
        angeltype.selected = true;
      }
    }
    tpl = '';
    tpl += Mustache.render(Shifts.templates.filter_form, {
      rooms: rooms,
      angeltypes: angeltypes
    });
    tpl += Mustache.render(Shifts.templates.shift_calendar, {
      timelane_ticks: time_slot,
      rooms: mustache_rooms
    });
    Shifts.$shiftplan.html(tpl);
    return $('#datetimepicker').datetimepicker({
      value: moment.unix(Shifts.render.START_TIME).format('YYYY-MM-DD HH:mm'),
      timepicker: true,
      inline: true,
      format: 'Y-m-d H:i',
      minDate: '-1970-01-02',
      maxDate: '+1970-01-03',
      onChangeDateTime: function(dp, $input) {
        var stime;
        stime = parseInt(moment($input.val()).format('X'), 10);
        Shifts.render.START_TIME = stime;
        return Shifts.db.set_option('filter_start_time', stime, function() {
          return Shifts.render.shiftplan();
        });
      }
    });
  }
};

Shifts.templates = {
  filter_form: '<form class="form-inline" action="" method="get"> <input type="hidden" name="p" value="user_shifts"> <div class="row"> <div class="col-md-6"> <h1>Shifts</h1> <div class="form-group" style="width: 768px; height: 250px;"> <input id="datetimepicker" type="text" /> </div> </div> <div class="col-md-2"> <div id="selection_rooms" class="selection rooms"> <h4>Rooms</h4> {{#rooms}} <div class="checkbox"> <label> <input type="checkbox" name="rooms[]" value="{{RID}}" {{#selected}}checked="checked"{{/selected}}> {{Name}} </label> </div><br /> {{/rooms}} <div class="form-group"> <div class="btn-group mass-select"> <a href="#all" class="btn btn-default">All</a> <a href="#none" class="btn btn-default">None</a> </div> </div> </div> </div> <div class="col-md-2"> <div id="selection_types" class="selection types"> <h4>Angeltypes<sup>1</sup></h4> {{#angeltypes}} <div class="checkbox"> <label> <input type="checkbox" name="types[]" value="{{id}}" {{#selected}}checked="checked"{{/selected}}> {{name}} </label> </div><br /> {{/angeltypes}} <div class="form-group"> <div class="btn-group mass-select"> <a href="#all" class="btn btn-default">All</a> <a href="#none" class="btn btn-default">None</a> </div> </div> </div> </div> <div class="col-md-2"> <div id="selection_filled" class="selection filled"> <h4>Occupancy</h4> <div class="checkbox"> <label> <input type="checkbox" name="filled[]" value="0" checked="checked"> occupied </label> </div><br /> <div class="checkbox"> <label> <input type="checkbox" name="filled[]" value="1" checked="checked"> free </label> </div><br /> <div class="form-group"> <div class="btn-group mass-select"> <a href="#all" class="btn btn-default">All</a> <a href="#none" class="btn btn-default">None</a> </div> </div> </div> </div> </div> <div class="row"> <div class="col-md-6"> <div><sup>1</sup>The tasks shown here are influenced by the angeltypes you joined already! <a href="?p=angeltypes&amp;action=about">Description of the jobs.</a></div> <input id="filterbutton" class="btn btn-primary" type="submit" style="width: 75%; margin-bottom: 20px" value="Filter"> </div> </div> </form>',
  shift_calendar: '<div class="shift-calendar"> <div class="lane time"> <div class="header">Time</div> {{#timelane_ticks}} {{#tick}} <div class="tick {{daytime}}"></div> {{/tick}} {{#tick_hour}} <div class="tick {{daytime}} hour">{{label}}</div> {{/tick_hour}} {{#tick_day}} <div class="tick {{daytime}} day">{{label}}</div> {{/tick_day}} {{/timelane_ticks}} </div> {{#rooms}} {{#lanes}} <div class="lane"> <div class="header"> <a href="?p=rooms&action=view&room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{Name}}</a> </div> {{#shifts}} {{#tick}} <div class="tick {{daytime}}"></div> {{/tick}} {{#tick_hour}} <div class="tick {{daytime}} hour">{{text}}</div> {{/tick_hour}} {{#tick_day}} <div class="tick {{daytime}} day">{{text}}</div> {{/tick_day}} {{#shift}} <div class="shift panel panel-{{state_class}}" style="height: {{height}}px;"> <div class="panel-heading"> <a href="?p=shifts&amp;action=view&amp;shift_id={{SID}}">{{starttime}} ‐ {{endtime}} — {{shifttype_name}}</a> <div class="pull-right"> <div class="btn-group"> <a href="?p=user_shifts&amp;edit_shift={{SID}}" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span></a> <a href="?p=user_shifts&amp;delete_shift={{SID}}" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-trash"></span></a> </div> </div> </div> <div class="panel-body"> {{#shift_title}}<span class="glyphicon glyphicon-info-sign"></span> {{shift_title}}<br />{{/shift_title}} <a href="?p=rooms&amp;action=view&amp;room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{room_name}}</a> </div> <ul class="list-group"> {{#angeltypes}} <li class="list-group-item"><strong><a href="?p=angeltypes&amp;action=view&amp;angeltype_id={{TID}}">{{at_name}}</a>:</strong> {{#angels}} <span><a href="?p=users&amp;action=view&amp;user_id={{UID}}"><span class="icon-icon_angel"></span> {{Nick}}</a></span> {{/angels}} {{/angeltypes}} </li> <li class="list-group-item"> <a href="?p=user_shifts&amp;shift_id=2696&amp;type_id=104575" class="btn btn-default btn-xs">Neue Engel hinzufügen</a> </li> </ul> <div class="shift-spacer"></div> </div> {{/shift}} {{/shifts}} </div> {{/lanes}} {{/rooms}} </div>'
};

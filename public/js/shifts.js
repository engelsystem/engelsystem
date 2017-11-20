var Shifts,
  indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

Shifts = window.Shifts || {};

Shifts.init = function() {
  var dbtest;
  Shifts.$shiftplan = $('#shiftplan');
  if (Shifts.$shiftplan.length > 0) {
    Shifts.log('shifts init');
    if (indexOf.call(document.cookie, 'websql=') < 0) {
      try {
        dbtest = window.openDatabase('_engelsystem_test', '1.0', '', 10 * 1024 * 1024);
        document.cookie = 'websql=yes';
      } catch (error) {
        document.cookie = 'websql=nope';
        window.location.href = '';
      }
    }
    return Shifts.db.init(function() {
      Shifts.log('db initialized');
      return Shifts.fetcher.start(true, function() {
        Shifts.log('fetch complete.');
        Shifts.render.header_footer();
        Shifts.render.shiftplan();
        Shifts.interaction.init();
        setInterval(function() {
          return Shifts.fetcher.start(false, function() {});
        }, 1000 * 60 * 5);
        return Shifts.db.get_shift_range(function(date_range) {
          var waitforcal;
          return waitforcal = setInterval(function() {
            if (Shifts.render.START_TIME) {
              $('#datetimepicker').datetimepicker({
                value: moment.unix(Shifts.render.START_TIME).format('YYYY-MM-DD HH:mm'),
                timepicker: true,
                inline: true,
                format: 'Y-m-d H:i',
                minDate: moment.unix(date_range[0]).format('YYYY-MM-DD'),
                maxDate: moment.unix(date_range[1]).format('YYYY-MM-DD'),
                onChangeDateTime: function(dp, $input) {
                  var stime;
                  stime = parseInt(moment($input.val()).format('X'), 10);
                  Shifts.render.START_TIME = stime;
                  $('#filterbutton').removeAttr('disabled');
                  return Shifts.db.set_option('filter_start_time', stime, function() {
                    if (Shifts.render.rendering_time < Shifts.render.render_threshold) {
                      return Shifts.render.shiftplan();
                    }
                  });
                }
              });
              return clearInterval(waitforcal);
            }
          }, 1);
        });
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
  prefix: '',
  websql: {},
  init: function(done) {
    try {
      Shifts.db.prefix = '_' + Shifts.db.slugify($('.footer').html().split('<br>')[0]);
    } catch (error) {
      Shifts.db.prefix = '';
    }
    Shifts.log('init db');
    Shifts.db.websql = openDatabase('engelsystem' + Shifts.db.prefix, '1.0', '', 10 * 1024 * 1024);
    return Shifts.db.websql.transaction(function(t) {
      t.executeSql('CREATE TABLE IF NOT EXISTS Shifts (SID unique, title, shifttype_id INT, start_time INT, end_time INT, RID INT)');
      t.executeSql('CREATE TABLE IF NOT EXISTS User (UID unique, nick)');
      t.executeSql('CREATE TABLE IF NOT EXISTS Room (RID unique, Name)');
      t.executeSql('CREATE TABLE IF NOT EXISTS ShiftEntry (id unique, SID INT, TID INT, UID INT)');
      t.executeSql('CREATE TABLE IF NOT EXISTS ShiftTypes (id unique, name, angeltype_id INT)');
      t.executeSql('CREATE TABLE IF NOT EXISTS AngelTypes (id unique, name)');
      t.executeSql('CREATE TABLE IF NOT EXISTS NeededAngelTypes (id unique, room_id INT, shift_id INT, angel_type_id INT, angel_count INT)');
      t.executeSql('CREATE TABLE IF NOT EXISTS options (option_key, option_value)');
      return Shifts.db.populate_ids(function() {
        return done();
      });
    });
  },
  slugify: function(text) {
    return text.toString().toLowerCase().replace(/^[\s|\-|_]+/, '').replace(/[\s|\-|_]+$/, '').replace(/\s+/g, '_').replace(/__+/g, '_').replace(/[^\w\-]+/g, '');
  },
  object_to_array: function(obj) {
    var a, i, len, o;
    a = [];
    for (i = 0, len = obj.length; i < len; i++) {
      o = obj[i];
      a.push(o);
    }
    return a;
  },
  populate_ids: function(done) {
    return Shifts.db.websql.transaction(function(t) {
      t.executeSql('SELECT RID from Room', [], function(t, res) {
        var i, len, r, ref, results;
        ref = res.rows;
        results = [];
        for (i = 0, len = ref.length; i < len; i++) {
          r = ref[i];
          results.push(Shifts.interaction.selected_rooms.push(r.RID));
        }
        return results;
      });
      t.executeSql('SELECT id from AngelTypes', [], function(t, res) {
        var a, i, len, results;
        results = [];
        for (i = 0, len = res.length; i < len; i++) {
          a = res[i];
          results.push(Shifts.interaction.selected_angeltypes.push(a.id));
        }
        return results;
      });
      return done();
    });
  },
  insert_room: function(room, done) {
    room.RID = parseInt(room.RID, 10);
    return Shifts.db.websql.transaction(function(t) {
      t.executeSql('INSERT INTO Room (RID, Name) VALUES (?, ?)', [room.RID, room.Name]);
      Shifts.interaction.selected_rooms.push(room.RID);
      return done();
    });
  },
  insert_user: function(user, done) {
    user.UID = parseInt(user.UID, 10);
    return Shifts.db.websql.transaction(function(t) {
      t.executeSql('INSERT INTO User (UID, Nick) VALUES (?, ?)', [user.UID, user.Nick]);
      return done();
    });
  },
  insert_shift: function(shift, done) {
    shift.SID = parseInt(shift.SID, 10);
    shift.RID = parseInt(shift.RID, 10);
    return Shifts.db.websql.transaction(function(t) {
      t.executeSql('INSERT INTO Shifts (SID, title, shifttype_id, start_time, end_time, RID) VALUES (?, ?, ?, ?, ?, ?)', [shift.SID, shift.title, shift.shifttype_id, shift.start, shift.end, shift.RID]);
      return done();
    });
  },
  insert_shiftentry: function(shiftentry, done) {
    shiftentry.id = parseInt(shiftentry.id, 10);
    shiftentry.SID = parseInt(shiftentry.SID, 10);
    shiftentry.TID = parseInt(shiftentry.TID, 10);
    shiftentry.UID = parseInt(shiftentry.UID, 10);
    return Shifts.db.websql.transaction(function(t) {
      t.executeSql('INSERT INTO ShiftEntry (id, SID, TID, UID) VALUES (?, ?, ?, ?)', [shiftentry.id, shiftentry.SID, shiftentry.TID, shiftentry.UID], function() {});
      return done();
    });
  },
  insert_shifttype: function(shifttype, done) {
    shifttype.id = parseInt(shifttype.id, 10);
    return Shifts.db.websql.transaction(function(t) {
      t.executeSql('INSERT INTO ShiftTypes (id, name) VALUES (?, ?)', [shifttype.id, shifttype.name]);
      return done();
    });
  },
  insert_angeltype: function(angeltype, done) {
    return Shifts.db.websql.transaction(function(t) {
      t.executeSql('INSERT INTO AngelTypes (id, name) VALUES (?, ?)', [angeltype.id, angeltype.name], function() {
        return Shifts.interaction.selected_angeltypes.push(angeltype.id);
      });
      return done();
    });
  },
  insert_needed_angeltype: function(needed_angeltype, done) {
    needed_angeltype.id = parseInt(needed_angeltype.id, 10);
    needed_angeltype.RID = parseInt(needed_angeltype.RID, 10) || null;
    needed_angeltype.SID = parseInt(needed_angeltype.SID, 10) || null;
    needed_angeltype.ATID = parseInt(needed_angeltype.ATID, 10);
    needed_angeltype.count = parseInt(needed_angeltype.count, 10);
    return Shifts.db.websql.transaction(function(t) {
      t.executeSql('INSERT INTO NeededAngelTypes (id, room_id, shift_id, angel_type_id, angel_count) VALUES (?, ?, ?, ?, ?)', [needed_angeltype.id, needed_angeltype.RID, needed_angeltype.SID, needed_angeltype.ATID, needed_angeltype.count], function() {});
      return done();
    });
  },
  get_shifts: function(filter_rooms, filter_angeltypes, done) {
    var end_time, filter_angeltypes_ids, filter_rooms_ids, start_time;
    filter_rooms_ids = filter_rooms.join(',');
    filter_angeltypes_ids = filter_angeltypes.join(',');
    start_time = Shifts.render.get_starttime();
    end_time = Shifts.render.get_endtime();
    return Shifts.db.websql.transaction(function(t) {
      return t.executeSql('SELECT DISTINCT Shifts.SID, Shifts.title as shift_title, Shifts.shifttype_id, Shifts.start_time, Shifts.end_time, Shifts.RID, ShiftTypes.name as shifttype_name, Room.Name as room_name FROM NeededAngelTypes JOIN Shifts ON Shifts.SID = NeededAngelTypes.shift_id JOIN Room ON Room.RID = Shifts.RID JOIN ShiftTypes ON ShiftTypes.id = Shifts.shifttype_id WHERE NeededAngelTypes.angel_count > 0 AND Shifts.start_time >= ? AND Shifts.end_time <= ? AND Shifts.RID IN (?) AND NeededAngelTypes.angel_type_id IN (?) ORDER BY Shifts.start_time, Shifts.SID', [start_time, end_time, filter_rooms_ids, filter_angeltypes_ids], function(t, res) {
        var r;
        r = Shifts.db.object_to_array(res.rows);
        return done(r);
      });
    });
  },
  get_angeltypes_needed: function(done) {
    var end_time, start_time;
    start_time = Shifts.render.get_starttime();
    end_time = Shifts.render.get_endtime();
    return Shifts.db.websql.transaction(function(t) {
      return t.executeSql('SELECT DISTINCT NeededAngelTypes.shift_id, NeededAngelTypes.angel_type_id, NeededAngelTypes.angel_count, AngelTypes.name FROM NeededAngelTypes JOIN Shifts ON NeededAngelTypes.shift_id = Shifts.SID JOIN AngelTypes ON NeededAngelTypes.angel_type_id = AngelTypes.id WHERE Shifts.start_time >= ? AND Shifts.end_time <= ? AND NeededAngelTypes.angel_count > 0 ORDER BY NeededAngelTypes.shift_id', [start_time, end_time], function(t, res) {
        var r;
        r = Shifts.db.object_to_array(res.rows);
        return done(r);
      });
    });
  },
  get_shiftentries: function(done) {
    var end_time, start_time;
    start_time = Shifts.render.get_starttime();
    end_time = Shifts.render.get_endtime();
    return Shifts.db.websql.transaction(function(t) {
      return t.executeSql('SELECT DISTINCT ShiftEntry.SID, ShiftEntry.TID, ShiftEntry.UID, User.Nick, AngelTypes.name as at_name FROM ShiftEntry JOIN User ON ShiftEntry.UID = User.UID JOIN Shifts ON ShiftEntry.SID = Shifts.SID JOIN AngelTypes ON ShiftEntry.TID = AngelTypes.id WHERE Shifts.start_time >= ? AND Shifts.end_time <= ? ORDER BY ShiftEntry.SID', [start_time, end_time], function(t, res) {
        var r;
        r = Shifts.db.object_to_array(res.rows);
        return done(r);
      });
    });
  },
  get_usershifts: function(user_id, done) {
    return Shifts.db.websql.transaction(function(t) {
      return t.executeSql('SELECT DISTINCT ShiftEntry.SID, ShiftEntry.TID, Shifts.start_time, Shifts.end_time FROM ShiftEntry JOIN Shifts ON ShiftEntry.SID = Shifts.SID WHERE ShiftEntry.UID = ? ORDER BY ShiftEntry.SID', [user_id], function(t, res) {
        var r;
        r = Shifts.db.object_to_array(res.rows);
        return done(r);
      });
    });
  },
  get_shift_range: function(done) {
    return Shifts.db.websql.transaction(function(t) {
      return t.executeSql('SELECT start_time FROM Shifts ORDER BY start_time ASC LIMIT 1', [], function(t, res) {
        var now, start_time;
        if (res.rows.length > 0) {
          start_time = res.rows[0].start_time;
          return t.executeSql('SELECT end_time FROM Shifts ORDER BY end_time DESC LIMIT 1', [], function(t, res) {
            var end_time;
            end_time = res.rows[0].end_time;
            return done([start_time, end_time]);
          });
        } else {
          now = new Date();
          return done([now, now]);
        }
      });
    });
  },
  get_rooms: function(done) {
    return Shifts.db.websql.transaction(function(t) {
      return t.executeSql('SELECT * FROM Room ORDER BY Name', [], function(t, res) {
        var r;
        r = Shifts.db.object_to_array(res.rows);
        return done(r);
      });
    });
  },
  get_angeltypes: function(done) {
    return Shifts.db.websql.transaction(function(t) {
      return t.executeSql('SELECT * FROM AngelTypes ORDER BY name', [], function(t, res) {
        var r;
        r = Shifts.db.object_to_array(res.rows);
        return done(r);
      });
    });
  },
  get_option: function(key, done) {
    return Shifts.db.websql.transaction(function(t) {
      return t.executeSql('SELECT option_value FROM options WHERE option_key = ? LIMIT 1', [key], function(t, res) {
        try {
          return done(res.rows[0].option_value);
        } catch (error) {
          return done(false);
        }
      });
    });
  },
  set_option: function(key, value, done) {
    return Shifts.db.websql.transaction(function(t) {
      return t.executeSql('DELETE FROM options WHERE option_key = ?', [key], function() {
        return Shifts.db.websql.transaction(function(t2) {
          return t2.executeSql('INSERT INTO options (option_key, option_value) VALUES (?, ?)', [key, value], function() {
            return done();
          });
        });
      });
    });
  }
};

Shifts.fetcher = {
  total_objects_count: 0,
  total_objects_count_since_start: 0,
  remaining_objects_count: 0,
  start: function(display_status, done) {
    if (display_status) {
      Shifts.$shiftplan.html(Shifts.templates.fetcher_status);
    }
    return Shifts.fetcher.fetch_in_parts(function() {
      return done();
    });
  },
  fetch_in_parts: function(done) {
    var fn, idname, latest_ids, table, table_mapping;
    table_mapping = {
      Room: 'RID',
      AngelTypes: 'id',
      ShiftTypes: 'id',
      User: 'UID',
      Shifts: 'SID',
      NeededAngelTypes: 'id',
      ShiftEntry: 'id'
    };
    latest_ids = [];
    fn = function(table, idname) {
      return Shifts.db.websql.transaction(function(tx) {
        return tx.executeSql("SELECT " + idname + " FROM " + table + " ORDER BY " + idname + " DESC LIMIT 1", [], function(tx, res) {
          var r;
          if (res.rows.length > 0) {
            r = res.rows[0][idname];
          } else {
            r = 0;
          }
          return latest_ids.push(table + '=' + r);
        });
      });
    };
    for (table in table_mapping) {
      idname = table_mapping[table];
      fn(table, idname);
    }
    return setTimeout(function() {
      var url;
      Shifts.$shiftplan.find('#fetcher_statustext').text('Fetching data from server...');
      Shifts.$shiftplan.find('#remaining_objects').text('');
      url = '?p=shifts_json_export_websql&' + latest_ids.join('&');
      return $.get(url, function(data) {
        Shifts.fetcher.total_objects_count = 0;
        Shifts.fetcher.total_objects_count += parseInt(data.rooms_total, 10);
        Shifts.fetcher.total_objects_count += parseInt(data.angeltypes_total, 10);
        Shifts.fetcher.total_objects_count += parseInt(data.shift_types_total, 10);
        Shifts.fetcher.total_objects_count += parseInt(data.users_total, 10);
        Shifts.fetcher.total_objects_count += parseInt(data.shifts_total, 10);
        Shifts.fetcher.total_objects_count += parseInt(data.needed_angeltypes_total, 10);
        Shifts.fetcher.total_objects_count += parseInt(data.shift_entries_total, 10);
        Shifts.fetcher.remaining_objects_count = Shifts.fetcher.total_objects_count;
        if (Shifts.fetcher.total_objects_count_since_start === 0) {
          Shifts.fetcher.total_objects_count_since_start = Shifts.fetcher.total_objects_count;
        }
        Shifts.$shiftplan.find('#fetcher_statustext').text('Importing new objects into browser database.');
        Shifts.$shiftplan.find('#remaining_objects').text(Shifts.fetcher.remaining_objects_count + ' remaining...');
        Shifts.$shiftplan.find('#abort').on('click', function() {
          document.cookie = 'websql=nope';
          return window.location.href = '';
        });
        return Shifts.db.get_option('filter_start_time', function(res) {
          if (res) {
            Shifts.render.START_TIME = parseInt(res, 10);
          }
          return Shifts.db.get_option('filter_selected_rooms', function(res) {
            var i, len, r, ref;
            if (res) {
              Shifts.interaction.selected_rooms = [];
              ref = res.split(',');
              for (i = 0, len = ref.length; i < len; i++) {
                r = ref[i];
                Shifts.interaction.selected_rooms.push(parseInt(r, 10));
              }
            }
            return Shifts.db.get_option('filter_selected_angeltypes', function(res) {
              var a, j, len1, ref1;
              if (res) {
                Shifts.interaction.selected_angeltypes = [];
                ref1 = res.split(',');
                for (j = 0, len1 = ref1.length; j < len1; j++) {
                  a = ref1[j];
                  Shifts.interaction.selected_angeltypes.push(parseInt(a, 10));
                }
              }
              return Shifts.db.get_option('filter_occupancy', function(res) {
                if (res) {
                  Shifts.interaction.occupancy = res;
                }
                return Shifts.db.get_option('rendering_time', function(res) {
                  var rooms;
                  if (res) {
                    Shifts.render.rendering_time = parseInt(res, 10);
                  } else {
                    Shifts.render.rendering_time = 2000;
                  }
                  rooms = data.rooms;
                  return Shifts.fetcher.process(Shifts.db.insert_room, rooms, function() {
                    var angeltypes;
                    angeltypes = data.angeltypes;
                    return Shifts.fetcher.process(Shifts.db.insert_angeltype, angeltypes, function() {
                      var shift_types;
                      shift_types = data.shift_types;
                      return Shifts.fetcher.process(Shifts.db.insert_shifttype, shift_types, function() {
                        var users;
                        users = data.users;
                        return Shifts.fetcher.process(Shifts.db.insert_user, users, function() {
                          var shifts;
                          shifts = data.shifts;
                          return Shifts.fetcher.process(Shifts.db.insert_shift, shifts, function() {
                            var needed_angeltypes;
                            needed_angeltypes = data.needed_angeltypes;
                            return Shifts.fetcher.process(Shifts.db.insert_needed_angeltype, needed_angeltypes, function() {
                              var shift_entries;
                              shift_entries = data.shift_entries;
                              return Shifts.fetcher.process(Shifts.db.insert_shiftentry, shift_entries, function() {
                                if (Shifts.fetcher.total_objects_count <= 0) {
                                  return done();
                                } else {
                                  return Shifts.fetcher.fetch_in_parts(done);
                                }
                              });
                            });
                          });
                        });
                      });
                    });
                  });
                });
              });
            });
          });
        });
      });
    }, 500);
  },
  process: function(processing_func, items_to_process, done) {
    var $pb, $ro, item, percentage;
    $ro = Shifts.$shiftplan.find('#remaining_objects');
    $pb = Shifts.$shiftplan.find('#progress_bar');
    if (items_to_process.length > 0) {
      item = items_to_process.shift();
      Shifts.fetcher.remaining_objects_count--;
      if (Shifts.fetcher.remaining_objects_count % 100 === 0) {
        percentage = 100 - Shifts.fetcher.remaining_objects_count / Shifts.fetcher.total_objects_count_since_start * 100;
        $ro.text(Shifts.fetcher.remaining_objects_count + ' remaining...');
        $pb.text(Math.round(percentage) + '%');
        $pb.width(percentage + '%');
      }
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
  occupancy: 'free',
  datepicker_interval: false,
  init: function() {
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
      Shifts.db.set_option('filter_selected_rooms', Shifts.interaction.selected_rooms.join(','), function() {});
      $('#filterbutton').removeAttr('disabled');
      if (Shifts.render.rendering_time < Shifts.render.render_threshold) {
        return Shifts.render.shiftplan();
      }
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
      Shifts.db.set_option('filter_selected_angeltypes', Shifts.interaction.selected_angeltypes.join(','), function() {});
      $('#filterbutton').removeAttr('disabled');
      if (Shifts.render.rendering_time < Shifts.render.render_threshold) {
        return Shifts.render.shiftplan();
      }
    });
  },
  on_mass_select: function() {
    return Shifts.$shiftplan.on('click', '.mass-select a', function(ev) {
      var $all, $free, i, j, k, l, len, len1, len2, len3, ref, ref1, ref2, ref3, room, type;
      if ($(this).parents('#selection_rooms').length) {
        if ($(ev.target).attr('href') === '#all') {
          ref = $('#selection_rooms input');
          for (i = 0, len = ref.length; i < len; i++) {
            room = ref[i];
            $(room).prop('checked', true);
            Shifts.interaction.selected_rooms.push(parseInt(room.value, 10));
          }
          Shifts.db.set_option('filter_selected_rooms', Shifts.interaction.selected_rooms.join(','), function() {});
        }
        if ($(ev.target).attr('href') === '#none') {
          ref1 = $('#selection_rooms input');
          for (j = 0, len1 = ref1.length; j < len1; j++) {
            room = ref1[j];
            $(room).prop('checked', false);
          }
          Shifts.interaction.selected_rooms = [];
          Shifts.db.set_option('filter_selected_rooms', 'none', function() {});
        }
      }
      if ($(this).parents('#selection_types').length) {
        if ($(ev.target).attr('href') === '#all') {
          ref2 = $('#selection_types input');
          for (k = 0, len2 = ref2.length; k < len2; k++) {
            type = ref2[k];
            $(type).prop('checked', true);
            Shifts.interaction.selected_angeltypes.push(parseInt(type.value, 10));
          }
          Shifts.db.set_option('filter_selected_angeltypes', Shifts.interaction.selected_angeltypes.join(','), function() {});
        }
        if ($(ev.target).attr('href') === '#none') {
          ref3 = $('#selection_types input');
          for (l = 0, len3 = ref3.length; l < len3; l++) {
            type = ref3[l];
            $(type).prop('checked', false);
          }
          Shifts.interaction.selected_angeltypes = [];
          Shifts.db.set_option('filter_selected_angeltypes', 'none', function() {});
        }
      }
      if ($(this).parents('#selection_filled').length) {
        $all = $('#selection_filled a[href=#all]');
        $free = $('#selection_filled a[href=#free]');
        if ($(ev.target).attr('href') === '#all') {
          Shifts.interaction.occupancy = 'all';
          $all.removeClass('btn-default');
          $all.addClass('btn-primary');
          $free.removeClass('btn-primary');
          $free.addClass('btn-default');
        }
        if ($(ev.target).attr('href') === '#free') {
          Shifts.interaction.occupancy = 'free';
          $free.removeClass('btn-default');
          $free.addClass('btn-primary');
          $all.removeClass('btn-primary');
          $all.addClass('btn-default');
        }
        Shifts.db.set_option('filter_occupancy', Shifts.interaction.occupancy, function() {});
      }
      $('#filterbutton').removeAttr('disabled');
      if (Shifts.render.rendering_time < Shifts.render.render_threshold) {
        Shifts.render.shiftplan();
      }
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
  metric_timestamp: false,
  rendering_time: 0,
  render_threshold: 700,
  tick: function(time, label) {
    var current_quarter, daytime, diffhour, hour, tick_quarter;
    if (label == null) {
      label = false;
    }
    daytime = 'tick_bright';
    hour = moment.unix(time).format('H');
    if (hour > 19 || hour < 8) {
      daytime = 'tick_dark';
    }
    if (hour === moment().format('H')) {
      tick_quarter = Math.floor(moment.unix(time).format('m') / 60 * 4);
      current_quarter = Math.floor(moment().format('m') / 60 * 4);
      if (tick_quarter === current_quarter) {
        daytime = 'tick_active';
      }
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
  header_footer: function() {
    var tpl;
    tpl = '';
    tpl += Mustache.render(Shifts.templates.header_and_dateselect);
    tpl += Mustache.render(Shifts.templates.footer);
    return Shifts.$shiftplan.html(tpl);
  },
  shiftplan: function() {
    var $sc, curr_progress, loadprg, refresh_time, sco, step_size, tpl, user_id;
    user_id = parseInt($('#shiftplan').data('user_id'), 10);
    Shifts.render.metric_timestamp = new Date();
    $('#filterbutton').attr('disabled', 'disabled');
    if (Shifts.render.rendering_time > Shifts.render.render_threshold) {
      $sc = Shifts.$shiftplan.find('.shift-calendar');
      sco = $sc.offset();
      tpl = Mustache.render(Shifts.templates.loading, {
        cal_t: sco.top - 50,
        cal_l: sco.left,
        cal_w: $sc.width(),
        cal_h: $sc.height(),
        msg_t: sco.top - 50 + $sc.height() / 50,
        msg_l: sco.left + $sc.width() / 2 - 200
      });
      $sc.before(tpl);
      refresh_time = 200;
      step_size = refresh_time;
      curr_progress = 0;
      loadprg = setInterval(function() {
        var percentage;
        percentage = Math.round(curr_progress / Shifts.render.rendering_time * 120);
        Shifts.$shiftplan.find('#cal_loading_progress').width(percentage + '%');
        curr_progress += step_size;
        if (curr_progress > Shifts.render.rendering_time) {
          return clearInterval(loadprg);
        }
      }, refresh_time);
    }
    return Shifts.db.get_rooms(function(rooms) {
      return Shifts.db.get_angeltypes(function(angeltypes) {
        var angeltype, filter_form, i, j, len, len1, occupancy, ref, ref1, room, selected_angeltypes, selected_rooms;
        for (i = 0, len = rooms.length; i < len; i++) {
          room = rooms[i];
          if (ref = room.RID, indexOf.call(Shifts.interaction.selected_rooms, ref) >= 0) {
            room.selected = true;
          }
        }
        for (j = 0, len1 = angeltypes.length; j < len1; j++) {
          angeltype = angeltypes[j];
          if (ref1 = angeltype.id, indexOf.call(Shifts.interaction.selected_angeltypes, ref1) >= 0) {
            angeltype.selected = true;
          }
        }
        switch (Shifts.interaction.occupancy) {
          case 'all':
            occupancy = {
              all: 'primary',
              free: 'default'
            };
            break;
          case 'free':
            occupancy = {
              all: 'default',
              free: 'primary'
            };
        }
        filter_form = Mustache.render(Shifts.templates.filter_form, {
          rooms: rooms,
          angeltypes: angeltypes,
          occupancy: occupancy
        });
        Shifts.$shiftplan.find('.filter-form').html(filter_form);
        selected_rooms = Shifts.interaction.selected_rooms;
        selected_angeltypes = Shifts.interaction.selected_angeltypes;
        return Shifts.db.get_shifts(selected_rooms, selected_angeltypes, function(db_shifts) {
          return Shifts.db.get_shiftentries(function(db_shiftentries) {
            return Shifts.db.get_angeltypes_needed(function(db_angeltypes_needed) {
              return Shifts.db.get_usershifts(user_id, function(db_usershifts) {
                return Shifts.render.shiftplan_assemble(rooms, angeltypes, db_shifts, db_angeltypes_needed, db_shiftentries, db_usershifts);
              });
            });
          });
        });
      });
    });
  },
  shiftplan_assemble: function(rooms, angeltypes, db_shifts, db_angeltypes_needed, db_shiftentries, db_usershifts) {
    var add_shift, atn, calculate_signup_state, calculate_state_class, end_time, end_timestamp, entry_exists, firstblock_starttime, highest_lane_nr, i, j, k, l, lane, lane_nr, lanes, lastblock_endtime, len, len1, len2, len3, len4, len5, m, mustache_rooms, n, needed_angeltypes, ref, rendered_until, room_id, room_nr, s, se, shift, shift_added, shift_calendar, shift_fits, shift_nr, shiftentries, shifts_count, start_time, thistime, time_slot;
    lanes = {};
    shiftentries = {};
    needed_angeltypes = {};
    for (i = 0, len = db_angeltypes_needed.length; i < len; i++) {
      atn = db_angeltypes_needed[i];
      needed_angeltypes[atn.shift_id + '-' + atn.angel_type_id] = atn.angel_count;
    }
    for (j = 0, len1 = db_shiftentries.length; j < len1; j++) {
      se = db_shiftentries[j];
      if (typeof shiftentries[se.SID] === 'undefined') {
        shiftentries[se.SID] = [];
        shiftentries[se.SID].push({
          TID: se.TID,
          at_name: se.at_name,
          angels: [],
          angels_needed: needed_angeltypes[se.SID + '-' + se.TID]
        });
      }
    }
    for (k = 0, len2 = db_angeltypes_needed.length; k < len2; k++) {
      atn = db_angeltypes_needed[k];
      if (typeof shiftentries[atn.shift_id] === 'undefined') {
        shiftentries[atn.shift_id] = [];
        shiftentries[atn.shift_id].push({
          TID: atn.angel_type_id,
          at_name: atn.name,
          angels: [],
          angels_needed: atn.angel_count
        });
      } else {
        entry_exists = false;
        for (s in shiftentries[atn.shift_id]) {
          if (atn.angel_type_id === shiftentries[atn.shift_id][s].TID) {
            entry_exists = true;
            break;
          }
        }
        if (!entry_exists) {
          shiftentries[atn.shift_id].push({
            TID: atn.angel_type_id,
            at_name: atn.name,
            angels: [],
            angels_needed: atn.angel_count
          });
        }
      }
    }
    for (l = 0, len3 = db_shiftentries.length; l < len3; l++) {
      se = db_shiftentries[l];
      for (s in shiftentries[se.SID]) {
        if (se.TID === shiftentries[se.SID][s].TID) {
          shiftentries[se.SID][s].angels.push({
            UID: se.UID,
            Nick: se.Nick
          });
          shiftentries[se.SID][s].angels_needed--;
        }
      }
    }
    add_shift = function(shift, room_id) {
      var blocks, height, lane_nr, ref;
      if (shift.shift_title === 'null') {
        shift.shift_title = null;
      }
      shift.starttime = moment.unix(shift.start_time).format('HH:mm');
      shift.endtime = moment.unix(shift.end_time).format('HH:mm');
      shift.angeltypes = shiftentries[shift.SID];
      shift.signup_state = calculate_signup_state(shift);
      shift.state_class = calculate_state_class(shift.signup_state);
      if (Shifts.interaction.occupancy === 'free') {
        if ((ref = shift.signup_state) !== 'free' && ref !== 'collides' && ref !== 'signed_up') {
          return true;
        }
      }
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
    calculate_signup_state = function(shift) {
      var angels_needed, at, len4, len5, len6, m, n, now_unix, p, ref, u;
      for (m = 0, len4 = db_usershifts.length; m < len4; m++) {
        u = db_usershifts[m];
        if (u.SID === shift.SID) {
          return 'signed_up';
        }
      }
      now_unix = moment().format('X');
      if (shift.end_time < now_unix) {
        return 'shift_ended';
      }
      angels_needed = 0;
      ref = shift.angeltypes;
      for (n = 0, len5 = ref.length; n < len5; n++) {
        at = ref[n];
        angels_needed = angels_needed + at.angels_needed;
      }
      if (angels_needed === 0) {
        return 'occupied';
      }
      for (p = 0, len6 = db_usershifts.length; p < len6; p++) {
        u = db_usershifts[p];
        if (u.SID !== shift.SID) {
          if (!(shift.start_time >= u.end_time || shift.end_time <= u.start_time)) {
            return 'collides';
          }
        }
      }
      return 'free';
    };
    calculate_state_class = function(signup_state) {
      switch (signup_state) {
        case 'shift_ended':
          return 'default';
        case 'signed_up':
          return 'primary';
        case 'free':
          return 'danger';
        case 'angeltype':
          return 'warning';
        case 'collides':
          return 'warning';
        case 'occupied':
          return 'success';
        case 'admin':
          return 'success';
      }
    };
    shift_fits = function(shift, room_id, lane_nr) {
      var lane_shift, len4, m, ref;
      ref = lanes[room_id][lane_nr];
      for (m = 0, len4 = ref.length; m < len4; m++) {
        lane_shift = ref[m];
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
    for (m = 0, len4 = db_shifts.length; m < len4; m++) {
      shift = db_shifts[m];
      if (shift.start_time < firstblock_starttime) {
        firstblock_starttime = shift.start_time;
      }
      if (shift.end_time > lastblock_endtime) {
        lastblock_endtime = shift.end_time;
      }
      room_id = shift.RID;
      if (typeof lanes[room_id] === 'undefined') {
        lanes[room_id] = [[]];
      }
      shift_added = false;
      ref = lanes[room_id];
      for (n = 0, len5 = ref.length; n < len5; n++) {
        lane = ref[n];
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
    shifts_count = 0;
    mustache_rooms = [];
    for (room_nr in rooms) {
      if (room_nr === 'length') {
        break;
      }
      Shifts.log(room_nr);
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
          shifts_count++;
        }
        while (rendered_until < parseInt(lastblock_endtime, 10) + Shifts.render.TIME_MARGIN) {
          mustache_rooms[room_nr].lanes[lane_nr].shifts.push(Shifts.render.tick(rendered_until, true));
          rendered_until += Shifts.render.SECONDS_PER_ROW;
        }
      }
    }
    if (shifts_count === 0) {
      mustache_rooms = [];
    }
    shift_calendar = Mustache.render(Shifts.templates.shift_calendar, {
      timelane_ticks: time_slot,
      rooms: mustache_rooms
    });
    Shifts.$shiftplan.find('.shift-calendar').html(shift_calendar);
    Shifts.$shiftplan.find('.loading-overlay, .loading-overlay-msg').remove();
    end_timestamp = new Date();
    Shifts.render.rendering_time = end_timestamp - Shifts.render.metric_timestamp;
    Shifts.db.set_option('rendering_time', Shifts.render.rendering_time, function() {});
    return (function() {
      var $header, $time_lanes, $top_ref, left, top;
      $time_lanes = $('.shift-calendar .time');
      $header = $('.shift-calendar .header');
      $top_ref = $('.container-fluid .row');
      top = $header.offset().top;
      left = 15;
      $time_lanes.css({
        'position': 'relative',
        'z-index': 999
      });
      $header.css({
        'position': 'relative',
        'z-index': 900
      });
      return $(window).on('scroll', function() {
        $time_lanes.css({
          'left': Math.max(0, $(window).scrollLeft() - left) + 'px'
        });
        return $header.css({
          'top': Math.max(0, $(window).scrollTop() - top + $top_ref.offset().top) + 'px'
        });
      });
    })();
  }
};

Shifts.templates = {
  loading: '<div class="loading-overlay" style=" position: absolute; top: {{cal_t}}px; left: {{cal_l}}px; width: {{cal_w}}px; height: {{cal_h}}px; background: #fff; opacity: 0.5; z-index: 1000; "></div> <div class="loading-overlay-msg" style=" position: absolute; top: {{msg_t}}px; left: {{msg_l}}px; width: 400px; height: 80px; padding: 1em; text-align: center; background: #fff; border: 1px solid #999; border-radius: 3px; z-index: 1001; "> Building view... <div class="row" style="margin: 2px 0 0; width: 100%;"> <div class="progress"> <div id="cal_loading_progress" class="progress-bar" style="width: 0%"> </div> </div>',
  fetcher_status: '<style> @media (max-width: 1080px) and (min-width: 768px) { #fetcher_status { margin-top: 4em; } } </style> <div id="fetcher_status"> <span id="fetcher_statustext">Fetching data from server...</span> <span id="remaining_objects"></span> <div class="progress"> <div id="progress_bar" class="progress-bar" style="width: 0%;"> 0% </div> </div> </div> <a id="abort" href="" class="btn btn-default btn-xs">Abort and switch to legacy view</a>',
  header_and_dateselect: '<form class="form-inline" action="" method="get"> <input type="hidden" name="p" value="user_shifts"> <div class="row"> <div class="col-md-6"> <h1>Shifts</h1> <div class="form-group" style="width: 768px; height: 250px;"> <input id="datetimepicker" type="hidden" /> </div> </div> <div class="filter-form"></div> </div> <div class="row"> <div class="col-md-6"> <div><sup>1</sup>The tasks shown here are influenced by the angeltypes you joined already! <a href="?p=angeltypes&amp;action=about">Description of the jobs.</a></div> <input id="filterbutton" class="btn btn-primary" type="submit" style="width: 75%; margin-bottom: 20px" value="Filter"> </div> </div> <div class="shift-calendar"> <div style="height: 100px;"> Loading... </div> </div>',
  filter_form: '<div class="col-md-2"> <div id="selection_rooms" class="selection rooms"> <h4>Rooms</h4> {{#rooms}} <div class="checkbox"> <label> <input type="checkbox" name="rooms[]" value="{{RID}}" {{#selected}}checked="checked"{{/selected}}> {{Name}} </label> </div><br /> {{/rooms}} <div class="form-group"> <div class="btn-group mass-select"> <a href="#all" class="btn btn-default">All</a> <a href="#none" class="btn btn-default">None</a> </div> </div> </div> </div> <div class="col-md-2"> <div id="selection_types" class="selection types"> <h4>Angeltypes<sup>1</sup></h4> {{#angeltypes}} <div class="checkbox"> <label> <input type="checkbox" name="types[]" value="{{id}}" {{#selected}}checked="checked"{{/selected}}> {{name}} </label> </div><br /> {{/angeltypes}} <div class="form-group"> <div class="btn-group mass-select"> <a href="#all" class="btn btn-default">All</a> <a href="#none" class="btn btn-default">None</a> </div> </div> </div> </div> <div class="col-md-2"> <div id="selection_filled" class="selection filled"> <h4>Occupancy</h4> <div class="form-group"> <div class="btn-group mass-select"> <a href="#all" class="btn btn-{{#occupancy}}{{all}}{{/occupancy}}">All</a> <a href="#free" class="btn btn-{{#occupancy}}{{free}}{{/occupancy}}">Free</a> </div> </div> </div> </div> </div>',
  footer: '</form>',
  shift_calendar: '<div class="lane time"> <div class="header">Time</div> {{#timelane_ticks}} {{#tick}} <div class="tick {{daytime}}"></div> {{/tick}} {{#tick_hour}} <div class="tick {{daytime}} hour">{{label}}</div> {{/tick_hour}} {{#tick_day}} <div class="tick {{daytime}} day">{{label}}</div> {{/tick_day}} {{/timelane_ticks}} </div> {{#rooms}} {{#lanes}} <div class="lane"> <div class="header"> <a href="?p=rooms&action=view&room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{Name}}</a> </div> {{#shifts}} {{#tick}} <div class="tick {{daytime}}"></div> {{/tick}} {{#tick_hour}} <div class="tick {{daytime}} hour">{{text}}</div> {{/tick_hour}} {{#tick_day}} <div class="tick {{daytime}} day">{{text}}</div> {{/tick_day}} {{#shift}} <div class="shift panel panel-{{state_class}}" style="height: {{height}}px;"> <div class="panel-heading"> <a href="?p=shifts&amp;action=view&amp;shift_id={{SID}}">{{starttime}} ‐ {{endtime}} — {{shifttype_name}}</a> <div class="pull-right"> <div class="btn-group"> <a href="?p=user_shifts&amp;edit_shift={{SID}}" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span></a> <a href="?p=user_shifts&amp;delete_shift={{SID}}" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-trash"></span></a> </div> </div> </div> <div class="panel-body"> {{#shift_title}}<span class="glyphicon glyphicon-info-sign"></span> {{shift_title}}<br />{{/shift_title}} <a href="?p=rooms&amp;action=view&amp;room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{room_name}}</a> </div> <ul class="list-group"> {{#angeltypes}} <li class="list-group-item"><strong><a href="?p=angeltypes&amp;action=view&amp;angeltype_id={{TID}}">{{at_name}}</a>:</strong> {{#angels}} <span><a href="?p=users&amp;action=view&amp;user_id={{UID}}"><span class="icon-icon_angel"></span> {{Nick}}</a></span>, {{/angels}} <a href="?p=user_shifts&amp;shift_id={{SID}}&amp;type_id={{TID}}">{{angels_needed}} helpers needed</a> <a href="?p=user_shifts&amp;shift_id={{SID}}&amp;type_id={{TID}}" class="btn btn-default btn-xs btn-primary">Sign up</a> {{/angeltypes}} </li> <li class="list-group-item"> <a href="?p=user_shifts&amp;shift_id={{SID}}" class="btn btn-default btn-xs">Neue Engel hinzufügen</a> </li> </ul> <div class="shift-spacer"></div> </div> {{/shift}} {{/shifts}} </div> {{/lanes}} {{/rooms}} {{^rooms}} <div class="alert alert-warning" style="margin-top: 2em;">No shifts could be found for the selected date.</div> {{/rooms}}'
};

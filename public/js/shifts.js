var Shifts;

Shifts = window.Shifts || {};

Shifts.db = {};

Shifts.db.init = function(done) {
  Shifts.log('init db');
  return alasql('CREATE INDEXEDDB DATABASE IF NOT EXISTS engelsystem; ATTACH INDEXEDDB DATABASE engelsystem;', function() {
    return alasql('USE engelsystem', function() {
      return alasql('CREATE TABLE IF NOT EXISTS Shifts (SID, title, shift_start, shift_end)', function() {
        return alasql('CREATE TABLE IF NOT EXISTS User (UID, nick)', function() {
          return alasql('CREATE TABLE IF NOT EXISTS options (option_key, option_value)', function() {
            return done();
          });
        });
      });
    });
  });
};

Shifts.fetcher = {};

Shifts.fetcher.start = function() {
  var i, len, results, s, statements;
  statements = ['INSERT INTO Shifts (SID, title, shift_start, shift_end, user_id) VALUES (1, "Testschicht", 1483182000, 1483189200, 1)', 'INSERT INTO Shifts (SID, title, shift_start, shift_end, user_id) VALUES (2, "Testschicht", 1483189200, 1483196400, 1)', 'INSERT INTO Shifts (SID, title, shift_start, shift_end, user_id) VALUES (3, "Access control", 1483189200, 1483196400, 2)', 'INSERT INTO User (UID, nick) VALUES (1, "longneck")', 'INSERT INTO User (UID, nick) VALUES (2, "Thomas")'];
  results = [];
  for (i = 0, len = statements.length; i < len; i++) {
    s = statements[i];
    Shifts.log(s);
    results.push(alasql(s));
  }
  return results;
};

Shifts.init = function() {
  Shifts.log('init');
  return Shifts.db.init(function() {
    Shifts.log('db initialized');
    return Shifts.fetcher.start();
  });
};

$(function() {
  return Shifts.init();
});

Shifts.log = function(msg) {
  return console.info(msg);
};


Shifts = window.Shifts || {}

Shifts.init = ->
    Shifts.$shiftplan = $('#shiftplan')
    if Shifts.$shiftplan.length > 0
        Shifts.log 'shifts init'

        if 'websql=' not in document.cookie
            try
                dbtest = window.indexedDB.open("_engelsystem_test", 3)
                dbtest.onerror = (ev) ->
                    document.cookie = 'websql=nope'
                    window.location.href = ''
                dbtest.onsuccess = (ev) ->
                    alasql 'CREATE INDEXEDDB DATABASE IF NOT EXISTS _engelsystem_test;', ->
                        alasql 'DROP INDEXEDDB DATABASE _engelsystem_test;'
                        document.cookie = 'websql=yes'
            catch
                document.cookie = 'websql=nope'
                window.location.href = ''

        Shifts.db.init ->
            Shifts.log 'db initialized'
            Shifts.fetcher.start ->
                Shifts.log 'fetch complete.'
                Shifts.render.header_footer()
                Shifts.render.shiftplan()
                Shifts.interaction.init()

                Shifts.db.get_shift_range (date_range) ->
                    waitforcal = setInterval ->
                        if Shifts.render.START_TIME
                            $('#datetimepicker').datetimepicker
                                value: moment.unix(Shifts.render.START_TIME).format('YYYY-MM-DD HH:mm')
                                timepicker: true
                                inline: true
                                format: 'Y-m-d H:i'
                                minDate: moment.unix(date_range[0]).format('YYYY-MM-DD')
                                maxDate: moment.unix(date_range[1]).format('YYYY-MM-DD')
                                onChangeDateTime: (dp, $input) ->
                                    stime = parseInt moment($input.val()).format('X'), 10
                                    Shifts.render.START_TIME = stime
                                    Shifts.db.set_option 'filter_start_time', stime, ->
                                        Shifts.render.shiftplan()
                            clearInterval waitforcal
                    , 1

Shifts.log = (msg) ->
    console.log msg



# document ready
$ ->
    Shifts.init()


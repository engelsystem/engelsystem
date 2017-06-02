
Shifts = window.Shifts || {}

Shifts.init = ->
    Shifts.$shiftplan = $('#shiftplan')
    if Shifts.$shiftplan.length > 0
        Shifts.log 'shifts init'
        Shifts.db.init ->
            Shifts.log 'db initialized'
            Shifts.fetcher.start ->
                Shifts.log 'fetch complete.'
                Shifts.render.header_footer()
                Shifts.render.shiftplan()
                Shifts.interaction.init()

                $('#datetimepicker').datetimepicker
                    value: moment.unix(Shifts.render.START_TIME).format('YYYY-MM-DD HH:mm')
                    timepicker: true
                    inline: true
                    format: 'Y-m-d H:i'
                    minDate: '-1970-01-05'
                    maxDate: '+1970-01-03'
                    onChangeDateTime: (dp, $input) ->
                        stime = parseInt moment($input.val()).format('X'), 10
                        Shifts.render.START_TIME = stime
                        Shifts.db.set_option 'filter_start_time', stime, ->
                            Shifts.render.shiftplan()

Shifts.log = (msg) ->
    console.log msg



# document ready
$ ->
    Shifts.init()


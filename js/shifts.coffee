###
# JavaScript Shiftplan Solution(tm) using distributed WebSQL technology in the browser-based cloud
# - empowering angels leveraging their visible return
#
###

Shifts = window.Shifts || {}

Shifts.init = ->

    Shifts.$shiftplan = $('#shiftplan')
    if Shifts.$shiftplan.length > 0
        Shifts.log 'shifts init'

        if 'websql=' not in document.cookie
            try
                dbtest = window.openDatabase '_engelsystem_test', '1.0', '', 10*1024*1024
                dbtest.transaction (t) ->
                document.cookie = 'websql=yes'
            catch
                document.cookie = 'websql=nope'
                window.location.href = ''

        Shifts.db.init ->
            Shifts.log 'db initialized'
            Shifts.lang.init ->
                Shifts.log 'languages initialized'
                Shifts.fetcher.start true, ->
                    Shifts.log 'fetch complete.'
                    Shifts.db.populate_ids ->
                        Shifts.render.header_footer()
                        Shifts.render.shiftplan()
                        Shifts.interaction.init()

                        # fetch data every 5mins
                        setInterval ->
                            Shifts.fetcher.start false, ->
                        , 1000 * 60 * 5

                        Shifts.db.get_shift_range (date_range) ->
                            waitforcal = setInterval ->
                                if Shifts.render.START_TIME
                                    theme = 'light'
                                    try
                                        css = $('link:nth(0)').attr('href')
                                        if css.match /theme[1,4,6]/ # define dark themes
                                            theme = 'dark'
                                    $('#datetimepicker').datetimepicker
                                        value: moment.unix(Shifts.render.START_TIME).format('YYYY-MM-DD HH:mm')
                                        timepicker: true
                                        inline: true
                                        theme: theme
                                        format: 'Y-m-d H:i'
                                        minDate: moment.unix(date_range[0]).format('YYYY-MM-DD')
                                        maxDate: moment.unix(date_range[1]).format('YYYY-MM-DD')
                                        onChangeDateTime: (dp, $input) ->
                                            stime = parseInt moment($input.val()).format('X'), 10
                                            Shifts.render.START_TIME = stime
                                            $('#filterbutton').removeAttr 'disabled'
                                            Shifts.db.set_option 'filter_start_time', stime, ->
                                                if Shifts.render.rendering_time < Shifts.render.render_threshold
                                                    Shifts.render.shiftplan()
                                    clearInterval waitforcal
                            , 1

Shifts.log = (msg) ->
    console.log msg



# document ready
$ ->
    Shifts.init()


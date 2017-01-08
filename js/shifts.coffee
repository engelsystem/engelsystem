
Shifts = window.Shifts || {}

Shifts.init = ->
    Shifts.$shiftplan = $('#shiftplan')
    if Shifts.$shiftplan.length > 0
        Shifts.log 'shifts init'
        Shifts.db.init ->
            Shifts.log 'db initialized'
            Shifts.fetcher.start ->
                Shifts.log 'fetch complete.'
                Shifts.render.shiftplan()

        $('body').on 'click', '#filterbutton', ->
            Shifts.render.shiftplan()
            return false

Shifts.log = (msg) ->
    console.info msg



# document ready
$ ->
    Shifts.init()


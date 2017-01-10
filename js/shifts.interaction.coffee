
Shifts.interaction =

    init: ->
        Shifts.interaction.on_filter_change()
        Shifts.interaction.on_filter_click()

    on_filter_change: ->
        false

    on_filter_click: ->
        Shifts.$shiftplan.on 'click', '#filterbutton', ->
            Shifts.render.shiftplan()
            return false


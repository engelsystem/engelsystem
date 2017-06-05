
Shifts.interaction =

    selected_rooms: []
    selected_angeltypes: []
    occupancy: 'free'
    datepicker_interval: false

    init: ->

        # prefill all rooms and angeltypes - done in Shifts.db.populate_ids()
        #

        # init frontend events
        Shifts.interaction.on_filter_change()
        Shifts.interaction.on_mass_select()
        Shifts.interaction.on_filter_click()

    on_filter_change: ->
        Shifts.$shiftplan.on 'change', '#selection_rooms input', ->
            Shifts.interaction.selected_rooms = []
            for room in $('#selection_rooms input')
                if room.checked
                    Shifts.interaction.selected_rooms.push parseInt(room.value, 10)
            Shifts.db.set_option 'filter_selected_rooms', Shifts.interaction.selected_rooms.join(','), ->

            $('#filterbutton').removeAttr 'disabled'

            if Shifts.render.rendering_time < 500
                Shifts.render.shiftplan()

        Shifts.$shiftplan.on 'change', '#selection_types input', ->
            Shifts.interaction.selected_angeltypes = []
            for type in $('#selection_types input')
                if type.checked
                    Shifts.interaction.selected_angeltypes.push parseInt(type.value, 10)
            Shifts.db.set_option 'filter_selected_angeltypes', Shifts.interaction.selected_angeltypes.join(','), ->

            $('#filterbutton').removeAttr 'disabled'

            if Shifts.render.rendering_time < 500
                Shifts.render.shiftplan()

    on_mass_select: ->
        Shifts.$shiftplan.on 'click', '.mass-select a', (ev) ->
            if $(this).parents('#selection_rooms').length
                if $(ev.target).attr('href') == '#all'
                    for room in $('#selection_rooms input')
                        $(room).prop 'checked', true
                        Shifts.interaction.selected_rooms.push parseInt(room.value, 10)
                    Shifts.db.set_option 'filter_selected_rooms', Shifts.interaction.selected_rooms.join(','), ->
                if $(ev.target).attr('href') == '#none'
                    for room in $('#selection_rooms input')
                        $(room).prop 'checked', false
                    Shifts.interaction.selected_rooms = []
                    Shifts.db.set_option 'filter_selected_rooms', 'none', ->

            if $(this).parents('#selection_types').length
                if $(ev.target).attr('href') == '#all'
                    for type in $('#selection_types input')
                        $(type).prop 'checked', true
                        Shifts.interaction.selected_angeltypes.push parseInt(type.value, 10)
                    Shifts.db.set_option 'filter_selected_angeltypes', Shifts.interaction.selected_angeltypes.join(','), ->
                if $(ev.target).attr('href') == '#none'
                    for type in $('#selection_types input')
                        $(type).prop 'checked', false
                    Shifts.interaction.selected_angeltypes = []
                    Shifts.db.set_option 'filter_selected_angeltypes', 'none', ->

            if $(this).parents('#selection_filled').length
                $all = $('#selection_filled a[href=#all]')
                $free = $('#selection_filled a[href=#free]')
                if $(ev.target).attr('href') == '#all'
                    Shifts.interaction.occupancy = 'all'
                    $all.removeClass 'btn-default'
                    $all.addClass 'btn-primary'
                    $free.removeClass 'btn-primary'
                    $free.addClass 'btn-default'
                if $(ev.target).attr('href') == '#free'
                    Shifts.interaction.occupancy = 'free'
                    $free.removeClass 'btn-default'
                    $free.addClass 'btn-primary'
                    $all.removeClass 'btn-primary'
                    $all.addClass 'btn-default'
                Shifts.db.set_option 'filter_occupancy', Shifts.interaction.occupancy, ->

            $('#filterbutton').removeAttr 'disabled'

            if Shifts.render.rendering_time < 500
                Shifts.render.shiftplan()

            return false

    on_filter_click: ->
        Shifts.$shiftplan.on 'click', '#filterbutton', ->
            Shifts.render.shiftplan()
            return false


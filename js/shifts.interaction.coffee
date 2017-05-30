
Shifts.interaction =

    selected_rooms: []
    selected_angeltypes: []
    selected_occupancy: []
    datepicker_interval: false

    init: ->

        # prefill all rooms and angeltypes - done in Shifts.db.populate_ids()
        #

        # prefill occupancy
        Shifts.interaction.selected_occupancy.push '1'
        Shifts.interaction.selected_occupancy.push '0'

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

            Shifts.render.shiftplan()

        Shifts.$shiftplan.on 'change', '#selection_types input', ->
            Shifts.interaction.selected_angeltypes = []
            for type in $('#selection_types input')
                if type.checked
                    Shifts.interaction.selected_angeltypes.push parseInt(type.value, 10)

            Shifts.render.shiftplan()

    on_mass_select: ->
        Shifts.$shiftplan.on 'click', '.mass-select a', (ev) ->

            if $(this).parents('#selection_rooms').length
                if $(ev.target).attr('href') == '#all'
                    for room in $('#selection_rooms input')
                        Shifts.interaction.selected_rooms.push parseInt(room.value, 10)
                if $(ev.target).attr('href') == '#none'
                    Shifts.interaction.selected_rooms = []

            if $(this).parents('#selection_types').length
                Shifts.log 'dagg'
                if $(ev.target).attr('href') == '#all'
                    for type in $('#selection_types input')
                        Shifts.interaction.selected_angeltypes.push parseInt(type.value, 10)
                if $(ev.target).attr('href') == '#none'
                    Shifts.interaction.selected_angeltypes = []

            if $(this).parents('#selection_filled').length
                if $(ev.target).attr('href') == '#all'
                    for occupancy in $('#selection_filled input')
                        Shifts.interaction.selected_occupancy.push parseInt(occupancy.value, 10)
                if $(ev.target).attr('href') == '#none'
                    Shifts.interaction.selected_occupancy = []

            Shifts.render.shiftplan()
            return false

    on_filter_click: ->
        Shifts.$shiftplan.on 'click', '#filterbutton', ->
            Shifts.render.shiftplan()
            return false


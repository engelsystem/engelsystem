
Shifts.interaction =

    selected_rooms: []
    selected_angeltypes: []
    selected_occupancy: []

    init: ->

        # prefill all rooms and angeltypes - done in Shifts.db.populate_ids()
        #

        # prefill occupancy
        Shifts.interaction.selected_occupancy.push '1'
        Shifts.interaction.selected_occupancy.push '0'

        # init frontend events
        Shifts.interaction.on_filter_change()
        Shifts.interaction.on_filter_click()

    on_filter_change: ->
        Shifts.$shiftplan.on 'change', '#selection_rooms input', ->
            Shifts.interaction.selected_rooms = []
            for room in $('#selection_rooms input')
                if room.checked
                    Shifts.interaction.selected_rooms.push parseInt(room.value, 10)

            Shifts.render.shiftplan()

    on_filter_click: ->
        Shifts.$shiftplan.on 'click', '#filterbutton', ->
            Shifts.render.shiftplan()
            return false


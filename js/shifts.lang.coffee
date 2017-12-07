
Shifts.lang =

    init: (done) ->
        try
            Shifts.lang.items = JSON.parse $('#translations').text()
        catch
            Shifts.lang.items = {}

        Shifts.lang.process_templates ->
            done()

    process_templates: (done) ->
        apply_translation = (text) ->
            text = text.replace /\[\[.*?\]\]/g, (x) ->
                x = x.replace('[[', '').replace(']]', '')
                return Shifts._(x)
            return text

        for k, v of Shifts.templates
            Shifts.templates[k] = apply_translation(v)

        done()

Shifts._ = (text) ->
    try
        t = Shifts.lang.items[text]
    if not t
        t = "[#{text}]"
        ###
        # translation definitions are stored in includes/pages/user_shifts_browser.php
        ###
        Shifts.log "Missing translation definition: \"#{text}\""

    return t


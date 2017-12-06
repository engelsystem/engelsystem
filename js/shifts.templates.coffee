
Shifts.templates =

    loading: '
<div class="loading-overlay" style="
position: absolute;
top: {{cal_t}}px;
left: {{cal_l}}px;
width: {{cal_w}}px;
height: {{cal_h}}px;
background: #fff;
opacity: 0.5;
z-index: 1000;
"></div>

<div class="loading-overlay-msg" style="
position: absolute;
top: {{msg_t}}px;
left: {{msg_l}}px;
width: 400px;
height: 80px;
padding: 1em;
text-align: center;
background: #fff;
border: 1px solid #999;
border-radius: 3px;
z-index: 1001;
">

Building view...

<div class="row" style="margin: 2px 0 0; width: 100%;">
  <div class="progress">
    <div id="cal_loading_progress" class="progress-bar" style="width: 0%">
  </div>
</div>'

    fetcher_status: '
<style>
@media (max-width: 1080px) and (min-width: 768px) {
    #fetcher_status {
        margin-top: 4em;
    }
}
</style>
<div id="fetcher_status">
    <span id="fetcher_statustext">Fetching data from server...</span> <span id="remaining_objects"></span>
    <div class="progress">
      <div id="progress_bar" class="progress-bar" style="width: 0%;">
        0%
      </div>
    </div>
</div>
<a id="abort" href="" class="btn btn-default btn-xs">Abort and switch to legacy view</a>'

    header_and_dateselect: '
<form class="form-inline" action="" method="get">
  <input type="hidden" name="p" value="user_shifts">
  <div class="row">
    <div class="col-md-6">
      <h1>Shifts</h1>
      <div class="form-group" style="width: 768px; height: 250px;">
        <input id="datetimepicker" type="hidden" />
      </div>
    </div>

    <div class="filter-form"></div>

  </div>
  <div class="row">
    <div class="col-md-6"></div>
    <div class="col-md-6">
        <div><sup>1</sup>The tasks shown here are influenced by the angeltypes you joined already!
        <a href="angeltypes?action=about">Description of the jobs.</a></div>
        <input id="filterbutton" class="btn btn-primary" type="submit" style="width: 75%; margin-bottom: 20px" value="Filter">
    </div>
  </div>

  <div class="shift-calendar">
      <div style="height: 100px;">
          Loading...
      </div>
  </div>'

    filter_form: '
    <div class="col-md-2">
        <div id="selection_rooms" class="selection rooms">
            <h4>Rooms</h4>
            {{#rooms}}
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="rooms[]" value="{{RID}}" {{#selected}}checked="checked"{{/selected}}> {{Name}}
                    </label>
                </div><br />
            {{/rooms}}
            <div class="form-group">
                <div class="btn-group mass-select">
                    <a href="#all" class="btn btn-default">All</a>
                    <a href="#none" class="btn btn-default">None</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div id="selection_types" class="selection types">
            <h4>Angeltypes<sup>1</sup></h4>
            {{#angeltypes}}
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="types[]" value="{{id}}" {{#selected}}checked="checked"{{/selected}}> {{name}}
                    </label>
                </div><br />
            {{/angeltypes}}
            <div class="form-group">
                <div class="btn-group mass-select">
                    <a href="#all" class="btn btn-default">All</a>
                    <a href="#none" class="btn btn-default">None</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div id="selection_filled" class="selection filled">
            <h4>Occupancy</h4>
            <div class="form-group">
                <div class="btn-group mass-select">
                    <a href="#all" class="btn btn-{{#occupancy}}{{all}}{{/occupancy}}">All</a>
                    <a href="#free" class="btn btn-{{#occupancy}}{{free}}{{/occupancy}}">Free</a>
                </div>
            </div>
        </div>
      </div>
    </div>'

    footer: '
</form>'

    shift_calendar: '
<div class="lane time">
  <div class="header">Time</div>
  {{#timelane_ticks}}
      {{#tick}}
          <div class="tick {{daytime}}"></div>
      {{/tick}}
      {{#tick_hour}}
          <div class="tick {{daytime}} hour">{{label}}</div>
      {{/tick_hour}}
      {{#tick_day}}
          <div class="tick {{daytime}} day">{{label}}</div>
      {{/tick_day}}
  {{/timelane_ticks}}
</div>

{{#rooms}}
    {{#lanes}}
      <div class="lane">
        <div class="header">
          <a href="rooms?action=view&room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{Name}}</a>
        </div>
        {{#shifts}}
            {{#tick}}
                <div class="tick {{daytime}}"></div>
            {{/tick}}
            {{#tick_hour}}
                <div class="tick {{daytime}} hour">{{text}}</div>
            {{/tick_hour}}
            {{#tick_day}}
                <div class="tick {{daytime}} day">{{text}}</div>
            {{/tick_day}}
            {{#shift}}
                <div class="shift panel panel-{{state_class}}" style="height: {{height}}px;">
                  <div class="panel-heading">
                    <a href="shifts?action=view&amp;shift_id={{SID}}">{{starttime}} ‐ {{endtime}} — {{shifttype_name}}</a>
                {{#is_admin}}
                    <div class="pull-right">
                      <div class="btn-group">
                        <a href="user-shifts?edit_shift={{SID}}" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span></a>
                        <a href="user-shifts?delete_shift={{SID}}" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-trash"></span></a>
                      </div>
                    </div>
                {{/is_admin}}
                  </div>
                  <div class="panel-body">
                    {{#shift_title}}<span class="glyphicon glyphicon-info-sign"></span> {{shift_title}}<br />{{/shift_title}}
                    <a href="rooms?action=view&amp;room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{room_name}}</a>
                  </div>
                  <ul class="list-group">
                    {{#angeltypes}}
                        {{#restricted}}
                            <li class="list-group-item"><strong><a href="angeltypes?action=view&amp;angeltype_id={{TID}}"> <span class="glyphicon glyphicon-lock"></span> {{at_name}}</a>:</strong>
                        {{/restricted}}
                        {{^restricted}}
                            <li class="list-group-item"><strong><a href="angeltypes?action=view&amp;angeltype_id={{TID}}">{{at_name}}</a>:</strong>
                        {{/restricted}}
                          {{#angels}}
                              {{#freeloaded}}
                                <span style="text-decoration: line-through;"><a href="users?action=view&amp;user_id={{UID}}"><span class="icon-icon_angel"></span> {{Nick}}</a></span>,
                              {{/freeloaded}}
                              {{^freeloaded}}
                                <span><a href="users?action=view&amp;user_id={{UID}}"><span class="icon-icon_angel"></span> {{Nick}}</a></span>,
                              {{/freeloaded}}
                          {{/angels}}
                        {{#helpers_needed}}
                            {{#shift_ended}}
                                  {{angels_needed}} helpers needed (ended)
                            {{/shift_ended}}
                            {{^shift_ended}}
                                {{#restricted}}
                                  {{angels_needed}} helpers needed <span class="glyphicon glyphicon-lock"></span>
                                  {{#confirmed}}
                                    <a href="user-shifts?shift_id={{SID}}&amp;type_id={{TID}}" class="btn btn-default btn-xs btn-primary">Sign up</a>
                                  {{/confirmed}}
                                {{/restricted}}
                                {{^restricted}}
                                    {{#angeltype_mismatch}}
                                      {{angels_needed}} helpers needed
                                      <a href="user_angeltypes?action=add&amp;angeltype_id={{TID}}" class="btn btn-default btn-xs">Become {{at_name}}</a>
                                    {{/angeltype_mismatch}}
                                    {{^angeltype_mismatch}}
                                      <a href="user-shifts?shift_id={{SID}}&amp;type_id={{TID}}">{{angels_needed}} helpers needed</a>
                                      <a href="user-shifts?shift_id={{SID}}&amp;type_id={{TID}}" class="btn btn-default btn-xs btn-primary">Sign up</a>
                                    {{/angeltype_mismatch}}
                                {{/restricted}}
                            {{/shift_ended}}
                        {{/helpers_needed}}
                    {{/angeltypes}}
                    </li>
                    <li class="list-group-item">
                      <a href="user-shifts?edit_shift={{SID}}" class="btn btn-default btn-xs">Neue Engel hinzufügen</a>
                    </li>
                  </ul>
                  <div class="shift-spacer"></div>
                </div>
            {{/shift}}
        {{/shifts}}
      </div>
    {{/lanes}}
{{/rooms}}
{{^rooms}}
<div class="alert alert-warning" style="margin-top: 2em;">No shifts could be found for the selected date.</div>
{{/rooms}}'


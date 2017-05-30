
Shifts.templates =

    filter_form: '
<form class="form-inline" action="" method="get">
  <input type="hidden" name="p" value="user_shifts">
  <div class="row">
    <div class="col-md-6">
      <h1>Shifts</h1>
      <div class="form-group">
        <input id="datetimepicker" type="text" />
          <select class="form-control" id="start_day" name="start_day">
            <option value="2017-01-08">2017-01-08</option>
            <option value="2017-01-09">2017-01-09</option>
            <option value="2017-01-10" selected="selected">2017-01-10</option>
            <option value="2017-01-11">2017-01-11</option>
            <option value="2017-01-12">2017-01-12</option>
            <option value="2017-01-13">2017-01-13</option>
            <option value="2017-01-14">2017-01-14</option>
            <option value="2017-01-15">2017-01-15</option>
            <option value="2017-01-16">2017-01-16</option>
          </select>
      </div>
      <div class="form-group">
        <div class="input-group">
          <input class="form-control" type="text" id="start_time" name="start_time" size="5" pattern="^\d{1,2}:\d{2}$" placeholder="HH:MM" maxlength="5" value="00:00">
          <div class="input-group-btn">
            <button class="btn btn-default" title="Now" type="button">
              <span class="glyphicon glyphicon-time"></span>
            </button>
          </div>
        </div>
      </div>
      &#8211;
      <div class="form-group">
          <select class="form-control" id="start_day" name="start_day">
            <option value="2017-01-08">2017-01-08</option>
            <option value="2017-01-09">2017-01-09</option>
            <option value="2017-01-10">2017-01-10</option>
            <option value="2017-01-11" selected="selected">2017-01-11</option>
            <option value="2017-01-12">2017-01-12</option>
            <option value="2017-01-13">2017-01-13</option>
            <option value="2017-01-14">2017-01-14</option>
            <option value="2017-01-15">2017-01-15</option>
            <option value="2017-01-16">2017-01-16</option>
          </select>
      </div>
      <div class="form-group">
        <div class="input-group">
          <input class="form-control" type="text" id="end_time" name="end_time" size="5" pattern="^\d{1,2}:\d{2}$" placeholder="HH:MM" maxlength="5" value="00:00">
          <div class="input-group-btn">
            <button class="btn btn-default" title="Now" type="button">
              <span class="glyphicon glyphicon-time"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
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
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="filled[]" value="0" checked="checked"> occupied
                </label>
            </div><br />
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="filled[]" value="1" checked="checked"> free
                </label>
            </div><br />
            <div class="form-group">
                <div class="btn-group mass-select">
                    <a href="#all" class="btn btn-default">All</a>
                    <a href="#none" class="btn btn-default">None</a>
                </div>
            </div>
        </div>
      </div>
    </div>
	<div class="row">
		<div class="col-md-6">
            <div><sup>1</sup>The tasks shown here are influenced by the angeltypes you joined already!
            <a href="?p=angeltypes&amp;action=about">Description of the jobs.</a></div>
            <input id="filterbutton" class="btn btn-primary" type="submit" style="width: 75%; margin-bottom: 20px" value="Filter">
		</div>
	</div>
</form>'

    shift_calendar: '
<div class="shift-calendar">

  <div class="lane time">
    <div class="header">Time</div>
    {{#timelane_ticks}}
        {{#tick}}
            <div class="tick"></div>
        {{/tick}}
        {{#tick_hour}}
            <div class="tick hour">{{label}}</div>
        {{/tick_hour}}
        {{#tick_day}}
            <div class="tick day">{{label}}</div>
        {{/tick_day}}
    {{/timelane_ticks}}
  </div>

{{#rooms}}
    {{#lanes}}
      <div class="lane">
        <div class="header">
          <a href="?p=rooms&action=view&room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{Name}}</a>
        </div>
        {{#shifts}}
            {{#tick}}
                <div class="tick"></div>
            {{/tick}}
            {{#tick_hour}}
                <div class="tick hour">{{text}}</div>
            {{/tick_hour}}
            {{#tick_day}}
                <div class="tick day">{{text}}</div>
            {{/tick_day}}
            {{#shift}}
                <div class="shift panel panel-{{state_class}}" style="height: {{height}}px;">
                  <div class="panel-heading">
                    <a href="?p=shifts&amp;action=view&amp;shift_id={{SID}}">{{starttime}} ‐ {{endtime}} — {{shifttype_name}}</a>
                    <div class="pull-right">
                      <div class="btn-group">
                        <a href="?p=user_shifts&amp;edit_shift={{SID}}" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span></a>
                        <a href="?p=user_shifts&amp;delete_shift={{SID}}" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-trash"></span></a>
                      </div>
                    </div>
                  </div>
                  <div class="panel-body">
                    {{#shift_title}}<span class="glyphicon glyphicon-info-sign"></span> {{shift_title}}<br />{{/shift_title}}
                    <a href="?p=rooms&amp;action=view&amp;room_id={{RID}}"><span class="glyphicon glyphicon-map-marker"></span> {{room_name}}</a>
                  </div>
                  <ul class="list-group">
                    <li class="list-group-item"><strong><a href="?p=angeltypes&amp;action=view&amp;angeltype_id=104575">Angel</a>:</strong>
                      <span style=""><a class="" href="?p=users&amp;action=view&amp;user_id=1755"><span class="icon-icon_angel"></span> Pantomime</a></span>,
                      <span style=""><a class="" href="?p=users&amp;action=view&amp;user_id=50"><span class="icon-icon_angel"></span> sandzwerg</a></span>
                    </li>
                    <li class="list-group-item">
                      <a href="?p=user_shifts&amp;shift_id=2696&amp;type_id=104575" class="btn btn-default btn-xs">Neue Engel hinzufügen</a>
                    </li>
                  </ul>
                  <div class="shift-spacer"></div>
                </div>
            {{/shift}}
        {{/shifts}}
      </div>
    {{/lanes}}
{{/rooms}}
</div>'


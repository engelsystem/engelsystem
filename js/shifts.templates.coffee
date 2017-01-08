
Shifts.templates =

    filter_form: '
<form class="form-inline" action="" method="get">
  <input type="hidden" name="p" value="user_shifts">
  <div class="row">
    <div class="col-md-6">
      <h1>%title%</h1>
      <div class="form-group">%start_select%</div>
      <div class="form-group">
        <div class="input-group">
          <input class="form-control" type="text" id="start_time" name="start_time" size="5" pattern="^\d{1,2}:\d{2}$" placeholder="HH:MM" maxlength="5" value="%start_time%">
          <div class="input-group-btn">
            <button class="btn btn-default" title="Now" type="button" onclick="">
              <span class="glyphicon glyphicon-time"></span>
            </button>
          </div>
        </div>
      </div>
      &#8211;
      <div class="form-group">%end_select%</div>
      <div class="form-group">
        <div class="input-group">
          <input class="form-control" type="text" id="end_time" name="end_time" size="5" pattern="^\d{1,2}:\d{2}$" placeholder="HH:MM" maxlength="5" value="%end_time%">
          <div class="input-group-btn">
            <button class="btn btn-default" title="Now" type="button" onclick="">
              <span class="glyphicon glyphicon-time"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-2">%room_select%</div>
    <div class="col-md-2">%type_select%</div>
    <div class="col-md-2">%filled_select%</div>
  </div>
	<div class="row">
		<div class="col-md-6">
        <div>%task_notice%</div>
        <input id="filterbutton" class="btn btn-primary" type="submit" style="width: 75%; margin-bottom: 20px" value="%filter%">
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
                <div class="shift panel panel-success" style="height: {{height}}px;">
                  <div class="panel-heading">
                    <a href="?p=shifts&amp;action=view&amp;shift_id=2696">00:00 ‐ 02:00 — {{shifttype_name}}</a>
                    <div class="pull-right">
                      <div class="btn-group">
                        <a href="?p=user_shifts&amp;edit_shift=2696" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span></a>
                        <a href="?p=user_shifts&amp;delete_shift=2696" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-trash"></span></a>
                      </div>
                    </div>
                  </div>
                  <div class="panel-body">
                    {{#shift_title}}<span class="glyphicon glyphicon-info-sign"></span> {{shift_title}}<br />{{/shift_title}}
                    <a href="?p=rooms&amp;action=view&amp;room_id=42"><span class="glyphicon glyphicon-map-marker"></span> {{room_name}}</a>
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


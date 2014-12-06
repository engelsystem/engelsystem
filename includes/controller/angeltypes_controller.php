<?php

/**
 * Text for Angeltype related links.
 */
function angeltypes_title() {
  return _("Angeltypes");
}

/**
 * Route angeltype actions.
 */
function angeltypes_controller() {
  if (! isset($_REQUEST['action']))
    $_REQUEST['action'] = 'list';

  switch ($_REQUEST['action']) {
    default:
    case 'list':
      return angeltypes_list_controller();
    case 'view':
      return angeltype_controller();
    case 'edit':
      return angeltype_edit_controller();
    case 'delete':
      return angeltype_delete_controller();
    case 'about':
      return angeltypes_about_controller();
  }
}

/**
 * Job description for all angeltypes (public to everyone)
 */
function angeltypes_about_controller() {
  global $privileges, $user;

  if (isset($user))
    $angeltypes = AngelTypes_with_user($user);
  else
    $angeltypes = AngelTypes();
  if ($angeltypes === false)
    engelsystem_error("Unable to load angeltypes.");

  return array(
      _("Teams/Job description"),
      AngelTypes_about_view($angeltypes, isset($user))
  );
}

/**
 * Delete an Angeltype.
 */
function angeltype_delete_controller() {
  global $privileges, $user;

  if (! in_array('admin_angel_types', $privileges))
    redirect(page_link_to('angeltypes'));

  $angeltype = AngelType($_REQUEST['angeltype_id']);
  if ($angeltype === false)
    engelsystem_error("Unable to load angeltype.");
  if ($angeltype == null)
    redirect(page_link_to('angeltypes'));

  if (isset($_REQUEST['confirmed'])) {
    $result = AngelType_delete($angeltype);
    if ($result === false)
      engelsystem_error("Unable to delete angeltype.");

    engelsystem_log("Deleted angeltype: " . $name);
    success(sprintf(_("Angeltype %s deleted."), $name));
    redirect(page_link_to('angeltypes'));
  }

  return array(
      sprintf(_("Delete angeltype %s"), $angeltype['name']),
      AngelType_delete_view($angeltype)
  );
}

/**
 * Change an Angeltype.
 */
function angeltype_edit_controller() {
  global $privileges, $user;

  $name = "";
  $restricted = false;
  $description = "";

  if (isset($_REQUEST['angeltype_id'])) {
    $angeltype = AngelType($_REQUEST['angeltype_id']);
    if ($angeltype === false)
      engelsystem_error("Unable to load angeltype.");
    if ($angeltype == null)
      redirect(page_link_to('angeltypes'));

    $name = $angeltype['name'];
    $restricted = $angeltype['restricted'];
    $description = $angeltype['description'];

    if (! User_is_AngelType_coordinator($user, $angeltype))
      redirect(page_link_to('angeltypes'));
  } else {
    if (! in_array('admin_angel_types', $privileges))
      redirect(page_link_to('angeltypes'));
  }

  // In coordinator mode only allow to modify description
  $coordinator_mode = ! in_array('admin_angel_types', $privileges);

  if (isset($_REQUEST['submit'])) {
    $ok = true;

    if (! $coordinator_mode) {
      if (isset($_REQUEST['name'])) {
        list($valid, $name) = AngelType_validate_name($_REQUEST['name'], $angeltype);
        if (! $valid) {
          $ok = false;
          error(_("Please check the name. Maybe it already exists."));
        }
      }

      $restricted = isset($_REQUEST['restricted']);
    }

    if (isset($_REQUEST['description']))
      $description = strip_request_item_nl('description');

    if ($ok) {
      $restricted = $restricted ? 1 : 0;
      if (isset($angeltype)) {
        $result = AngelType_update($angeltype['id'], $name, $restricted, $description);
        if ($result === false)
          engelsystem_error("Unable to update angeltype.");
        engelsystem_log("Updated angeltype: " . $name . ", restricted: " . $restricted);
        $angeltype_id = $angeltype['id'];
      } else {
        $angeltype_id = AngelType_create($name, $restricted, $description);
        if ($angeltype_id === false)
          engelsystem_error("Unable to create angeltype.");
        engelsystem_log("Created angeltype: " . $name . ", restricted: " . $restricted);
      }

      success("Angel type saved.");
      redirect(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype_id);
    }
  }

  return array(
      sprintf(_("Edit %s"), $name),
      AngelType_edit_view($name, $restricted, $description, $coordinator_mode)
  );
}

/**
 * View details of a given angeltype.
 */
function angeltype_controller() {
  global $privileges, $user;

  if (! in_array('angeltypes', $privileges))
    redirect('?');

  if (! isset($_REQUEST['angeltype_id']))
    redirect(page_link_to('angeltypes'));

  $angeltype = AngelType($_REQUEST['angeltype_id']);
  if ($angeltype === false)
    engelsystem_error("Unable to load angeltype.");
  if ($angeltype == null)
    redirect(page_link_to('angeltypes'));

  $user_angeltype = UserAngelType_by_User_and_AngelType($user, $angeltype);
  if ($user_angeltype === false)
    engelsystem_error("Unable to load user angeltype.");

  $members = Users_by_angeltype($angeltype);
  if ($members === false)
    engelsystem_error("Unable to load members.");

  return array(
      sprintf(_("Team %s"), $angeltype['name']),
      AngelType_view($angeltype, $members, $user_angeltype, in_array('admin_user_angeltypes', $privileges) || $user_angeltype['coordinator'], in_array('admin_angel_types', $privileges), $user_angeltype['coordinator'])
  );
}

/**
 * View a list of all angeltypes.
 */
function angeltypes_list_controller() {
  global $privileges, $user;

  if (! in_array('angeltypes', $privileges))
    redirect('?');

  $angeltypes = AngelTypes_with_user($user);
  if ($angeltypes === false)
    engelsystem_error("Unable to load angeltypes.");

  foreach ($angeltypes as &$angeltype) {
    $actions = array(
        button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'],_("view"),"btn-xs")
        //'<a class="view btn btn-default" href="' . page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'] . '">' . _("view") . '</a>'
    );

    if (in_array('admin_angel_types', $privileges)) {
      $actions[] = button(page_link_to('angeltypes') . '&action=edit&angeltype_id=' . $angeltype['id'], _("edit"), "btn-xs");
      $actions[] = button(page_link_to('angeltypes') . '&action=delete&angeltype_id=' . $angeltype['id'], _("delete"), "btn-xs");

      //$actions[] = '<a class="edit" href="' . page_link_to('angeltypes') . '&action=edit&angeltype_id=' . $angeltype['id'] . '">' . _("edit") . '</a>';
      //$actions[] = '<a class="delete" href="' . page_link_to('angeltypes') . '&action=delete&angeltype_id=' . $angeltype['id'] . '">' . _("delete") . '</a>';
    }

    $angeltype['membership'] = AngelType_render_membership($angeltype);
    if ($angeltype['user_angeltype_id'] != null) {
      //$actions[] = '<a class="cancel" href="' . page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $angeltype['user_angeltype_id'] . '">' . _("leave") . '</a>';
      $actions[] = button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $angeltype['user_angeltype_id'], _("leave"), "btn-xs");
    } else {
      $actions[] = button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], _("join"), "btn-xs");
      //$actions[] = '<a class="add" href="' . page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'] . '">' . _("join") . '</a>';
    }

    $angeltype['restricted'] = $angeltype['restricted'] ? glyph('lock') : '';
    $angeltype['name'] = '<a href="' . page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'] . '">' . $angeltype['name'] . '</a>';

    $angeltype['actions'] = table_buttons($actions);
  }

  return array(
      angeltypes_title(),
      AngelTypes_list_view($angeltypes, in_array('admin_angel_types', $privileges))
  );
}
?>

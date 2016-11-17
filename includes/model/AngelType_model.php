<?php

/**
 * Returns an array containing the basic attributes of angeltypes.
 * FIXME! This is the big sign for needing entity objects
 */
function AngelType_new() {
  return [
      'id' => null,
      'name' => "",
      'restricted' => false,
      'description' => '',
      'requires_driver_license' => false 
  ];
}

/**
 * Delete an Angeltype.
 *
 * @param Angeltype $angeltype          
 */
function AngelType_delete($angeltype) {
  $result = sql_query("
      DELETE FROM `AngelTypes` 
      WHERE `id`='" . sql_escape($angeltype['id']) . "' 
      LIMIT 1");
  if ($result === false) {
    engelsystem_error("Unable to delete angeltype.");
  }
  engelsystem_log("Deleted angeltype: " . AngelType_name_render($angeltype));
  return $result;
}

/**
 * Update Angeltype.
 *
 * @param Angeltype $angeltype
 *          The angeltype
 */
function AngelType_update($angeltype) {
  $result = sql_query("
      UPDATE `AngelTypes` SET 
      `name`='" . sql_escape($angeltype['name']) . "', 
      `restricted`=" . sql_bool($angeltype['restricted']) . ",
      `description`='" . sql_escape($angeltype['description']) . "',
      `requires_driver_license`=" . sql_bool($angeltype['requires_driver_license']) . "
      WHERE `id`='" . sql_escape($angeltype['id']) . "'");
  if ($result === false) {
    engelsystem_error("Unable to update angeltype.");
  }
  engelsystem_log("Updated angeltype: " . $angeltype['name'] . ($angeltype['restricted'] ? ", restricted" : "") . ($angeltype['requires_driver_license'] ? ", requires driver license" : ""));
  return $result;
}

/**
 * Create an Angeltype.
 *
 * @param Angeltype $angeltype
 *          The angeltype
 * @return the created angeltype
 */
function AngelType_create($angeltype) {
  $result = sql_query("
      INSERT INTO `AngelTypes` SET 
      `name`='" . sql_escape($angeltype['name']) . "', 
      `restricted`=" . sql_bool($angeltype['restricted']) . ",
      `description`='" . sql_escape($angeltype['description']) . "',
      `requires_driver_license`=" . sql_bool($angeltype['requires_driver_license']));
  if ($result === false) {
    engelsystem_error("Unable to create angeltype.");
  }
  $angeltype['id'] = sql_id();
  engelsystem_log("Created angeltype: " . $angeltype['name'] . ($angeltype['restricted'] ? ", restricted" : "") . ($angeltype['requires_driver_license'] ? ", requires driver license" : ""));
  return $angeltype;
}

/**
 * Validates a name for angeltypes.
 * Returns ValidationResult containing validation success and validated name.
 *
 * @param string $name
 *          Wanted name for the angeltype
 * @param AngelType $angeltype
 *          The angeltype the name is for
 * @return ValidationResult result and validated name
 */
function AngelType_validate_name($name, $angeltype) {
  $name = strip_item($name);
  if ($name == "") {
    return new ValidationResult(false, "");
  }
  if ($angeltype != null && isset($angeltype['id'])) {
    $valid = sql_num_query("
        SELECT * 
        FROM `AngelTypes` 
        WHERE `name`='" . sql_escape($name) . "' 
        AND NOT `id`='" . sql_escape($angeltype['id']) . "'
        LIMIT 1") == 0;
    return new ValidationResult($valid, $name);
  }
  $valid = sql_num_query("
        SELECT `id` 
        FROM `AngelTypes` 
        WHERE `name`='" . sql_escape($name) . "' 
        LIMIT 1") == 0;
  return new ValidationResult($valid, $name);
}

/**
 * Returns all angeltypes and subscription state to each of them for given user.
 *
 * @param User $user          
 */
function AngelTypes_with_user($user) {
  $result = sql_select("
      SELECT `AngelTypes`.*, 
      `UserAngelTypes`.`id` as `user_angeltype_id`,
      `UserAngelTypes`.`confirm_user_id`,
      `UserAngelTypes`.`supporter`
      FROM `AngelTypes` 
      LEFT JOIN `UserAngelTypes` ON `AngelTypes`.`id`=`UserAngelTypes`.`angeltype_id` 
      AND `UserAngelTypes`.`user_id`=" . $user['UID'] . "
      ORDER BY `name`");
  if ($result === false) {
    engelsystem_error("Unable to load angeltypes.");
  }
  return $result;
}

/**
 * Returns all angeltypes.
 */
function AngelTypes() {
  $result = sql_select("
      SELECT * 
      FROM `AngelTypes` 
      ORDER BY `name`");
  if ($result === false) {
    engelsystem_error("Unable to load angeltypes.");
  }
  return $result;
}

/**
 * Returns AngelType id array
 */
function AngelType_ids() {
  $result = sql_select("SELECT `id` FROM `AngelTypes`");
  if ($result === false) {
    engelsystem_error("Unable to load angeltypes.");
  }
  return select_array($result, 'id', 'id');
}

/**
 * Returns angelType by id.
 *
 * @param $angeltype_id angelType
 *          ID
 */
function AngelType($angeltype_id) {
  $angelType_source = sql_select("SELECT * FROM `AngelTypes` WHERE `id`='" . sql_escape($angeltype_id) . "' LIMIT 1");
  if ($angelType_source === false) {
    engelsystem_error("Unable to load angeltype.");
  }
  if (count($angelType_source) > 0) {
    return $angelType_source[0];
  }
  return null;
}

?>
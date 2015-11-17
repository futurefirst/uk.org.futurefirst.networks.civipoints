<?php

class CRM_Points_BAO_Points extends CRM_Points_DAO_Points {
  const DAO_NAME    = 'CRM_Points_DAO_Points';
  const ENTITY_NAME = 'Points';

  /**
   * Get details of available action links.
   *
   * @return array In the form accepted by CRM_Core_Action::formLink
   */
  public static function actionLinks() {
    return array(
      CRM_Core_Action::ADD    => array(
        'name'  => ts('Grant'),
        'title' => ts('Grant Points'),
        'url'   => 'civicrm/points/grant',
        'qs'    => 'cid=%%cid%%',
      ),
      CRM_Core_Action::UPDATE => array(
        'name'  => ts('Edit'),
        'title' => ts('Edit Existing Points'),
        'url'   => 'civicrm/points/grant',
        'qs'    => 'pid=%%pid%%',
      ),
      CRM_Core_Action::DELETE => array(
        'name'  => ts('Delete'),
        'title' => ts('Delete Existing Points'),
        'url'   => 'civicrm/points/delete',
        'qs'    => 'pid=%%pid%%',
      ),
    );
  }

  /**
   * Create a new Points based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Points_DAO_Points|NULL
   */
  public static function create($params) {
    $hook = empty($params['id']) ? 'create' : 'edit';

    // Basically just runs hooks and passes parameters through to the DAO
    CRM_Utils_Hook::pre($hook, self::ENTITY_NAME, CRM_Utils_Array::value('id', $params), $params);
    $className = self::DAO_NAME;
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, self::ENTITY_NAME, $instance->id, $instance);

    return $instance;
  }

  /**
   * Gets the balance of points at a given date.
   *
   * @param array $params
   * @return int
   */
  public static function getSum($params) {
    // Clear any default SELECT parameters, just want the sum as of that date
    $instance = self::instanceWithEffectiveDate($params);
    $instance->selectAdd();
    $instance->selectAdd("SUM(`points`) AS `sum_points`");

    $instance->find(TRUE);
    $sum = $instance->sum_points;
    self::hook_civicrm_points_sum($sum, $instance);
    return $sum;
  }

  /**
   * Provides a custom hook to change a calculated points total in-flight.
   *
   * @param int $sum
   * @param CRM_Points_DAO_Points $dao
   */
  protected static function hook_civicrm_points_sum(&$sum, &$dao) {
    // The number of parameters to CRM_Utils_Hook::invoke changes from 4.4 to 4.5
    $version = civicrm_api('System', 'getvalue', array('version' => 3, 'return' => 'version'));
    $vparts  = explode('.', $version);
    if ($vparts[0] > 4 || ($vparts[0] == 4 && $vparts[1] > 4)) {
      return CRM_Utils_Hook::singleton()->invoke(2, $sum, $dao, $sum, $sum, $sum, $sum, 'civicrm_points_sum');
    }
    else {
      return CRM_Utils_Hook::singleton()->invoke(2, $sum, $dao, $sum, $sum, $sum, 'civicrm_points_sum');
    }
  }

  /**
   * Create a new DAO instance with additional WHERE conditions for the
   * effective date at which Points records should be found.
   *
   * @param array $params Must include 'date'
   * @return CRM_Points_DAO_Points
   */
  protected static function instanceWithEffectiveDate($params) {
    // Check explicitly that an effective date has been specified
    // (this isn't a DAO parameter), then create a DAO
    CRM_Utils_Type::validate($params['date'], 'Date');
    $className = self::DAO_NAME;
    $instance = new $className();
    _civicrm_api3_dao_set_filter($instance, $params, TRUE, 'Points');

    // Apply additional WHERE conditions based on that date
    $instance->whereAdd("`start_date` <= '{$params['date']}'");
    $instance->whereAdd("`end_date`   >= '{$params['date']}' OR `end_date` IS NULL");
    return $instance;
  }

  /**
   * Extends the basic Get action with ability to get Points records effective
   * at a certain date. 'filter.end_date_low' for example was not right as it
   * did not include records where end_date is NULL.
   *
   * @param array $params Must include 'date'
   * @return array
   */
  public static function getEffective($params) {
    $instance = self::instanceWithEffectiveDate($params);
    return _civicrm_api3_dao_to_array($instance, $params, FALSE, 'Points');
  }
}

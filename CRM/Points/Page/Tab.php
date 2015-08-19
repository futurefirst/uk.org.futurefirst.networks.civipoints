<?php

require_once 'CRM/Core/Page.php';

class CRM_Points_Page_Tab extends CRM_Core_Page {
  /**
   * Assign data to be output on the contact's points tab.
   */
  function run() {
    // cid and type parameters to be passed in by the tabs hook.
    // Abort now if they are not supplied.
    $store = NULL;
    $cid  = CRM_Utils_Request::retrieve('cid',  'Positive', $store, TRUE);
    $type = CRM_Utils_Request::retrieve('type', 'String',   $store, TRUE);

    // Get points assignments that are currently in effect.
    $pointsResult = civicrm_api('Points', 'geteffective', array(
      'version'        => 3,
      'sequential'     => 1,
      'contact_id'     => $cid,
      'points_type_id' => $type,
      'options'        => array(
        'limit'        => 0,
      ),
      'api.contact.getvalue' => array(
        'id'     => '$value.grantor_contact_id',
        'return' => 'sort_name',
      ),
    ));
    if (civicrm_error($pointsResult)) {
      CRM_Core_Error::fatal($pointsResult['error_message']);
    }

    // Add some extra fields for ease of presentation.
    foreach ($pointsResult['values'] as &$points) {
      $points['grantor_sort_name']    = $points['api.contact.getvalue'];
      $points['grant_date_time_show'] = CRM_Utils_Date::customFormat($points['grant_date_time']);
      $points['start_date_show']      = CRM_Utils_Date::customFormat($points['start_date']);
      $points['end_date_show']        = CRM_Utils_Date::customFormat($points['end_date']);
    }

    $this->assign('points', $pointsResult['values']);
    parent::run();
  }
}

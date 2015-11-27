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

    foreach ($pointsResult['values'] as &$points) {
      // Add some extra fields for ease of presentation.
      $points['grantor_sort_name']    = $points['api.contact.getvalue'];
      $points['grant_date_time_show'] = CRM_Utils_Date::customFormat($points['grant_date_time']);
      $points['start_date_show']      = CRM_Utils_Date::customFormat($points['start_date']);
      $points['end_date_show']        = CRM_Utils_Date::customFormat($points['end_date']);

      $grantor_url = CRM_Utils_System::url('civicrm/contact/view', array(
        'reset' => 1,
        'cid'   => $points['grantor_contact_id'],
      ));
      $points['grantor_link'] = "<a href='{$grantor_url}' title='" . ts('View Contact') . "'>{$points['api.contact.getvalue']}</a>";

      // Add actions links
      $points['links'] = CRM_Core_Action::formLink(
        CRM_Points_BAO_Points::actionLinks(),
        CRM_Core_Action::UPDATE | CRM_Core_Action::DELETE,
        array(
          'cid'  => $points['contact_id'],
          'type' => $points['points_type_id'],
          'pid'  => $points['id'],
        )
      );
    }

    $this->assign('points', $pointsResult['values']);
    $this->assign('cid',    $cid);
    parent::run();
  }
}

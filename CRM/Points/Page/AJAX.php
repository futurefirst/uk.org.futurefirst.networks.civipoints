<?php

class CRM_Points_Page_AJAX {
  /**
   * Get updated data for the points tab over AJAX.
   * Takes parameters from the HTTP request variables.
   * Returns results by echoing JSON to stdout.
   * Does not return to the calling function.
   */
  public static function getEffectiveAjax() {
    // cid and type parameters to be passed in by the AJAX request.
    // Abort now if they are not supplied.
    $store = NULL;
    $cid  = CRM_Utils_Request::retrieve('cid',  'Positive', $store, TRUE);
    $type = CRM_Utils_Request::retrieve('type', 'String',   $store, TRUE);
    $date = CRM_Utils_Request::retrieve('date', 'String',   $store, FALSE); // May be empty, may be 'all'
    if (!empty($date) && $date != 'all') {
      $date = CRM_Utils_Date::customFormat($date, '%Y%m%d');
      CRM_Utils_Type::validate($date, 'Date');
    }

    // Get points assignments that are currently in effect.
    $params = array(
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
    );
    if (!empty($date)) {
      $params['date'] = $date;
    }
    $pointsResult = civicrm_api('Points', 'geteffective', $params);
    if (civicrm_error($pointsResult)) {
      CRM_Core_Error::fatal($pointsResult['error_message']);
    }

    $results = array();
    foreach ($pointsResult['values'] as $points) {
      // Add some extra fields for ease of presentation.
      $result = array();
      $result[] = $points['points'];

      $grantor_url = CRM_Utils_System::url('civicrm/contact/view', array(
        'reset' => 1,
        'cid'   => $points['grantor_contact_id'],
      ));
      $result[] = "<a href='{$grantor_url}' title='" . ts('View Contact') . "'>{$points['api.contact.getvalue']}</a>";
      $result[] = $points['api.contact.getvalue'];

      $result[] = CRM_Utils_Date::customFormat($points['grant_date_time']);
      $result[] = $points['grant_date_time'];
      $result[] = CRM_Utils_Date::customFormat($points['start_date']);
      $result[] = $points['start_date'];
      $result[] = CRM_Utils_Date::customFormat($points['end_date']);
      $result[] = $points['end_date'];

      $result[] = $points['description'];
      $result[] = $points['entity_table'];
      $result[] = $points['entity_id'];

      // Add actions links
      $result[] = CRM_Core_Action::formLink(
        CRM_Points_BAO_Points::actionLinks(),
        CRM_Core_Action::UPDATE | CRM_Core_Action::DELETE,
        array(
          'cid'  => $points['contact_id'],
          'type' => $points['points_type_id'],
          'pid'  => $points['id'],
        )
      );

      $results[] = $result;
    }
    echo json_encode($results);
    CRM_Utils_System::civiExit();
  }
}

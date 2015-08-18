<?php

require_once 'CRM/Core/Page.php';

class CRM_Points_Page_Tab extends CRM_Core_Page {
  function run() {
    $store = NULL;
    $cid  = CRM_Utils_Request::retrieve('cid',  'Positive', $store, TRUE);
    $type = CRM_Utils_Request::retrieve('type', 'String',   $store, TRUE);

    $pointsResult = civicrm_api('Points', 'geteffective', array(
      'version'        => 3,
      'sequential'     => 1,
      'contact_id'     => $cid,
      'points_type_id' => $type,
      'options'        => array(
        'limit'        => 0,
      ),
    ));
    $this->assign('points', $pointsResult['values']);

    parent::run();
  }
}

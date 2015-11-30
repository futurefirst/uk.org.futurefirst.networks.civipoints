<?php

require_once 'CRM/Core/Page.php';

class CRM_Points_Page_Tab extends CRM_Core_Page {
  /**
   * Assign data to be output on the contact's points tab.
   */
  function run() {
    // cid and type parameters to be passed in by the tabs hook.
    // Abort now if they are not supplied.
    // Little is done here; the actual points data is now retrieved using AJAX.
    $store = NULL;
    $this->assign('cid',  CRM_Utils_Request::retrieve('cid',  'Positive', $store, TRUE));
    $this->assign('type', CRM_Utils_Request::retrieve('type', 'String',   $store, TRUE));
    parent::run();
  }
}

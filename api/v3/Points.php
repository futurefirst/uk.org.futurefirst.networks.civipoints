<?php

/**
 * Points.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_points_create_spec(&$spec) {
  // Get current contact ID as a default
  $session = CRM_Core_Session::singleton();
  $cid     = $session->get('userID');

  $spec['id']['title']                        = ts('Unique Points ID');

  $spec['contact_id']['title']                = ts('Winning Contact');
  $spec['contact_id']['api.required']         = 1;

  $spec['grantor_contact_id']['title']        = ts('Granting Contact');
  $spec['grantor_contact_id']['api.default']  = $cid;

  $spec['points']['title']                    = ts('Points Granted/Removed');
  $spec['points']['api.required']             = 1;

  $spec['grant_date_time']['title']           = ts('Date/Time Entered');
  $spec['grant_date_time']['api.default']     = date('YmdHis');
  $spec['grant_date_time']['api.required']    = 1;

  $spec['start_date']['title']                = ts('Effective From');
  $spec['start_date']['api.default']          = date('Ymd');
  $spec['start_date']['api.required']         = 1;

  $spec['end_date']['title']                  = ts('Effective To');

  $spec['description']['title']               = ts('Description');

  $spec['entity_table']['title']              = ts('Refers to Entity Type');

  $spec['entity_id']['title']                 = ts('Refers to Entity ID');

  $spec['points_type_id']['title']            = ts('Points Type');
  $spec['points_type_id']['api.required']     = 1;
}

/**
 * Points.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_points_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Points.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_points_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Points.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_points_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

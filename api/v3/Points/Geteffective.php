<?php

/**
 * Points.Geteffective API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_points_geteffective_spec(&$spec) {
  $spec['contact_id']['title']            = ts('Winning Contact');
  $spec['contact_id']['api.required']     = 1;
  $spec['points_type_id']['title']        = ts('Points Type');
  $spec['points_type_id']['api.required'] = 1;
  $spec['date']['title']                  = ts('Effective Date');
  $spec['date']['api.default']            = date('Ymd');
  $spec['date']['api.required']           = 1;
}

/**
 * Points.Geteffective API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_points_geteffective($params) {
  $results = CRM_Points_BAO_Points::getEffective($params);
  return civicrm_api3_create_success($results, $params, 'Points', 'Geteffective');
}

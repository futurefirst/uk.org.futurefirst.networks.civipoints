<?php

/**
 * Points.getsum API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_points_getsum_spec(&$spec) {
  $spec['contact_id']['title']            = 'Winning Contact';
  $spec['contact_id']['api.required']     = 1;
  $spec['points_type_id']['title']        = 'Points Type';
  $spec['points_type_id']['api.required'] = 1;
  $spec['date']['title']                  = 'Effective Date';
  $spec['date']['api.default']            = date('Ymd');
}

/**
 * Points.Getsum API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_points_getsum($params) {
  $sum = CRM_Points_BAO_Points::getSum($params);
  return $sum;
//  if (array_key_exists('magicword', $params) && $params['magicword'] == 'sesame') {
//    $returnValues = array( // OK, return several data rows
//      12 => array('id' => 12, 'name' => 'Twelve'),
//      34 => array('id' => 34, 'name' => 'Thirty four'),
//      56 => array('id' => 56, 'name' => 'Fifty six'),
//    );
//    // ALTERNATIVE: $returnValues = array(); // OK, success
//    // ALTERNATIVE: $returnValues = array("Some value"); // OK, return a single value
//
//    // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
//    return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
//  } else {
//    throw new API_Exception(/*errorMessage*/ 'Everyone knows that the magicword is "sesame"', /*errorCode*/ 1234);
//  }
}

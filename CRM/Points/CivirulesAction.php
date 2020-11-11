<?php

use CRM_Points_ExtensionUtil as E;

/**
 * Class for CiviRule action Points
 *
 * Based on action Emailapi from org.civicoop.emailapi, that action by
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 *
 * @author David Knoll <david@futurefirst.org.uk>
 */
class CRM_Points_CivirulesAction extends CRM_CivirulesActions_Generic_Api {

  /**
   * Method to get the api entity to process in this CiviRule action
   *
   * @access protected
   * @abstract
   */
  protected function getApiEntity() {
    return 'Points';
  }

  /**
   * Method to get the api action to process in this CiviRule action
   *
   * @access protected
   * @abstract
   */
  protected function getApiAction() {
    return 'create';
  }

  /**
   * Returns an array with parameters used for processing an action
   *
   * @param array $parameters
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return array
   * @access protected
   */
  protected function alterApiParameters($parameters, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $parameters['contact_id'] = $triggerData->getContactId();

    $expiration_interval = $parameters['expiration_interval'] ?? FALSE;
    $expiration_unit = $parameters['expiration_unit'] ?? FALSE;
    if ($expiration_interval && $expiration_unit) {
      $date = new DateTime();
      $date->add(new DateInterval('P2Y'));
      $parameters['end_date'] = $date->format('Y-m-d');
    }

    return $parameters;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * $access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirules/actions/points', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the action params
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();

    $points = $params['points'];
    $type = civicrm_api('OptionValue', 'getvalue', [
      'version'           => 3,
      'value'             => $params['points_type_id'],
      'option_group_name' => 'points_type',
      'return'            => 'label']);
    $expiration_interval = $params['expiration_interval'] ?? FALSE;
    $expiration_unit = $params['expiration_unit'] ?? FALSE;

    $out = E::ts('Grant 1 %2 point to the contact', [
      'plural' => 'Grant %count %2 points to the contact',
      'count'  => $points,
      2        => $type]);

    if ($expiration_interval && $expiration_unit) {
      $out .= E::ts(', expiring after 1 %2', [
        'plural' => ', expiring after %count %2s',
        'count'  => $expiration_interval,
        2        => $expiration_unit]);
    } else {
      $out .= E::ts(', which will not expire');
    }
  
    return $out;
  }
}

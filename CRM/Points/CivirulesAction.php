<?php
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
    //this method could be overridden in subclasses to alter parameters to meet certain criteria
    $parameters['contact_id'] = $triggerData->getContactId();

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

    return ts('Grant %1 %2 points to the contact', array(
      1 => $params['points'],
      2 => civicrm_api('OptionValue', 'getvalue', array(
        'version'           => 3,
        'value'             => $params['points_type_id'],
        'option_group_name' => 'points_type',
        'return'            => 'label',
      )),
    ));
  }
}

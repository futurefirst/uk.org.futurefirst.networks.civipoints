<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Points_Form_CivirulesCondition extends CRM_CivirulesConditions_Form_ValueComparison {
  /**
   * Overridden to add the Points Type field to the value comparison condition
   */
  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->add('select', 'points_type_id', ts('Points Type'), CRM_Core_OptionGroup::values('points_type'), TRUE);
  }

  /**
   * If editing an existing condition, load existing data into the form.
   * If not, use the default points type option.
   *
   * @return array
   */
  public function setDefaultValues() {
    // Load data from existing condition
    $data = array();
    $defaultValues = parent::setDefaultValues();
    if ($this->ruleCondition->find(true)) {
      $data = unserialize($this->ruleCondition->condition_params);
    }

    if (empty($data['points_type_id'])) {
      // Get default points type
      $defaultValues['points_type_id'] = civicrm_api('OptionValue', 'getvalue', array(
        'version'           => 3,
        'option_group_name' => 'points_type',
        'is_default'        => 1,
        'return'            => 'value',
      ));
    }
    else {
      $defaultValues['points_type_id'] = $data['points_type_id'];
    }
    return $defaultValues;
  }

  /**
   * Save the value of the points type field. The rest is left to the parent form.
   */
  public function postProcess() {
    $data = unserialize($this->ruleCondition->condition_params);
    $data['points_type_id'] = $this->_submitValues['points_type_id'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }
}

<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Points_Form_CivirulesAction extends CRM_Core_Form {
  protected $ruleActionId = FALSE;
  protected $ruleAction;
  protected $action;

  public $fieldNames = array(
    'points' => 'points',
    'points_type_id' => 'points_type_id',
    'description' => 'description',
    'expiration_interval' => 'expiration_interval',
    'expiration_unit' => 'expiration_unit',
  );

  /**
   * Overridden parent method to do pre-form building processing
   *
   * @throws Exception when action or rule action not found
   * @access public
   */
  public function preProcess() {
    $this->ruleActionId = CRM_Utils_Request::retrieve('rule_action_id', 'Integer');

    $this->ruleAction = new CRM_Civirules_BAO_RuleAction();
    $this->action = new CRM_Civirules_BAO_Action();
    $this->ruleAction->id = $this->ruleActionId;
    if ($this->ruleAction->find(TRUE)) {
      $this->action->id = $this->ruleAction->action_id;
      if (!$this->action->find(TRUE)) {
        throw new Exception('CiviRules Could not find action with id '.$this->ruleAction->action_id);
      }
    }
    else {
      throw new Exception('CiviRules Could not find rule action with id '.$this->ruleActionId);
    }

    parent::preProcess();
  }

  /**
   * Method to get points types
   *
   * @return array
   * @access protected
   */
  protected static function getPointsTypes() {
    $types_result = civicrm_api('OptionValue', 'get', array(
      'version'           => 3,
      'sequential'        => 1,
      'option_group_name' => 'points_type',
      'is_active'         => 1,
    ));

    $return = array('' => ts('-- please select --'));
    if (!civicrm_error($types_result) && $types_result['count']) {
      foreach ($types_result['values'] as $type) {
        $return[$type['value']] = $type['label'];
      }
    }
    return $return;
  }

  function buildQuickForm() {
    $this->setFormTitle();
    $this->add('hidden', 'rule_action_id');

    $this->add('text',   'points',              ts('Points'),         NULL,                   TRUE);
    $this->add('select', 'points_type_id',      ts('of type'),        self::getPointsTypes(), TRUE);
    $this->add('text',   'description',         ts('Description'));
    $this->add('number', 'expiration_interval', ts('Expiring after'), ['class' => 'six', 'min' => 0]);
    
    $this->freqUnits = CRM_Core_SelectValues::getRecurringFrequencyUnits();
    unset($this->freqUnits['hour']);
    foreach ($this->freqUnits as $val => $label) {
      $freqUnitsDisplay[$val] = ts('%1(s)', [1 => $label]);
    }
    $this->add('select', 'expiration_unit', ts('time units'), $freqUnitsDisplay, TRUE);

    //$this->add('datepicker', 'expiration_date', ts('Expiration Date'), [], FALSE, ['time' => FALSE]);
    $this->add('static', 'note_end_date',  ts('Note'),        ts('If you enter an expiration time above, the points will expire the specified amount of time after their start date. Specifying a static end date on points granted by a CiviRule is not yet implemented.'));

    $this->addButtons(array(
      array('type' => 'next',   'name' => ts('Save'),   'isDefault' => TRUE),
      array('type' => 'cancel', 'name' => ts('Cancel')),
    ));
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $data = array();
    $defaultValues = array();
    $defaultValues['rule_action_id'] = $this->ruleActionId;
    if (!empty($this->ruleAction->action_params)) {
      $data = unserialize($this->ruleAction->action_params);
    }

    foreach ($this->fieldNames as $fieldName) {
      if (!empty($data[$fieldName])) {
        $defaultValues[$fieldName] = $data[$fieldName];
      }        
    }
    if (empty($data['points_type_id'])) {
      $defaultValues['points_type_id'] = civicrm_api('OptionValue', 'getvalue', array(
        'version'           => 3,
        'option_group_name' => 'points_type',
        'is_active'         => 1,
        'is_default'        => 1,
        'return'            => 'value',
      ));
    }

    return $defaultValues;
  }

  /**
   * Add validation rules to the form.
   */
  function addRules() {
    parent::addRules();
    $this->addFormRule(array('CRM_Points_Form_CivirulesAction', 'myRules'));
  }

  /**
   * Validate that the points field contains an integer.
   *
   * @param array $values Maps field names to values
   * @return boolean|array TRUE if all good, else an array mapping field names to error messages
   */
  static function myRules($values) {
    $errors = array();
    if (!CRM_Utils_Type::validate($values['points'], 'Int', FALSE)) {
      $errors['points'] = ts('Please enter a nonzero integer.');
    }
    return empty($errors) ? TRUE : $errors;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data = array_intersect_key($this->_submitValues, $this->fieldNames);

    $ruleAction = new CRM_Civirules_BAO_RuleAction();
    $ruleAction->id = $this->ruleActionId;
    $ruleAction->action_params = serialize($data);
    $ruleAction->save();

    $session = CRM_Core_Session::singleton();
    $session->setStatus('Action '.$this->action->label.' parameters updated to CiviRule '.CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->ruleAction->rule_id),
      'Action parameters updated', 'success');

    $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id='.$this->ruleAction->rule_id, TRUE);
    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * Method to set the form title
   *
   * @access protected
   */
  protected function setFormTitle() {
    $title = 'CiviRules Edit Action parameters';
    $this->assign('ruleActionHeader', 'Edit action '.$this->action->label.' of CiviRule '.CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->ruleAction->rule_id));
    CRM_Utils_System::setTitle($title);
  }
}

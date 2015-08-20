<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Points_Form_Grant extends CRM_Core_Form {
  var $_contact_id;
  var $_contact_name;
  var $_contact_url;
  var $_grantor_contact_id;
  var $_grantor_contact_name;
  var $_grantor_contact_url;

  /**
   * This function is called prior to building and submitting the form
   */
  function preProcess() {
    // Winning contact (passed in URL, can't be changed here)
    $contact_id = CRM_Utils_Request::retrieve('cid', 'Positive');
    if (empty($contact_id)) {
      $contact_id = $this->getSubmitValue('contact_id');
    }
    if (!empty($contact_id)) {
      $this->_contact_id = $contact_id;
    }
    if (!empty($this->_contact_id)) {
      $this->_contact_name = civicrm_api('Contact', 'getvalue', array('version' => 3, 'id' => $this->_contact_id, 'return' => 'display_name'));
      $this->_contact_url  = CRM_Utils_System::url('civicrm/contact/view', array('reset' => 1, 'cid' => $this->_contact_id), FALSE, NULL, FALSE);
    }

    // Granting contact (current user, can't be changed here)
    $session = CRM_Core_Session::singleton();
    $grantor_contact_id = $session->get('userID');
    if (!empty($grantor_contact_id)) {
      $this->_grantor_contact_id = $grantor_contact_id;
    }
    if (!empty($this->_grantor_contact_id)) {
      $this->_grantor_contact_name = civicrm_api('Contact', 'getvalue', array('version' => 3, 'id' => $this->_grantor_contact_id, 'return' => 'display_name'));
      $this->_grantor_contact_url  = CRM_Utils_System::url('civicrm/contact/view', array('reset' => 1, 'cid' => $this->_grantor_contact_id), FALSE, NULL, FALSE);
    }

    parent::preProcess();
  }

  function setDefaultValues() {
    return array(
      'start_date' => array(
        'd' => date('d'),
        'M' => date('m'),
        'Y' => date('Y'),
      ),
    );
  }

  /**
   * Will be called prior to outputting html (and prior to buildForm hook)
   */
  function buildQuickForm() {
    // Winning contact (passed in URL, can't be changed here)
    $this->add('link',   'contact_link', ts('Contact Name'), $this->_contact_url, FALSE, $this->_contact_name);
    $this->add('hidden', 'contact_id',   $this->_contact_id);

    // Granting contact (current user, can't be changed here)
    $this->add('link',   'grantor_contact_link', ts('Granting Contact'), $this->_grantor_contact_url, FALSE, $this->_grantor_contact_name);
    $this->add('hidden', 'grantor_contact_id',   $this->_grantor_contact_id);

    // Points type and number of points (required)
    $this->add('select', 'points_type_id', ts('Points Type'), CRM_Core_OptionGroup::values('points_type'), TRUE);
    $this->add('text',   'points',         ts('Points'),      array('size' => 5, 'maxlength' => 5),        TRUE);

    // Effective date (required), expiry date (optional)
    $this->add('date', 'start_date', ts('Effective From'), array('minYear' => date('Y') - 5, 'maxYear' => date('Y') + 5, 'addEmptyOption' => FALSE), TRUE);
    $this->add('date', 'end_date',   ts('Effective To'),   array('minYear' => date('Y') - 5, 'maxYear' => date('Y') + 5, 'addEmptyOption' => TRUE), FALSE);

    // Description (with max length)
    $this->add('textarea', 'description', ts('Description'), array('maxlength' => 255));

    // Submit/Cancel buttons
    $this->addButtons(array(
      array(
        'type'      => 'submit',
        'name'      => ts('Grant'),
        'isDefault' => TRUE,
      ),
      array(
        'type'      => 'cancel',
        'name'      => ts('Cancel'),
        'isDefault' => FALSE,
      ),
    ));

    // Export form elements
    CRM_Utils_System::setTitle(ts('Grant Points'));
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  /**
   * If your form requires special validation, add one or more callbacks here
   */
  function addRules() {
    $this->addFormRule(array('CRM_Points_Form_Grant', 'myRules'));
    parent::addRules();
  }

  /**
   * Here's our custom validation callback
   */
  static function myRules($values) {
    $errors = array();
    if (!CRM_Utils_Type::validate($values['contact_id'], 'Positive', FALSE)) {
      $errors['contact_id'] = ts('Contact is required.');
    }
    if (!CRM_Utils_Type::validate($values['grantor_contact_id'], 'Positive', FALSE)) {
      $errors['grantor_contact_id'] = ts('Granting Contact is required.');
    }
    if (!CRM_Utils_Type::validate($values['points'], 'Int', FALSE)) {
      $errors['points'] = ts('Points must be a whole number.');
    }

    if (self::validateFormDate($values['start_date'], TRUE) === FALSE) {
      $errors['start_date'] = ts('Effective From, if it is filled, must be completely filled.');
    }
    if (self::validateFormDate($values['end_date'], FALSE) === FALSE) {
      $errors['end_date'] = ts('Effective To, if it is filled, must be completely filled.');
    }

    if (strlen($values['description']) > 255) {
      $errors['description'] = ts('Description can have a maximum of 255 characters.');
    }
    return empty($errors) ? TRUE : $errors;
  }

  /**
   * Validate and process a date that comes back from the form
   *
   * @param array $formDate Contains keys d, M and Y
   * @param boolean $required Is it required ie no component can be empty
   * @return string|boolean|null
   *   A string in the form YYYYMMDD if a good date,
   *   FALSE if a bad/incomplete one,
   *   NULL if not provided and not required.
   */
  static function validateFormDate($formDate, $required) {
    $d = CRM_Utils_Type::validate($formDate['d'], 'Positive', FALSE);
    $M = CRM_Utils_Type::validate($formDate['M'], 'Positive', FALSE);
    $Y = CRM_Utils_Type::validate($formDate['Y'], 'Positive', FALSE);

    // Don't want partially filled dates
    if (
      (empty($d) || empty($M) || empty($Y)) &&
      !(empty($d) && empty($M) && empty($Y))
    ) {
      return FALSE;
    }

    // If date is required
    if (empty($d) || empty($M) || empty($Y)) {
      return $required ? FALSE : NULL;
    }

    // Assemble it and validate as a date
    $ymd = sprintf('%04d%02d%02d', $Y, $M, $d);
    if (strlen($ymd) == 8 && CRM_Utils_Type::validate($ymd, 'Date', FALSE)) {
      return $ymd;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Called after form is successfully submitted
   */
  function postProcess() {
    $values = $this->exportValues();
    echo "<div><pre>";
    print_r($values);
    echo "</pre></div>\n";
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}

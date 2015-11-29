<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Points_Form_Grant extends CRM_Core_Form {
  // Maximum length of description field
  const DESCRIPTION_MAX = 255;
  // Years either side of present for start/end date selection
  const YEARS_RANGE = 20;

  /**
   * @var int Cid of winning contact
   */
  var $_contact_id;
  /**
   * @var string Display name of winning contact
   */
  var $_contact_name;
  /**
   * @var string Contact view page URL of winning contact
   */
  var $_contact_url;
  /**
   * @var int Cid of granting contact
   */
  var $_grantor_contact_id;
  /**
   * @var string Display name of granting contact
   */
  var $_grantor_contact_name;
  /**
   * @var string Contact view page URL of granting contact
   */
  var $_grantor_contact_url;
  /**
   * @var array Cache lookup of points types
   */
  var $_points_types;
  /**
   * @var int Existing Points entity ID (if editing rather than creating)
   */
  var $_existing_id;

  /**
   * This function is called prior to building and submitting the form
   */
  function preProcess() {
    // Editing existing Points record?
    $existing_id = CRM_Utils_Request::retrieve('pid', 'Positive');
    if (!empty($existing_id)) {
      $this->_existing_id = $existing_id;
    }

    // Winning contact (passed in URL, can't be changed here)
    $contact_id = CRM_Utils_Request::retrieve('cid', 'Positive');
    if (empty($contact_id)) {
      $contact_id = $this->getSubmitValue('contact_id');
    }
    if (!empty($contact_id)) {
      $this->_contact_id = $contact_id;
    }

    // Granting contact (current user, can't be changed here)
    $session = CRM_Core_Session::singleton();
    $grantor_contact_id = $session->get('userID');
    if (!empty($grantor_contact_id)) {
      $this->_grantor_contact_id = $grantor_contact_id;
    }

    $this->_points_types = CRM_Core_OptionGroup::values('points_type');
    parent::preProcess();
  }

  /**
   * Will be called prior to outputting html (and prior to buildForm hook)
   */
  function buildQuickForm() {
    // Existing Points entity ID (if editing rather than creating)
    $this->add('hidden', 'id', $this->_existing_id);

    // Winning contact (passed in URL, can't be changed here)
    $this->add('link',   'contact_link', ts('Contact Name'), $this->_contact_url, FALSE, $this->_contact_name);
    $this->add('hidden', 'contact_id',   $this->_contact_id);

    // Granting contact (current user, can't be changed here)
    $this->add('link',   'grantor_contact_link', ts('Granting Contact'), $this->_grantor_contact_url, FALSE, $this->_grantor_contact_name);
    $this->add('hidden', 'grantor_contact_id',   $this->_grantor_contact_id);

    // Points type and number of points (required)
    $this->add('select', 'points_type_id', ts('Points Type'), $this->_points_types,                 TRUE);
    $this->add('text',   'points',         ts('Points'),      array('size' => 5, 'maxlength' => 5), TRUE);

    // Effective date (required), expiry date (optional)
    $this->add('date', 'start_date', ts('Effective From'), array('minYear' => date('Y') - self::YEARS_RANGE, 'maxYear' => date('Y') + self::YEARS_RANGE, 'addEmptyOption' => FALSE), TRUE);
    $this->add('date', 'end_date',   ts('Effective To'),   array('minYear' => date('Y') - self::YEARS_RANGE, 'maxYear' => date('Y') + self::YEARS_RANGE, 'addEmptyOption' => TRUE), FALSE);

    // Description (with max length)
    $this->add('textarea', 'description', ts('Description'), array('maxlength' => self::DESCRIPTION_MAX));

    // Submit/Cancel buttons
    $this->addButtons(array(
      array(
        'type'      => 'upload',
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

  /**
   * Returns default values of form elements.
   * If creating a new record, default start date to current date.
   * If editing a record, load defaults from it.
   * Look up and set the displayed name and contact view page URL for the winning and granting contacts.
   *
   * @return array
   */
  function setDefaultValues() {
    // Defaults for new records
    $type = CRM_Utils_Request::retrieve('type', 'String');
    $defaults = array(
      'start_date' => array(
        'd' => date('d'),
        'M' => date('m'),
        'Y' => date('Y'),
      ),
      'points_type_id' => $type ? $type : civicrm_api('OptionValue', 'getvalue', array(
        'version'           => 3,
        'option_group_name' => 'points_type',
        'is_default'        => 1,
        'return'            => 'value',
      )),
    );

    // If editing...
    if (!empty($this->_existing_id)) {
      $pointsExist = civicrm_api('Points', 'getsingle', array('version' => 3, 'id' => $this->_existing_id));
      if (civicrm_error($pointsExist)) {
        CRM_Core_Error::fatal($pointsExist['error_message']);
      }

      $this->_contact_id         = $pointsExist['contact_id'];
      $this->_grantor_contact_id = $pointsExist['grantor_contact_id'];
      $defaults = $pointsExist;
    }

    // Look up name and URL for winning contact
    if (!empty($this->_contact_id)) {
      $this->_contact_name = civicrm_api('Contact', 'getvalue', array('version' => 3, 'id' => $this->_contact_id, 'return' => 'display_name'));
      $this->_contact_url  = CRM_Utils_System::url('civicrm/contact/view', array('reset' => 1, 'cid' => $this->_contact_id), FALSE, NULL, FALSE);
      $this->getElement('contact_link')->setAttribute('href', $this->_contact_url);
      $this->getElement('contact_link')->setText($this->_contact_name);
    }

    // Look up name and URL for granting contact
    if (!empty($this->_grantor_contact_id)) {
      $this->_grantor_contact_name = civicrm_api('Contact', 'getvalue', array('version' => 3, 'id' => $this->_grantor_contact_id, 'return' => 'display_name'));
      $this->_grantor_contact_url  = CRM_Utils_System::url('civicrm/contact/view', array('reset' => 1, 'cid' => $this->_grantor_contact_id), FALSE, NULL, FALSE);
      $this->getElement('grantor_contact_link')->setAttribute('href', $this->_grantor_contact_url);
      $this->getElement('grantor_contact_link')->setText($this->_grantor_contact_name);
    }

    return $defaults;
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

    if (strlen($values['description']) > self::DESCRIPTION_MAX) {
      $errors['description'] = ts('Description can have a maximum of ' . self::DESCRIPTION_MAX . ' characters.');
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
    // Filter out stuff that isn't a parameter to the Points creation
    $values = $this->exportValues();
    foreach ($values as $key => &$value) {
      switch ($key) {
        case 'id':
        case 'contact_id':
        case 'grantor_contact_id':
        case 'points_type_id':
        case 'points':
        case 'description':
          if (empty($value)) {
            unset($values[$key]);
          }
          break;

        case 'start_date':
          $value = self::validateFormDate($value, TRUE);
          if (empty($value)) {
            unset($values[$key]);
          }
          break;
        case 'end_date':
          $value = self::validateFormDate($value, FALSE);
          if (empty($value)) {
            unset($values[$key]);
          }
          break;

        default:
          unset($values[$key]);
          break;
      }
    }

    // Create/update the Points entity.
    $values['version'] = 3;
    $createResult = civicrm_api('Points', 'create', $values);

    // Show and log an error message, if that failed.
    if (civicrm_error($createResult)) {
      CRM_Core_Session::setStatus(
        $createResult['error_message'],
        empty($values['id']) ? ts('Error granting points') : ts('Error editing points'),
        'error'
      );
      CRM_Core_Error::debug_log_message(
        'CiviPoints- ' . ts('Error granting points') . ":\n" . print_r($values, TRUE) . print_r($createResult, TRUE)
      );
      return;
    }

    // Otherwise show a success message.
    $tsParams = array(
      1 => $values['points'],
      2 => $this->_points_types[$values['points_type_id']],
      3 => $this->_contact_name,
      4 => CRM_Utils_Date::customFormat($values['start_date']),
    );
    if (empty($values['end_date'])) {
      CRM_Core_Session::setStatus(
        ts("%1 '%2' points granted to %3 from %4 inclusive. These points will not expire.", $tsParams),
        empty($values['id']) ? ts('Points Granted') : ts('Points Edited'),
        'success'
      );
    }
    else {
      $tsParams[5] = CRM_Utils_Date::customFormat($values['end_date']);
      CRM_Core_Session::setStatus(
        ts("%1 '%2' points granted to %3 from %4 to %5 inclusive.", $tsParams),
        empty($values['id']) ? ts('Points Granted') : ts('Points Edited'),
        'success'
      );
    }

    parent::postProcess();
  }
}

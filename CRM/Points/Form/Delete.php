<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Points_Form_Delete extends CRM_Core_Form {
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
    $contact_id = $this->getSubmitValue('contact_id');
    if (!empty($contact_id)) {
      $this->_contact_id = $contact_id;
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
    $this->add('static', 'points_type_id', ts('Points Type'));
    $this->add('static', 'points',         ts('Points'));

    // Effective date (required), expiry date (optional)
    $this->add('static', 'grant_date_time', ts('Date/Time Granted'));
    $this->add('static', 'start_date',      ts('Effective From'));
    $this->add('static', 'end_date',        ts('Effective To'));

    // Description (with max length)
    $this->add('static', 'description', ts('Description'));

    // Submit/Cancel buttons
    $this->addButtons(array(
      array(
        'type'      => 'submit',
        'name'      => ts('Delete'),
        'isDefault' => TRUE,
      ),
      array(
        'type'      => 'cancel',
        'name'      => ts('Cancel'),
        'isDefault' => FALSE,
      ),
    ));

    // Export form elements
    CRM_Utils_System::setTitle(ts('Delete Points'));
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
    $defaults = array();

    // If editing...
    if (!empty($this->_existing_id)) {
      $pointsExist = civicrm_api('Points', 'getsingle', array('version' => 3, 'id' => $this->_existing_id));
      if (civicrm_error($pointsExist)) {
        CRM_Core_Error::fatal($pointsExist['error_message']);
      }

      $this->_contact_id         = $pointsExist['contact_id'];
      $this->_grantor_contact_id = $pointsExist['grantor_contact_id'];
      $defaults = $pointsExist;

      // Render existing values for static display
      $defaults['points_type_id']  = $this->_points_types[$pointsExist['points_type_id']];
      $defaults['grant_date_time'] = CRM_Utils_Date::customFormat($pointsExist['grant_date_time']);
      $defaults['start_date']      = CRM_Utils_Date::customFormat($pointsExist['start_date']);
      if (!empty($pointsExist['end_date'])) {
        $defaults['end_date']      = CRM_Utils_Date::customFormat($pointsExist['end_date']);
      }
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
   * Called after form is successfully submitted
   */
  function postProcess() {
    // Filter out stuff that isn't a parameter to the Points creation
    $values = $this->exportValues();

    // Delete the Points entity.
    $deleteResult = civicrm_api('Points', 'delete', array(
      'version' => 3,
      'id'      => $values['id'],
    ));

    // Show and log an error message, if that failed.
    if (civicrm_error($deleteResult)) {
      CRM_Core_Session::setStatus(
        $deleteResult['error_message'],
        ts('Error deleting points'),
        'error'
      );
      CRM_Core_Error::debug_log_message(
        'CiviPoints- ' . ts('Error deleting Points entity %1 from contact %2', array(
          1 => $this->_existing_id,
          2 => $this->_contact_id,
        )) . ":\n" . print_r($deleteResult, TRUE)
      );
      return;
    }

    // Otherwise show a success message.
    CRM_Core_Session::setStatus(
      ts("Points deleted from %1. These points will no longer be included in the contact's history.", array(
        1 => $this->_contact_name,
      )),
      ts('Points Deleted'),
      'success'
    );

    // On success, redirect to the winning contact's page.
    parent::postProcess();
    CRM_Utils_System::redirect($this->_contact_url);
  }
}

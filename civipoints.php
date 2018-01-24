<?php

require_once 'civipoints.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civipoints_civicrm_config(&$config) {
  _civipoints_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civipoints_civicrm_xmlMenu(&$files) {
  _civipoints_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civipoints_civicrm_install() {
  _civipoints_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civipoints_civicrm_uninstall() {
  _civipoints_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civipoints_civicrm_enable() {
  _civipoints_civix_civicrm_enable();

  // Make sure the log table is created if required (CRM-15078)
  $schema = new CRM_Logging_Schema();
  $schema->fixSchemaDifferences();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civipoints_civicrm_disable() {
  _civipoints_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civipoints_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civipoints_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civipoints_civicrm_managed(&$entities) {
  _civipoints_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civipoints_civicrm_caseTypes(&$caseTypes) {
  _civipoints_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civipoints_civicrm_angularModules(&$angularModules) {
_civipoints_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civipoints_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civipoints_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function civipoints_civicrm_preProcess($formName, &$form) {

}

*/

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Loads any custom entity type definitions within the extension.
 * Doesn't seem to be listed on the wiki at time of writing.
 * This implementation is adapted from Civix's implementation of hook_civicrm_managed.
 */
function civipoints_civicrm_entityTypes(&$entityTypes) {
  // Find all custom entity type definition files under the extension's directory.
  $mgdFiles = _civipoints_civix_find_files(__DIR__, '*.entityType.php');
  foreach ($mgdFiles as $file) {
    // Add any definitions found in that file to the list.
    $es = include $file;
    foreach ($es as $e) {
      if (empty($e['module'])) {
        $e['module'] = 'uk.org.futurefirst.networks.civipoints';
      }
      $entityTypes[] = $e;
    }
  }
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * Require similar permissions to do stuff with Points as to do stuff with
 * certain Contact-related entities like Note, EntityTag, Website, Email, Phone etc.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterAPIPermissions
 */
function civipoints_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['points'] = array(
    'get'          => array('access CiviCRM', 'view all contacts'),
    'getsum'       => array('access CiviCRM', 'view all contacts'),
    'geteffective' => array('access CiviCRM', 'view all contacts'),
    'delete'       => array('access CiviCRM', 'delete contacts'),
    'default'      => array('access CiviCRM', 'edit all contacts'),
  );
}

/**
 * Implements hook_civicrm_tabs().
 *
 * For contacts who have or have had points, display a tab on the contact view page.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tabs
 */
function civipoints_civicrm_tabs(&$tabs, $contactID) {
  // One tab for each type of points
  $pointsTypes = CRM_Core_OptionGroup::values('points_type');

  foreach ($pointsTypes as $pointsTypeId => $pointsTypeLabel) {
    // Has this contact ever had any points of this type?
    // If not, don't bother showing the tab.
    // Note, this costs a lookup per active points type per contact page view, even if no results.
    $countRecs = civicrm_api('Points', 'getcount', array(
      'version'        => 3,
      'contact_id'     => $contactID,
      'points_type_id' => $pointsTypeId,
    ));
    if (!$countRecs) {
      continue;
    }

    // Display the current points total in the tab heading
    $sum = civicrm_api('Points', 'getsum', array(
      'version'        => 3,
      'contact_id'     => $contactID,
      'points_type_id' => $pointsTypeId,
    ));
    if ($sum === NULL) {
      $sum = 0;
    }

    // Page URL for a breakdown of points granted to that contact
    $url = CRM_Utils_System::url('civicrm/points/tab', array(
      'snippet' => 1,
      'cid'     => $contactID,
      'type'    => $pointsTypeId,
    ));

    // Add a tab for this points type
    $tabs[] = array(
      'id'     => 'civipoints_' . $pointsTypeId,
      'url'    => $url,
      'title'  => ts('Points (%1)', array(1 => $pointsTypeLabel)),
      'weight' => _civipoints_maxweight($tabs) + 5,
      'count'  => $sum,
    );
  }
}

/**
 * Implements hook_civicrm_summaryActions().
 *
 * For contacts who may be granted points, add the option to the menu
 * that appears when the Actions button is clicked on the contact view summary.
 *
 * @see CRM_Contact_BAO_Contact::contextMenu
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tabs
 */
function civipoints_civicrm_summaryActions(&$actions, $contactID) {
  // Idea for the future: bail out here if we're not meant to grant points
  // to this contact or this contact type, and it won't show the action
  // (won't stop you typing the civicrm/points/grant URL manually)

  // Add an entry to the end of the actions section
  $actions['civipoints'] = array(
    'title'       => ts('Grant Points'),
    'weight'      => _civipoints_maxweight($actions) + 5,
    'ref'         => 'grant-points',
    'key'         => 'civipoints',
    // cid=xxxx gets added to the action URL automatically
    'href'        => CRM_Utils_System::url('civicrm/points/grant', array(
      'reset'   => 1,
      'action'  => 'add',
      'context' => 'points',
    )),
    'permissions' => array('edit all contacts'),
  );
}

/**
 * Find the highest current weight of items in a menu.
 *
 * @param array $menu
 * @return int
 */
function _civipoints_maxweight($menu) {
  $maxweight = 0;
  foreach ($menu as $entry) {
    $weight = CRM_Utils_Array::value('weight', $entry, 0);
    if ($weight > $maxweight) {
      $maxweight = $weight;
    }
  }
  return $maxweight;
}

/**
 * Check whether CiviRules is installed.
 *
 * From org.civicoop.emailapi, by Jaap Jaansma of CiviCooP
 *
 * @return boolean
 */
function _civipoints_is_civirules_installed() {
  $installed = FALSE;
  try {
    $extensions = civicrm_api3('Extension', 'get');
    foreach ($extensions['values'] as $ext) {
      if ($ext['key'] == 'org.civicoop.civirules' && $ext['status'] == 'installed') {
        $installed = TRUE;
      }
    }
    if ($installed) return $installed;

    // CiviRules doesn't appear in the list of extensions above, so check directly.
    $select = CRM_Utils_SQL_Select::from('civicrm_extension')
      ->where("full_name = '!ext'", array('!ext' =>'org.civicoop.civirules'))
      ->where('is_active = !installed', array('!installed' => TRUE));
    return (bool) count($select->execute()->fetchAll());
  }
  catch (Exception $e) {
    return FALSE;
  }
  return FALSE;
}

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

  // Make sure the log table is created if required
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
 * Doesn't seem to be listed on the wiki at time of writing.
 * This implementation is adapted from Civix's implementation of hook_civicrm_managed.
 */
function civipoints_civicrm_entityTypes(&$entityTypes) {
  $mgdFiles = _civipoints_civix_find_files(__DIR__, '*.entityType.php');
  foreach ($mgdFiles as $file) {
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
 * Implements hook_civicrm_post().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
//function civipoints_civicrm_post($op, $objectName, $objectId, &$objectRef) {
//  if ($objectName == 'Points') {
//    watchdog('CiviPoints', "Post: :op :name :id\n:ref", array(
//      ':op'   => $op,
//      ':name' => $objectName,
//      ':id'   => $objectId,
//      ':ref'  => print_r($objectRef, TRUE),
//    ), WATCHDOG_DEBUG);
//  }
//}

/**
 * Implements hook_civicrm_points_sum().
 *
 * This is a custom hook for this extension.
 */
//function civipoints_civicrm_points_sum(&$sum, &$dao) {
//  watchdog('CiviPoints', "Sum: :sum\n:dao", array(
//    ':sum' => $sum,
//    ':dao' => print_r($dao, TRUE),
//  ), WATCHDOG_DEBUG);
//}

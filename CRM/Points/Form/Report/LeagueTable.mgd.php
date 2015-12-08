<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array(
  0 => array(
    'name'   => 'CRM_Points_Form_Report_LeagueTable',
    'entity' => 'ReportTemplate',
    'params' => array(
      'version'     => 3,
      'label'       => 'League Table',
      'description' => 'CiviPoints: Shows a list of contacts with their points, to allow league tables and rankings to be constructed.',
      'class_name'  => 'CRM_Points_Form_Report_LeagueTable',
      'report_url'  => 'points/leaguetable',
      'component'   => '',
    ),
  ),
);

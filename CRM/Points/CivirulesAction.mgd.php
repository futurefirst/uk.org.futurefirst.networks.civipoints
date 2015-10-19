<?php

if (_civipoints_is_civirules_installed()) {
  return array(
    0 => array(
      'name'   => 'Civirules:Action.Points',
      'entity' => 'CiviRuleAction',
      'params' => array(
        'version'    => 3,
        'name'       => 'civipoints_grant',
        'label'      => 'Grant points',
        'class_name' => 'CRM_Points_CivirulesAction',
        'is_active'  => 1,
      ),
    ),
  );
}

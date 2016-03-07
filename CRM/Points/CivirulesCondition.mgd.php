<?php

if (_civipoints_is_civirules_installed()) {
  return array(
    0 => array(
      'name'   => 'Civirules:Condition.Points',
      'entity' => 'CiviRuleCondition',
      'params' => array(
        'version'    => 3,
        'name'       => 'civipoints_getsum',
        'label'      => 'Contact has points',
        'class_name' => 'CRM_Points_CivirulesCondition',
        'is_active'  => 1,
      ),
    ),
  );
}

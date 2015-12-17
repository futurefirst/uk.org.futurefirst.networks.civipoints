<?php

/**
 * Shows a list of contacts with their points, to allow league tables and rankings to be constructed.
 */
class CRM_Points_Form_Report_LeagueTable extends CRM_Report_Form {
  function __construct() {
    $this->contactSubtype   = CRM_Contact_BAO_ContactType::subTypePairs(NULL, FALSE, NULL);
    $this->membershipType   = CRM_Member_PseudoConstant::membershipType();
    $this->membershipStatus = CRM_Member_PseudoConstant::membershipStatus(NULL, NULL, 'label');
    $this->pointsType       = CRM_Core_OptionGroup::values('points_type');
    $this->_columns         = array();

    // Generate options for each points type
    foreach ($this->pointsType as $ptid => $ptlabel) {
      $ptid = CRM_Core_DAO::escapeString($ptid);
      $this->_columns['civicrm_points_' . $ptid] = array();
      $this->_columns['civicrm_points_' . $ptid]['fields']    =
      $this->_columns['civicrm_points_' . $ptid]['filters']   =
      $this->_columns['civicrm_points_' . $ptid]['order_bys'] =
      array(
        'points_' . $ptid => array(
          'title' => ts('Points (%1)', array(1 => $ptlabel)),
          'type'  => CRM_Utils_Type::T_INT,
        ),
      );
      $this->_columns['civicrm_points_' . $ptid]['grouping'] = 'points-fields';
    }

    $this->_columns += array(
      'civicrm_contact' => array(
        'dao'    => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'id' => array(
            'no_display' => TRUE,
            'required'   => TRUE,
          ),
          'sort_name' => array(
            'title'   => ts('Contact Name'),
            'default' => TRUE,
          ),
          'contact_type'     => array(),
          'contact_sub_type' => array(),
        ),
        'filters' => array(
          'sort_name' => array(
            'title' => ts('Contact Name'),
          ),
          'contact_type' => array(
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options'      => CRM_Contact_BAO_ContactType::basicTypePairs(),
          ),
          'contact_sub_type' => array(
            'operatorType' => CRM_Report_Form::OP_MULTISELECT_SEPARATOR,
            'options'      => $this->contactSubtype,
          ),
        ),
        'group_bys' => array(
          'id' => array(
            'title'   => ts('Contact ID'),
            'default' => TRUE,
          ),
        ),
        'order_bys' => array(
          'id' => array(
            'title' => ts('Contact ID'),
          ),
          'sort_name' => array(
            'title' => ts('Contact Name'),
          ),
        ),
        'grouping' => 'contact-fields',
      ),

      'civicrm_address' => array(
        'dao'    => 'CRM_Core_DAO_Address',
        'fields' => array(
          'postal_code' => array(),
        ),
        'filters' => array(
          'postal_code' => array(),
        ),
        'grouping' => 'contact-fields',
      ),

      'civicrm_email' => array(
        'dao'    => 'CRM_Core_DAO_Email',
        'fields' => array(
          'email' => array(),
        ),
        'grouping' => 'contact-fields',
      ),

      'civicrm_membership' => array(
        'dao'    => 'CRM_Member_DAO_Membership',
        'fields' => array(
          'membership_type_id' => array(
            'title' => ts('Membership Type'),
          ),
          'status_id' => array(
            'title' => ts('Membership Status'),
          ),
          'join_date' => array(
            'type'  => CRM_Utils_Type::T_DATE,
          ),
          'start_date' => array(
            'title' => ts('Start Date'),
            'type'  => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array(
            'title' => ts('End Date'),
            'type'  => CRM_Utils_Type::T_DATE,
          ),
        ),
        'filters' => array(
          'membership_type_id' => array(
            'title'        => ts('Membership Type'),
            'type'         => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options'      => $this->membershipType,
          ),
          'status_id' => array(
            'title'        => ts('Membership Status'),
            'type'         => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options'      => $this->membershipStatus,
          ),
          'join_date' => array(
            'type'  => CRM_Utils_Type::T_DATE,
          ),
          'start_date' => array(
            'title' => ts('Start Date'),
            'type'  => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array(
            'title' => ts('End Date'),
            'type'  => CRM_Utils_Type::T_DATE,
          ),
        ),
        'grouping' => 'member-fields',
      ),
    );

    $this->_groupFilter        = TRUE;
    $this->_tagFilter          = TRUE;
    $this->_customGroupExtends = CRM_Contact_BAO_ContactType::basicTypes();
    parent::__construct();
  }

  function from() {
    // Basic contact details, membership optional
    $this->_from = "
             FROM `civicrm_contact` AS `{$this->_aliases['civicrm_contact']}`
                  {$this->_aclFrom}

        LEFT JOIN `civicrm_membership` AS `{$this->_aliases['civicrm_membership']}`
               ON `{$this->_aliases['civicrm_membership']}`.`contact_id`  = `{$this->_aliases['civicrm_contact']}`.`id`
              AND `{$this->_aliases['civicrm_membership']}`.`is_test`    IS NOT TRUE
    ";

    // Address, if needed
    if ($this->_addressField) {
      $this->_from .= "
        LEFT JOIN `civicrm_address` AS `{$this->_aliases['civicrm_address']}`
               ON `{$this->_aliases['civicrm_address']}`.`contact_id`  = `{$this->_aliases['civicrm_contact']}`.`id`
              AND `{$this->_aliases['civicrm_address']}`.`is_primary` IS TRUE
      ";
    }

    // E-mail, if needed
    if ($this->_emailField) {
      $this->_from .= "
        LEFT JOIN `civicrm_email` AS `{$this->_aliases['civicrm_email']}`
               ON `{$this->_aliases['civicrm_email']}`.`contact_id`  = `{$this->_aliases['civicrm_contact']}`.`id`
              AND `{$this->_aliases['civicrm_email']}`.`is_primary` IS TRUE
      ";
    }

    // Generate joins for each points type, inner query based on CRM_Points_BAO_Points::getSum
    $date = CRM_Utils_Date::currentDBDate();
    $date = CRM_Core_DAO::escapeString($date);
    foreach ($this->pointsType as $ptid => $ptlabel) {
      $ptid = CRM_Core_DAO::escapeString($ptid);
      $this->_from .= "
        LEFT JOIN (
           SELECT   `contact_id`,
                    SUM(`points`)    AS `points_{$ptid}`
             FROM   `civicrm_points`
            WHERE   `points_type_id`  = '{$ptid}'
              AND   `start_date`     <= '{$date}'
              AND   (
                      `end_date`     >= '{$date}'
               OR     `end_date`     IS NULL
                    )
         GROUP BY   `contact_id`
                  ) AS `{$this->_aliases['civicrm_points_' . $ptid]}`
               ON `{$this->_aliases['civicrm_points_' . $ptid]}`.`contact_id` = `{$this->_aliases['civicrm_contact']}`.`id`
      ";
    }
  }

  function where() {
    // Always exclude deleted (trashed) contacts
    $this->_whereClauses[] = "(`{$this->_aliases['civicrm_contact']}`.`is_deleted` IS NOT TRUE)";
    parent::where();
  }

  function alterDisplay(&$rows) {
    foreach ($rows as &$row) {
      // Format the contact subtypes field for display
      if (CRM_Utils_Array::value('civicrm_contact_contact_sub_type', $row)) {
        $row['civicrm_contact_contact_sub_type'] = self::formatSubtypes($row['civicrm_contact_contact_sub_type']);
      }

      // Format the membership type and status fields for display
      if (CRM_Utils_Array::value('civicrm_membership_membership_type_id', $row)) {
        $row['civicrm_membership_membership_type_id'] = $this->membershipType[$row['civicrm_membership_membership_type_id']];
      }
      if (CRM_Utils_Array::value('civicrm_membership_status_id', $row)) {
        $row['civicrm_membership_status_id'] = $this->membershipStatus[$row['civicrm_membership_status_id']];
      }

      // Make contact name a link to view the contact
      if (
        CRM_Utils_Array::value('civicrm_contact_sort_name', $row) &&
        CRM_Utils_Array::value('civicrm_contact_id',        $row)
      ) {
        $url = CRM_Utils_System::url('civicrm/contact/view', array(
          'reset' => 1,
          'cid'   => $row['civicrm_contact_id'],
        ));
        $row['civicrm_contact_sort_name_link']  = $url;
        $row['civicrm_contact_sort_name_hover'] = ts('View Contact');
      }
    }
  }

  /**
   * Format a separated string of contact subtype names (as stored in the database)
   * as a sorted, comma-separated string of contact subtype labels (for human reading)
   *
   * @param string $contact_sub_type
   * @return string
   */
  public static function formatSubtypes($contact_sub_type) {
    if (empty($contact_sub_type)) {
      return NULL;
    }
    $subtypes_map = CRM_Contact_BAO_ContactType::contactTypePairs();
    $subtypes     = CRM_Utils_Array::explodePadded($contact_sub_type);

    foreach ($subtypes as $key => $subtype) {
      if (empty($subtype)) {
        unset($subtypes[$key]);
      }
      else {
        $subtypes[$key] = $subtypes_map[$subtype];
      }
    }

    natcasesort($subtypes);
    return implode(', ', $subtypes);
  }

  /**
   * Dump the query for debugging
   *
   * @param boolean $applyLimit Limit rows returned for pagination
   * @return string
   */
//  function buildQuery($applyLimit = TRUE) {
//    $sql = parent::buildQuery($applyLimit);
//    echo "<div><pre>$sql</pre></div>\n";
//    return $sql;
//  }
}

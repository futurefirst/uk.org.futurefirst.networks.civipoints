<?php

/**
 * Search for contacts with points or ordered by points
 */
class CRM_Points_Form_Search_Points extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    $this->_columns = array(
      ts('Points')             => 'points',
      ts('Contact Name')       => 'sort_name',
      ts('Contact Type')       => 'contact_type',
      ts('Contact Subtype(s)') => 'contact_sub_type',
      ts('Membership Type')    => 'membership_type',
      ts('Membership Status')  => 'membership_status',
    );
    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(ts('Points Search'));

    $form->addElement('select', 'points_type_id', ts('Points Type'), CRM_Core_OptionGroup::values('points_type'));
    $form->addElement('text',   'points_from',    ts('From this many points'));
    $form->addElement('text',   'points_to',      ts('To this many points'));
    $form->add(       'static', 'note_points',    NULL, ts('If these numbers are both left empty, all contacts will be shown.'));
    $dateOptions = array(
      'format'  => 'd/m/Y',
      'minYear' => date('Y') - 100,
      'maxYear' => date('Y') + 100,
      'addEmptyOption' => TRUE,
    );
    $form->addElement('date',   'points_at_date', ts('As at date'), $dateOptions);
    $form->add(       'static', 'note_date',      NULL, ts('If the date is left empty, points will be checked as at the time of the search.'));

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('points_type_id', 'points_from', 'points_to', 'note_points', 'points_at_date', 'note_date'));
  }

  /**
   * Returns default values of form elements.
   *
   * @return array
   */
  function setDefaultValues() {
    return array(
      'points_type_id' => civicrm_api('OptionValue', 'getvalue', array(
        'version'           => 3,
        'option_group_name' => 'points_type',
        'is_default'        => 1,
        'return'            => 'value',
      )),
    );
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    // delegate to $this->sql(), $this->select(), $this->from(), $this->where(), etc.
    $sql = $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, $this->groupBy());
    //die("<div><pre>$sql</pre></div>\n"); // DEBUG
    return $sql;
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
      `contact_a`.`id`               AS `contact_id`,
      `cps`.`points`                 AS `points`,
      `contact_a`.`sort_name`        AS `sort_name`,
      `contact_a`.`contact_type`     AS `contact_type`,
      `contact_a`.`contact_sub_type` AS `contact_sub_type`,
      `cmt`.`name`                   AS `membership_type`,
      `cms`.`label`                  AS `membership_status`
    ";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    // Extract certain parameters, and escape for safety
    $points_type_id = CRM_Core_DAO::escapeString(CRM_Utils_Array::value('points_type_id', $this->_formValues));
    $points_at_date = CRM_Utils_Array::value('points_at_date', $this->_formValues);
    if (
      empty($points_at_date['Y']) &&
      empty($points_at_date['m']) &&
      empty($points_at_date['d'])
    ) {
      $points_at_date = "NOW()";
    }
    else {
      $points_at_date = sprintf("%04d%02d%02d", $points_at_date['Y'], $points_at_date['m'], $points_at_date['d']);
      CRM_Utils_Type::validate($points_at_date, 'Date');
      $points_at_date = "'$points_at_date'";
    }

    // Note: In order that the membership shown is the most recent one,
    // I'm using the 'LEFT JOIN' solution to the common problem
    // 'The Rows Holding the Group-wise Maximum of a Certain Column',
    // see http://dev.mysql.com/doc/refman/5.0/en/example-maximum-column-group-row.html
    return "
           FROM `civicrm_contact`           AS `contact_a`

      LEFT JOIN `civicrm_membership`        AS `cm`
             ON `cm`.`contact_id`            = `contact_a`.`id`
      LEFT JOIN `civicrm_membership`        AS `cm2`
             ON `cm2`.`contact_id`           = `cm`.`contact_id`
            AND `cm2`.`end_date`             > `cm`.`end_date`
      LEFT JOIN `civicrm_membership_type`   AS `cmt`
             ON `cmt`.`id`                   = `cm`.`membership_type_id`
      LEFT JOIN `civicrm_membership_status` AS `cms`
             ON `cms`.`id`                   = `cm`.`status_id`

      LEFT JOIN (
         SELECT   `contact_id`,
                  SUM(`points`)    AS `points`
           FROM   `civicrm_points`
          WHERE   `points_type_id`  = '$points_type_id'
            AND   `start_date`     <= $points_at_date
            AND   (
                    `end_date`     >= $points_at_date
             OR     `end_date`     IS NULL
                  )
       GROUP BY   `contact_id`
                )                  AS `cps`
             ON `cps`.`contact_id`  = `contact_a`.`id`
    ";
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @param bool $includeContactIDs
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $where = "
          `contact_a`.`is_deleted` IS NOT TRUE
      AND `cm2`.`id`               IS NULL
    ";

    $count  = 1;
    $clause = array();
    $params = array();

    // Minimum points
    $points_from = CRM_Utils_Array::value('points_from', $this->_formValues);
    if (CRM_Utils_Type::validate($points_from, 'Int', FALSE) !== NULL) {
      $clause[] = "(`cps`.`points` >= %{$count})";
      $params[$count] = array($points_from, 'Int');
      $count++;
    }

    // Maximum points
    $points_to = CRM_Utils_Array::value('points_to', $this->_formValues);
    if (CRM_Utils_Type::validate($points_to, 'Int', FALSE) !== NULL) {
      $clause[] = "(`cps`.`points` <= %{$count})";
      $params[$count] = array($points_to, 'Int');
      $count++;
    }

    if (!empty($clause)) {
      $where .= ' AND ' . implode(' AND ', $clause);
    }
    return $this->whereClause($where, $params);
  }

  /**
   * Construct a SQL GROUP BY clause
   *
   * @return string, sql fragment with columns to group by
   */
  function groupBy() {
    return "GROUP BY `contact_a`.`id`";
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
    $row['contact_type']     = CRM_Points_Form_Report_LeagueTable::formatSubtypes($row['contact_type']);
    $row['contact_sub_type'] = CRM_Points_Form_Report_LeagueTable::formatSubtypes($row['contact_sub_type']);
  }
}

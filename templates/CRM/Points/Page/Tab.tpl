{*
 * CiviPoints
 * Smarty template for contact view Points tab(s)
 *}
<div class="view-content">
  <div class="crm-block crm-content-block">
    <div class="action-link">
      <a class="button" href="{crmURL p="civicrm/points/grant" q="cid=$cid&type=$type"}">
        <span>
          <div class="icon add-icon"></div>
          {ts}Grant Points{/ts}
        </span>
      </a>
    </div>
    <br/>

    <div class="crm-accordion-wrapper">
      <div class="crm-accordion-header">
        {ts}Filter by effective date{/ts}
      </div>
      <div class="crm-accordion-body">
        &nbsp;
        <input type="button" id="points-current-{$type}" value="{ts}Current{/ts}"/>
        <input type="button" id="points-all-{$type}" value="{ts}All{/ts}"/>
        <label for="points-date-{$type}">{ts}Show points as at date:{/ts}</label>
        <input type="text" id="points-date-{$type}" size="10" maxlength="10"/>
      </div>
    </div>
    <br/>

    <table id="points-tab-table-{$type}">
      <thead>
        <tr>
          <th>{ts}Points{/ts}</th>
          <th>{ts}Granted by{/ts}</th>
          <th>{ts}Granted by{/ts}</th>       <!-- for sorting, hidden -->
          <th>{ts}Date/time granted{/ts}</th>
          <th>{ts}Date/time granted{/ts}</th><!-- for sorting, hidden -->
          <th>{ts}Start date{/ts}</th>
          <th>{ts}Start date{/ts}</th>       <!-- for sorting, hidden -->
          <th>{ts}End date{/ts}</th>
          <th>{ts}End date{/ts}</th>         <!-- for sorting, hidden -->
          <th>{ts}Description{/ts}</th>
          <th>{ts}Related to{/ts}</th>       <!-- not implemented yet, hidden -->
          <th>{ts}Related to{/ts}</th>       <!-- not implemented yet, hidden -->
          <th></th>                          <!-- action links, no title -->
        </tr>
      </thead>
      <tbody>
        <!-- empty to start with, filled by AJAX -->
      </tbody>
    </table>
  </div>
</div>

{literal}
  <script type="text/javascript">
    cj(document).ready(function() {
      var COL_POINTS          = 0;
      var COL_GRANTOR_SHOW    = 1;
      var COL_GRANTOR_SORT    = 2;
      var COL_GRANT_DATE_SHOW = 3;
      var COL_GRANT_DATE_SORT = 4;
      var COL_START_DATE_SHOW = 5;
      var COL_START_DATE_SORT = 6;
      var COL_END_DATE_SHOW   = 7;
      var COL_END_DATE_SORT   = 8;
      var COL_DESCRIPTION     = 9;
      var COL_ENTITY_TABLE    = 10;
      var COL_ENTITY_ID       = 11;
      var COL_ACTIONS         = 12;

      cj('#points-tab-table-{/literal}{$type}{literal}').dataTable({
        // Order by grant date/time ascending
        'aaSorting':       [[ COL_GRANT_DATE_SORT, 'asc' ]],
        // Try to make it look like Activities and Mailings
        'bFilter':         false,
        'iDisplayLength':  25,
        'sPaginationType': 'full_numbers',

        'aoColumnDefs':    [
          // Granting contact link
          { 'aTargets': [ COL_GRANTOR_SHOW    ], 'iDataSort': COL_GRANTOR_SORT    },
          // Display dates in local format, but sort by the underlying ISO format
          { 'aTargets': [ COL_GRANT_DATE_SHOW ], 'iDataSort': COL_GRANT_DATE_SORT },
          { 'aTargets': [ COL_START_DATE_SHOW ], 'iDataSort': COL_START_DATE_SORT },
          { 'aTargets': [ COL_END_DATE_SHOW   ], 'iDataSort': COL_END_DATE_SORT   },
          // Hide sort-only columns
          { 'aTargets': [ COL_GRANTOR_SORT, COL_GRANT_DATE_SORT, COL_START_DATE_SORT, COL_END_DATE_SORT ], 'bVisible': false },
          // Entity reference, not implemented yet
          { 'aTargets': [ COL_ENTITY_TABLE, COL_ENTITY_ID ], 'bVisible': false },
          // Action links
          { 'aTargets': [ COL_ACTIONS ], 'bSortable': false }
        ]
      });

      // Set up a datepicker for the effective date field
      cj('#points-date-{/literal}{$type}{literal}').datepicker({
        dateFormat: 'yy-mm-dd'
      });

      // When the date in the box is changed, retrieve fresh data
      cj('#points-date-{/literal}{$type}{literal}').change(function() {
        var table = cj('#points-tab-table-{/literal}{$type}{literal}').dataTable();
        var url   = '{/literal}{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Points_Page_AJAX&fnName=getEffectiveAjax&json=1'}{literal}';

        // Don't seem to have the DataTables fnReloadAjax plugin
        cj.ajax({
          'url':  url,
          'data': {
            'cid':  {/literal}{$cid}{literal},
            'type': {/literal}{$type}{literal},
            'date': cj(this).val()
          },
          'success': function(d) {
            var data = cj.parseJSON(d);
            table.fnClearTable();
            table.fnAddData(data);
          }
        });
      });

      // Handle the 'Current' button being clicked
      cj('#points-current-{/literal}{$type}{literal}').click(function() {
        cj('#points-date-{/literal}{$type}{literal}').val('');
        cj('#points-date-{/literal}{$type}{literal}').trigger('change');
      });

      // Handle the 'All' button being clicked
      cj('#points-all-{/literal}{$type}{literal}').click(function() {
        cj('#points-date-{/literal}{$type}{literal}').val('all');
        cj('#points-date-{/literal}{$type}{literal}').trigger('change');
      });

      // Trigger an initial load of the current effective points
      cj('#points-current-{/literal}{$type}{literal}').trigger('click');
    });
  </script>
{/literal}

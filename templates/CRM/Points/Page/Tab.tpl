{*
 * CiviPoints
 * Smarty template for contact view Points tab(s)
 *}
<div class="view-content">
  <div class="crm-block crm-content-block">
    <div class="action-link">
      <a class="button" href="{crmURL p="civicrm/points/grant" q="cid=$cid"}">
        <span>
          <div class="icon add-icon"></div>
          {ts}Grant Points{/ts}
        </span>
      </a>
    </div>

    <table id="points-tab-table">
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
          <th/>                              <!-- action links, no title -->
        </tr>
      </thead>
      <tbody>
        {foreach from=$points item=rec}
          <tr>
            <td>{$rec.points}</td>
            <td>{$rec.grantor_contact_id}</td>  <!-- will be replaced with a link -->
            <td>{$rec.grantor_sort_name}</td>   <!-- for sorting, hidden -->
            <td>{$rec.grant_date_time_show}</td>
            <td>{$rec.grant_date_time}</td>     <!-- for sorting, hidden -->
            <td>{$rec.start_date_show}</td>
            <td>{$rec.start_date}</td>          <!-- for sorting, hidden -->
            <td>{$rec.end_date_show}</td>
            <td>{$rec.end_date}</td>            <!-- for sorting, hidden -->
            <td>{$rec.description}</td>
            <td>{$rec.entity_table}</td>        <!-- not implemented yet, hidden -->
            <td>{$rec.entity_id}</td>           <!-- not implemented yet, hidden -->
            <td>{$rec.links}</td>
          </tr>
        {/foreach}
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

      cj('#points-tab-table').dataTable({
        // Order by grant date/time ascending
        'aaSorting':       [[ COL_GRANT_DATE_SORT, 'asc' ]],
        // Try to make it look like Activities and Mailings
        'bFilter':         false,
        'iDisplayLength':  25,
        'sPaginationType': 'full_numbers',

        'aoColumnDefs':    [
          // Granting contact link
          // Note, fnRender is now deprecated
          // Also it would be equally possible to use these columns the other way round with bUseRendered
          { 'aTargets': [ COL_GRANTOR_SHOW ], 'iDataSort': COL_GRANTOR_SORT, 'fnRender': function(obj) {
            return "<a href='{/literal}{crmURL p="civicrm/contact/view" q="reset=1&cid="}{literal}" + obj.aData[COL_GRANTOR_SHOW] + "'>" + obj.aData[COL_GRANTOR_SORT] + "</a>";
          }},
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
    });
  </script>
{/literal}

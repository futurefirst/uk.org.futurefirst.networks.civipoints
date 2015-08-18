<table id="points-tab-table">
  <thead>
    <tr>
      <th>{ts}Points{/ts}</th>
      <th>{ts}Granted by{/ts}</th>
      <th>{ts}Date/time granted{/ts}</th>
      <th>{ts}Start date{/ts}</th>
      <th>{ts}End date{/ts}</th>
      <th>{ts}Related to{/ts}</th>
      <th>{ts}Description{/ts}</th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$points item=rec}
      <tr>
        <td>{$rec.points}</td>
        <td>{$rec.grantor_contact_id}</td>
        <td>{$rec.grant_date_time}</td>
        <td>{$rec.start_date}</td>
        <td>{$rec.end_date}</td>
        <td>{$rec.entity_table} {$rec.entity_id}</td>
        <td>{$rec.description}</td>
      </tr>
    {/foreach}
  </tbody>
</table>

{literal}
  <script type="text/javascript">
    cj(document).ready(function() {
      cj('#points-tab-table').dataTable({
        'sPaginationType': 'full_numbers'
      });
    });
  </script>
{/literal}

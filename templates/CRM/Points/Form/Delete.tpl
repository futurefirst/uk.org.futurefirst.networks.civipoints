<div class="crm-form-block">
  {* HEADER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <div class="messages status no-popup">
    <div class="icon inform-icon"></div>
    {ts}
      Please note that these points will be removed from history for this contact.
      If points were not granted in error, but need to expire or be reset,
      it is better to edit the points records and set the 'Effective To' date appropriately.
    {/ts}
  </div>

  {* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}
  {foreach from=$elementNames item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}

  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>

<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-points-create">
    <div class="crm-section">
        <div class="label">{$form.points.label}</div>
        <div class="content">{$form.points.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.points_type_id.label}</div>
        <div class="content">{$form.points_type_id.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.description.label}</div>
        <div class="content">{$form.description.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.expiration_interval.label}</div>
        <div class="content">
            <span>{$form.expiration_interval.html}</span>
            <span>{$form.expiration_unit.html}</span>
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.note_end_date.label}</div>
        <div class="content">{$form.note_end_date.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

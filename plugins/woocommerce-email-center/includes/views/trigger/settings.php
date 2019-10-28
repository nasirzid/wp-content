<?php

/**
 * View for Trigger settings panel
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<style type="text/css">
    #submitdiv {
        display: none;
    }
</style>

<div id="rp_wcec_trigger_settings" class="rp_wcec">

    <div class="rp_wcec_settings_first_row">
        <div class="rp_wcec_field rp_wcec_field_double rp_wcec_no_left_margin">
            <?php RP_WCEC_Form_Builder::text(array(
                'id'        => 'rp_wcec_title',
                'name'      => 'rp_wcec[title]',
                'value'     => $object->get_title(),
                'label'     => __('Title', 'rp_wcec'),
                'required'  => 'required',
            )); ?>
        </div>
        <div class="rp_wcec_field rp_wcec_field_double">
            <?php RP_WCEC_Form_Builder::grouped_select(array(
                'id'        => 'rp_wcec_event',
                'name'      => 'rp_wcec[event]',
                'value'     => $object->get_event(),
                'label'     => __('Event', 'rp_wcec'),
                'options'   => RP_WCEC_Trigger::get_trigger_event_list_for_display(),
            )); ?>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="rp_wcec_settings_second_row">
        <div class="rp_wcec_field rp_wcec_field_double rp_wcec_no_left_margin">
            <?php RP_WCEC_Form_Builder::select(array(
                'id'        => 'rp_wcec_email_id',
                'name'      => 'rp_wcec[email_id]',
                'value'     => $object->get_email_id(),
                'label'     => __('Email To Send', 'rp_wcec'),
                'options'   => RightPress_Helper::add_empty_field_option(RP_WCEC_Email::get_list_of_all_items(true)),
                'required'  => 'required',
            )); ?>
        </div>
        <div class="rp_wcec_field rp_wcec_field_double">
            <?php RP_WCEC_Form_Builder::select(array(
                'id'        => 'rp_wcec_schedule_method',
                'name'      => 'rp_wcec[schedule_method]',
                'value'     => $object->get_schedule_method(),
                'label'     => __('Schedule', 'rp_wcec'),
                'options'   => RP_WCEC_Scheduler::get_method_list_for_display(),
            )); ?>
        </div>
        <div class="rp_wcec_field rp_wcec_field_single" style="display: none;">
            <?php RP_WCEC_Form_Builder::number(array(
                'id'            => 'rp_wcec_schedule_number',
                'name'          => 'rp_wcec[schedule_number]',
                'placeholder'   => '0 ',
                'label'         => '&nbsp;',
                'value'         => $schedule_number_value,
                'required'      => 'required',
            )); ?>
        </div>
        <div class="rp_wcec_field rp_wcec_field_single" style="display: none;">
            <?php RP_WCEC_Form_Builder::date(array(
                'id'            => 'rp_wcec_schedule_date_display',
                'name'          => 'rp_wcec_do_not_submit[schedule_date_display]',
                'class'         => 'rp_wcec_date',
                'placeholder'   => __('Select date', 'rp_wcec'),
                'label'         => '&nbsp;',
                'value'         => (!empty($schedule_date_value) ? RP_WCEC::get_wp_date($schedule_date_value) : ''),
                'required'      => 'required',
            )); ?>
            <?php RP_WCEC_Form_Builder::hidden(array(
                'id'            => 'rp_wcec_schedule_date',
                'name'          => 'rp_wcec[schedule_date]',
                'value'         => $schedule_date_value,
            )); ?>
        </div>
        <div class="rp_wcec_field rp_wcec_field_single" style="display: none;">
            <?php RP_WCEC_Form_Builder::select(array(
                'id'        => 'rp_wcec_schedule_weekday',
                'name'      => 'rp_wcec[schedule_weekday]',
                'value'     => $schedule_weekday_value,
                'label'     => '&nbsp;',
                'options'   => RP_WCEC_Scheduler::get_weekdays(),
            )); ?>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="rp_wcec_settings_conditions_row">
        <div class="rp_wcec_field rp_wcec_field_full">
            <label><?php _e('Conditions', 'rp_wcec'); ?>&nbsp;&nbsp;<span class="rp_wcec_none"><?php _e('Checked when trigger event fires', 'rp_wcec'); ?></span></label>
            <div class="rp_wcec_inner_wrapper">
                <div class="rp_wcec_add_condition">
                    <button type="button" class="button" value="<?php _e('Add Condition', 'rp_wcec'); ?>">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;<span><?php _e('Add Condition', 'rp_wcec'); ?></span>
                    </button>
                </div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="rp_wcec_settings_conditions_scheduled_row" style="display: none;">
        <div class="rp_wcec_field rp_wcec_field_full">
            <label><?php _e('Conditions - Scheduled', 'rp_wcec'); ?>&nbsp;&nbsp;<span class="rp_wcec_none"><?php _e('Checked when scheduled sending starts', 'rp_wcec'); ?></span></label>
            <div class="rp_wcec_inner_wrapper">
                <div class="rp_wcec_add_condition">
                    <button type="button" class="button" value="<?php _e('Add Condition', 'rp_wcec'); ?>">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;<span><?php _e('Add Condition', 'rp_wcec'); ?></span>
                    </button>
                </div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

</div>

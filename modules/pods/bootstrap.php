<?php
/**
 * Make pods forms use bootstrap 3 input classes
 */
namespace wordpress_helpers\modules\pods;

function addFormControl($options){
    $options['class'] .= 'form-control';
    return $options;
};

add_filter('pods_form_ui_field_paragraph_options',__NAMESPACE__.'\\addFormControl');
add_filter('pods_form_ui_field_currency_options',__NAMESPACE__.'\\addFormControl');
add_filter('pods_form_ui_field_pick_options',__NAMESPACE__.'\\addFormControl');
add_filter('pods_form_ui_field_datetime_options',__NAMESPACE__.'\\addFormControl');
add_filter('pods_form_ui_field_date_options',__NAMESPACE__.'\\addFormControl');
add_filter('pods_form_ui_field_text_options',__NAMESPACE__.'\\addFormControl');
add_filter('pods_form_ui_field_submit_options',__NAMESPACE__.'\\addFormControl');
add_filter('pods_form_ui_field_phone_options',__NAMESPACE__.'\\addFormControl');


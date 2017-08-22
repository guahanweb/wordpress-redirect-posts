<?php
function gw_locate_template($template_name, $template_path = '', $default_path = '') {
    if (!$template_path) {
        $template_path = 'redirectposts-plugin-templates/';
    }

    if (!$default_path) {
        $default_path = plugin_dir_path(__FILE__) . 'templates/';
    }

    $template = locate_template(array(
        $template_path . $template_name,
        $template_name
    ));

    if (!$template) {
        $template = $default_path . $template_name;
    }

    return apply_filters('gw_locate_template', $template, $template_name, $template_path, $default_path);
}

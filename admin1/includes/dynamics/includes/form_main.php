<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_main.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/**
 * @param string $form_name
 * @param string $method     - 'post' or 'get'
 * @param string $action_url - form current uri (defaults: FORM_REQUEST)
 * @param array  $options    :
 *                           form_id = default as form_name
 *                           class = default empty
 *                           enctype = true or false , set true to allow file upload
 *                           max_tokens = store into session number of tokens , default as 1.
 *
 * @return string
 */
function openform($form_name, $method, $action_url = FORM_REQUEST, array $options = []) {

    $method = (strtolower($method) == 'post') ? 'post' : 'get';
    $default_options = [
        'form_id'    => $form_name,
        'class'      => '',
        'enctype'    => FALSE,
        'max_tokens' => fusion_get_settings('form_tokens'),
        'remote_url' => '',
        'inline'     => FALSE,
        'on_submit'  => '',
        'token_time' => TIME,
    ];

    $options += $default_options;

    if (!$action_url) {
        $action_url = FORM_REQUEST;
    }

    $class = $options['class'];
    if (!\defender::safe()) {
        $class .= " warning";
    }

    $html = "<form name='".$form_name."' id='".$options['form_id']."' method='".$method."' action='".$action_url."' class='".($options['inline'] ? "form-inline " : '').($class ? $class : 'm-0')."'".($options['enctype'] ? " enctype='multipart/form-data'" : '').($options['on_submit'] ? " onSubmit='".$options['on_submit']."'" : '').">\n";

    if ($method == 'post') {
        $token = \Defender\Token::generate_token($options['form_id'], $options['max_tokens'], $options['remote_url'], $options['token_time']);
        $html .= "<input type='hidden' name='fusion_token' value='".$token."' />\n";
        $html .= "<input type='hidden' name='form_id' value='".$options['form_id']."' />\n";
    }

    return (string)$html;
}

/**
 * @return string
 */
function closeform() {
    return (string)"</form>\n";
}
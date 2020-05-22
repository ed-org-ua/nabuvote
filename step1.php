<?php

require("system/__init__.php");


/**
 * Session must be clean on this step
 */
if (!empty($_SESSION)) {
    session_unset();
    session_destroy();
}

/**
 * Handle form data
 */
if ($_POST) {
    $ukr_citizen = post_arg('ukr_citizen');
    $personal_data = post_arg('personal_data');
    $rules_agree = post_arg('rules_agree');
    $captcha_res = captcha_verify();
    $current_date = date('Y-m-d H:i:s');
    $need_wait = need_wait_before_retry();

    if (!$ukr_citizen)
        append_error("Не підтверджена згода з правилами голосування.");
    if (!$personal_data)
        append_error("Немає згоди на обробку персональних даних.");
    if (!$rules_agree)
        append_error("Не підтверджена згода з порядком проведення конкурсу.");
    if (!$captcha_res)
        append_error("Не пройдено тест на роботів!");
    if ($current_date < $settings['open_elections_time'])
        append_error("Голосування ще не розпочалось.");
    if ($current_date > $settings['close_elections_time'])
        append_error("Голосування вже закінчилось.");
    if ($need_wait)
        append_error("Повторна спроба можлива через $need_wait хв.");

    if (empty($_ERRORS) && $ukr_citizen && $personal_data && $rules_agree && $captcha_res) {
        init_user_session();
        set_test_passed('captcha');
        redirect('step2.php');
    }
}

require(get_template('step1'));

<?php

$raw_body = file_get_contents('php://input');

$update_order = array();
$data['host'] = $hostname;
$verify_endpoint = 'https://ipnpb.paypal.com/cgi-bin/webscr';
if (isset($data['test_ipn']) && (string)$data['test_ipn'] === '1') {
    $verify_endpoint = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
}
$verify_payload = 'cmd=_notify-validate';
if (is_string($raw_body) && $raw_body !== '') {
    $verify_payload .= '&' . $raw_body;
}

$verify_response = false;
$verify_error = null;
if (function_exists('curl_init')) {
    $ch = curl_init($verify_endpoint);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $verify_payload);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Connection: Close',
        'User-Agent: Microweber-PayPal-IPN',
    ]);
    $verify_response = curl_exec($ch);
    if ($verify_response === false) {
        $verify_error = curl_error($ch);
    }
    curl_close($ch);
} else {
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Connection: Close\r\nUser-Agent: Microweber-PayPal-IPN\r\n",
            'content' => $verify_payload,
            'timeout' => 30,
        ],
    ]);
    $verify_response = file_get_contents($verify_endpoint, false, $context);
}

if (trim((string)$verify_response) === 'VERIFIED') {
    if (isset($data['payment_gross']) and $data['payment_gross']) {
        // payment_gross: Will be empty for non-USD payments
        $update_order['payment_amount'] = $data['payment_gross'];
    } else if (isset($data['mc_gross']) and $data['mc_gross']) {
        // mc_gross: Will not be empty for non-USD payments
        $update_order['payment_amount'] = $data['mc_gross'];
    }
    if (isset($data['payer_email'])) {
        $update_order['payment_email'] = $data['payer_email'];
    }
    if (isset($data['payer_id'])) {
        $update_order['payer_id'] = $data['payer_id'];
    }
    if (isset($data['payer_status'])) {
        $update_order['payer_status'] = $data['payer_status'];
    }
    if (isset($data['address_name'])) {
        $update_order['payment_name'] = $data['address_name'];
    }
    if (isset($data['address_country'])) {
        $update_order['payment_country'] = $data['address_country'];
    }
    if (isset($data['address_street'])) {
        $update_order['payment_address'] = $data['address_street'];
    }
    if (isset($data['address_city'])) {
        $update_order['payment_city'] = $data['address_city'];
    }
    if (isset($data['address_state'])) {
        $update_order['payment_state'] = $data['address_state'];
    }
    if (isset($data['address_zip'])) {
        $update_order['payment_zip'] = $data['address_zip'];
    }
    if (isset($data['mc_currency'])) {
        $update_order['payment_currency'] = $data['mc_currency'];
    }
    if (isset($data['shipping'])) {
        $update_order['payment_shipping'] = $data['shipping'];
    }
    if (isset($data['payment_type'])) {
        $update_order['payment_type'] = $data['payment_type'];
    }
    if (isset($data['txn_id'])) {
        $update_order['transaction_id'] = $data['txn_id'];
    }
    if (isset($data['receiver_email'])) {
        $update_order['payment_receiver_email'] = $data['receiver_email'];
    }

    $update_order['is_paid'] = 1;
    $update_order['order_completed'] = 1;
    $update_order['payment_verify_token'] = $payment_verify_token;
}

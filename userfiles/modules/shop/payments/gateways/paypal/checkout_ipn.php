<?php





$update_order = array();
$data['host'] = $hostname;
$is_paypal = '';
if (is_string($hostname) && $hostname !== '') {
    $is_paypalArr = explode('.', $hostname);
    $n = count($is_paypalArr);
    if ($n >= 2) {
        $is_paypal = "{$is_paypalArr[$n-2]}.{$is_paypalArr[$n-1]}";
    }
}

if (strtolower(trim($is_paypal)) == 'paypal.com') {
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

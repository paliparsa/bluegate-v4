<?php
return [
 'sub_base_url' => env('BLUEGATE_SUB_BASE_URL'),
 'token_pepper' => env('BLUEGATE_TOKEN_PEPPER'),
 'node_health_timeout' => env('BLUEGATE_NODE_HEALTH_TIMEOUT', 8),
 'payments' => [
  'zarinpal' => [
   'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
   'amount_multiplier' => env('ZARINPAL_AMOUNT_MULTIPLIER', 10),
   'api_base' => env('ZARINPAL_API_BASE', 'https://payment.zarinpal.com'),
   'startpay_base' => env('ZARINPAL_STARTPAY_BASE', 'https://www.zarinpal.com/pg/StartPay'),
  ],
 ],
 'operations' => [
  'traffic_price_per_gb' => env('BLUEGATE_TRAFFIC_PRICE_PER_GB', 5000),
  'location_change_price' => env('BLUEGATE_LOCATION_CHANGE_PRICE', 0),
 ],
 'telegram' => [
  'bot_token' => env('TELEGRAM_BOT_TOKEN'),
  'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
 ],
];
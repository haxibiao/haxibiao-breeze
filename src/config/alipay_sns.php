<?php

return [
    'app_id'      => env('ALIPAY_SNS_APP_ID'),
    'pem_private' => base_path('cert/alipay/private_key'),
    'pem_public'  => base_path('cert/alipay/public_key'),
];

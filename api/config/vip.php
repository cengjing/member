<?php
/*
|--------------------------------------------------------------------------
| VIP期限配置
| name: vip展示名称
| deadline: vip期限（单位月）
| price: vip价格（单位元）
| originalPrice: vip原价(单位元，可为空，为空不显示原价)
|--------------------------------------------------------------------------
*/
$VIP_deadline = env('VIP_DEADLINE', null);
if ($VIP_deadline) {
    $deadline = json_decode($VIP_deadline, true);
} else {
    $deadline = [
        [
            'name' => '连续包月',
            'deadline' => 1,
            'price' => 18,
            'originalPrice' => 38
        ],
        [
            'name' => '连续包季',
            'deadline' => 3,
            'price' => 38,
            'originalPrice' => 58
        ],
        [
            'name' => '连续包年',
            'deadline' => 12,
            'price' => 180,
            'originalPrice' => 380
        ]
    ];
}
return [
    'deadline' => $deadline,
    'discount' => env('VIP_DISCOUNT', 0.9),    //折扣
    'reminder' => env('VIP_REMINDER', 5)    //vip到期提醒时间(单位天)
];

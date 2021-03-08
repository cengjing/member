<?php

namespace App\Http\Controllers\v1\Plugin\Client;

use Illuminate\Http\Request as Request;
use App\Http\Controllers\Controller;

/**
 * vip
 * VIP
 * Class VipController
 * @package App\Http\Controllers\v1\Plugin\Admin
 */
class VipController extends Controller
{
    /**
     * CouponList
     * vip费用列表
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @queryParam  limit int 每页显示条数
     * @queryParam  page string 页码
     */
    public function list(Request $request)
    {
        $vip = config('vip');
        return resReturn(1, $vip);
    }
}

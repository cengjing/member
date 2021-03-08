<?php


namespace App\Observers\PaymentLog;


use App\Models\v1\Coupon;
use App\Models\v1\MoneyLog;
use App\Models\v1\PaymentLog;
use App\Models\v1\User;
use App\Models\v1\UserCoupon;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * good indent payment succeed
 * VIP支付完成
 * Class GoodIndentPaymentSucceedObserver
 * @package App\Observers\GoodIndent
 */
class VipPaymentSucceedObserver
{
    protected $request;
    protected $route = [
        'app/paymentNotify'
    ];
    protected $execute = false;

    public function __construct(Request $request)
    {
        if (!app()->runningInConsole()) {
            $this->request = $request;
            $path = explode("admin", $request->path());
            if (count($path) == 2) {
                $name = 'admin' . $path[1];
            } else {
                $path = explode("app", $request->path());
                $name = 'app' . $path[1];
            }
            if (collect($this->route)->contains($name)) {
                $this->execute = true;
            }
        }
    }

    public function updated(PaymentLog $paymentLog)
    {
        if (($this->execute || app()->runningInConsole())) {
            // 状态为已完成
            if ($paymentLog->type == PaymentLog::PAYMENT_LOG_TYPE_VIP && $paymentLog->state == PaymentLog::PAYMENT_LOG_STATE_COMPLETE) {
                $vip = config('vip')['deadline'][$paymentLog->pay_id];

                $User = User::find($paymentLog->user_id);
                $originalVip = $User->vip;
                $deadline = $vip['deadline'];
                $vip_time = $User->vip_time ? strtotime($User->vip_time) : time();
                $User->vip = User::USER_VIP_YES;
                $User->vip_time = date("Y-m-d H:i:s", strtotime("+$deadline month", $vip_time));
                $User->save();
                $Money = new MoneyLog();
                $Money->user_id = $User->id;
                $Money->type = MoneyLog::MONEY_LOG_TYPE_EXPEND;
                $Money->money = $paymentLog->money;
                $Money->remark = '购买VIP';
                $Money->save();
                //第一个月开通即可领取优惠券
                if ($originalVip == User::USER_VIP_NO) {
                    $Coupon = Coupon::where('vip', Coupon::COUPON_VIP_YES)->get();
                    foreach ($Coupon as $c) {
                        if ($c->amount > 0) {
                            for ($i = 1; $i <= $c->amount; $i++) {
                                $UserCoupon = new UserCoupon();
                                $UserCoupon->user_id = $User->id;
                                $UserCoupon->coupon_id = $c->id;
                                $UserCoupon->ticket = orderNumber();
                                $UserCoupon->failure_time = Carbon::now()->toDateTimeString();
                                $UserCoupon->save();
                            }
                        }
                    }
                }
            }
        }
    }
}

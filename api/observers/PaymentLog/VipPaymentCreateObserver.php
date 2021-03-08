<?php


namespace App\Observers\PaymentLog;


use App\Code;
use App\Models\v1\Good;
use App\Models\v1\GoodIndent;
use App\Models\v1\GoodSku;
use App\Models\v1\MiniProgram;
use App\Models\v1\PaymentLog;
use Illuminate\Http\Request;

/**
 * vip payment create
 * VIP支付生成
 * Class VipPaymentCreateObserver
 * @package App\Observers\GoodIndent
 */
class VipPaymentCreateObserver
{
    protected $request;
    protected $route = [
        'app/unifiedPayment'
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

    public function creating(PaymentLog $paymentLog)
    {
        if (($this->execute || app()->runningInConsole())) {
            if ($this->request->type == PaymentLog::PAYMENT_LOG_TYPE_VIP && $paymentLog->state == PaymentLog::PAYMENT_LOG_STATE_CREATE) {
                $vip = config('vip')['deadline'][$this->request->id];
                $fee = $vip['price'] * 100;
                $openid = $this->request->header('openid');
                if (auth('web')->user()->vip) {
                    $body = '续费vip';
                } else {
                    $body = '购买vip';
                }
                $payment = (new MiniProgram())->payment($this->request->platform, $body, $fee, $openid, $this->request->trade_type);
                $paymentLog->user_id = auth('web')->user()->id;
                $paymentLog->number = $payment['number'];
                $paymentLog->money = $fee;
                $paymentLog->name = $body;
                $paymentLog->type = PaymentLog::PAYMENT_LOG_TYPE_VIP;
                $paymentLog->pay_id = $this->request->id; //vip的index
                $paymentLog->data = json_encode($payment);
            }
        }
    }
}

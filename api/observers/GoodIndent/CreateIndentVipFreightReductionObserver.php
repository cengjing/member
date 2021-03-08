<?php


namespace App\Observers\GoodIndent;


use App\Models\v1\GoodIndent;
use App\Models\v1\GoodIndentCommodity;
use App\Models\v1\MoneyLog;
use Illuminate\Http\Request;

/**
 * create indent commodity
 * 创建订单VIP减免运费
 * Class CreateIndentVipFreightReductionObserver
 * @package App\Observers\GoodIndent
 */
class CreateIndentVipFreightReductionObserver
{
    protected $request;
    protected $route = [
        'app/goodIndent',
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

    public function created(GoodIndent $goodIndent)
    {
        // 当状态为待付款时触发
        if (($this->execute || app()->runningInConsole()) && $goodIndent->state == GoodIndent::GOOD_INDENT_STATE_PAY) {
            // 存在运费才触发
            if ($goodIndent->carriage > 0) {
                $Money = new MoneyLog();
                $Money->user_id = $goodIndent->user_id;
                $Money->type = MoneyLog::MONEY_LOG_TYPE_INCOME;
                $Money->money = $goodIndent->carriage;
                $Money->remark = '超级会员减免运费';
                $Money->save();
                $goodIndent->carriage = 0;
                $goodIndent->save();
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\v1\User;
use App\Notifications\InvoicePaid;
use Illuminate\Console\Command;

class VipDueProcessing extends Command
{
    /**
     * vip到期处理
     *
     * @var string
     */
    protected $signature = 'vip:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'VIP due processing。';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @return void
     */
    public function handle()
    {
       $UserAll=User::where('vip',User::USER_VIP_YES)->where('vip_time','<=',date('Y-m-d H:i:s'))->get();
        if ($UserAll) {
            foreach ($UserAll as $u) {
                $User = User::find($u->id);
                $User->vip = User::USER_VIP_NO;
                $User->save();
                $parameter = [
                    'template' => 'vip_expire',   //通知模板标识
                    'user_id' => $u->id    //用户ID
                ];
                $invoice = [
                    'type' => InvoicePaid::NOTIFICATION_TYPE_SYSTEM_MESSAGES,
                    'title' => '您的超级会员已经到期',
                    'list' => [
                        [
                            'keyword' => '到期时间',
                            'data' => $u->vip_time
                        ]
                    ],
                    'remark' => '感谢您的支持与厚爱。',
                    'url' => '/pages/vip/index',
                    'parameter' => $parameter,
                    'prefers' => ['database', 'mail']
                ];
                $user = User::find($parameter['user_id']);
                $user->notify(new InvoicePaid($invoice));
            }
        }
    }
}

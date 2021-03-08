#会员体系
## 说明
- 该插件依赖dsshop项目，而非通用插件
- 支持版本:dsshop v2.0.0及以上
- 已同步版本：dsshop v2.0.0

## 功能介绍
- 普通会员和VIP会员区分
- VIP支持在线开通、后台开通、系统赠送
- VIP购物可享受折扣（单品设置折扣>默认折扣）
- VIP可以领取优惠券（结合优惠券插件）
- VIP到期提醒（可设置提醒天数，如5天，则在到期日5天前发送到期提醒，在到期当日发送到期提醒）
- 该插件需要依赖于优惠券插件，如果未安装优惠券，将引响部分功能无法使用

## 使用说明

#### 一、 下载coupon最新版

#### 二、 解压coupon到项目plugin目录下

#### 三、 登录dshop后台，进入插件列表

#### 四、 在线安装（请保持dshop的目录结构，如已部署到线上，请在本地测试环境安装，因涉及admin和uni-app，不建议在线安装）

#### 五、 进入api目录执行数据库迁移使用

```
php artisan migrate
```

#### 六、 进入后台，添加权限

| **权限名称** | **API**       | **分组**     | **菜单图标** | **显示在菜单栏** |
| ------------ | ------------- | ------------ | ------------ | ---------------- |
| 优惠券       | Coupon        | 工具         | 否           | 是               |
| 优惠券列表   | CouponList    | 工具->优惠券 | 否           | 是               |
| 添加优惠券   | CouponCreate  | 工具->优惠券 | 否           | 否               |
| 优惠券操作   | CouponEdit    | 工具->优惠券 | 否           | 否               |
| 优惠券删除   | CouponDestroy | 工具->优惠券 | 否           | 否               |



#### 七、 进入后台，为管理员分配权限

#### 八、 使用说明

##### 运维指南

- 插件提供`vip:expire`（vip到期处理）、`vip:reminder`(vip到期提醒)和`vipCoupon:grant`(vip优惠券发放，如需优惠券可不使用)命令，只需要将这几个命令加入到`Kernel`中，即可实现优惠券的自动任务

```php
protected $commands = [
    ...
    VipCouponGrant::class,
    VipDueProcessing::class,
    VipExpirationReminder::class,
];
protected function schedule(Schedule $schedule){
    ...
    //vip到期处理
    $schedule->command('vip:expire')->everyMinute();
    //vip到期提醒
    $schedule->command('vip:reminder')->everyMinute();
    //vip优惠券每月下发一次
    $schedule->command('vipCoupon:grant')->monthlyOn(1, '01:00');
}
```

##### 开发指南

- 插件并不整合到业务代码中，所以当你安装插件后，需要根据以下步骤进行个性化的开发

###### 商品增加vip折扣配置

``` vue
#admin\src\views\CommodityManagement\Good\components\Detail.vue
<template>
    ...
	<el-form-item label="货号" prop="number" style="width:400px;">
        <el-input v-model="ruleForm.number" maxlength="50" clearable/>
    </el-form-item>
    <el-form-item label="会员折扣" prop="discount" style="width:400px;">
        <el-input v-model="ruleForm.discount" maxlength="50" clearable/>
        <div class="el-upload__tip">配置后，会员享受折扣按该设置处理，未配置按全局处理</div>
    </el-form-item>
</template>
<script>
import Coupon from '../../api/coupon'
export default {
    data() {
		return {
			discount: '',
            rules: {
                discount: [
                  { required: false, validator: validatePrice, message: '折扣只能是数字', trigger: 'blur' }
                ],
            }
		};
	},
}
</script>
```

###### 商品控制器

```php
#api\app\Http\Controllers\v1\Admin\GoodController.php
/**
     * GoodCreate
     ...
     * @queryParam  discount float  会员折扣价
     */
public function create(SubmitGoodRequest $request){
    ...
    $Good->discount = $request->discount;
	...
}
public function edit(SubmitGoodRequest $request, $id){
    ...
    $Good->discount = $request->discount;
	...
}

```

###### 客户端用户增加会员折扣数据展示

```php
#api\app\Http\Controllers\v1\Client\UserController.php
public function detail()
{   
    ...
	$User = User::select('cellphone', 'nickname', 'portrait', 'money', 'uuid', 'email', 'notification', 'wechat', 'vip', 'vip_time')->find(auth('web')->user()->id);
}

```

###### 商品验证增加会员折扣验证

```php
#api\app\Http\Requests\v1\SubmitGoodRequest.php
'discount' => 'nullable|numeric',
'discount.numeric' =>'会员折扣格式有误',
```

###### 商品模型添加会员折扣代码

```php
#api\app\Models\v1\Good.php
/**
 * @property int discount
 * @method static find(int $id)
 */
class Good extends Model
{
	/**
     * 会员折扣价显示
     *
     * @param $row
     * @return string
     */
    public function getDiscountAttribute($row)
    {
        if (isset($this->attributes['discount'])) {
            if (self::$withoutAppends) {
                $return = $this->attributes['discount'];
            } else {
                $return = $this->attributes['discount'] / 100;
            }
            return $return > 0 ? $return : '';
        }
    }

    /**
     * 会员折扣价存入
     *
     * @param $row
     * @return string
     */
    public function setDiscountAttribute($value)
    {
        $this->attributes['discount'] = sprintf("%01.2f", $value) * 100;
    }
}
```

###### 支付记录添加vip类型

```php
#api\app\Models\v1\PaymentLog.php
const PAYMENT_LOG_TYPE_VIP = 'vip'; //支付类型:开通vip
```

###### 客户端用户列表添加vip代码

``` vue
#client\Dsshop\pages\user\user.vue
<template>
    ...
	<view class="vip-card-box">
		<image class="card-bg" src="/static/vip-card-bg.png" mode=""></image>
		<view class="b-btn" @click="navTo('/pages/vip/index')">
			<template v-if="user.vip">查看特权</template>
			<template v-else>立即开通</template>
		</view>
		<view class="tit">
			<text class="yticon icon-iLinkapp-"></text>
			超级会员
		</view>
		<text class="e-m">超级会员</text>
		<text class="e-b">
            <template v-if="user.vip">您的VIP将于{{user.vip_time}}到期</template>
            <template v-else>开通会员 省钱 省心</template>
		</text>
	</view>
</template>
```

###### 用户模型添加vip状态常量

```php
#api\app\Models\v1\User.php
const USER_VIP_YES = 1;  //是否vip：是
const USER_VIP_NO = 0;  //是否vip：否
```

###### 优惠券-添加增加vip的支持

``` vue
#admin\src\views\ToolManagement\Coupon\list.vue
<template>
    ...
	<el-table-column label="是否vip专属" sortable="custom" prop="type">
        <template slot-scope="scope">
          <span>{{ scope.row.vip ? '是' : '否' }}</span>
        </template>
      </el-table-column>
	...
	<el-tooltip v-permission="$store.jurisdiction.CouponEdit" v-if="scope.row.state === '发放中' && scope.row.vip === 0" class="item" effect="dark" content="提前结束" placement="top-start">
    <el-tooltip v-permission="$store.jurisdiction.CouponEdit" v-if="scope.row.state === '未发放' && scope.row.vip === 0" class="item" effect="dark" content="提前开始" placement="top-start">
    ...
    <el-form-item label="是否vip专属" prop="vip">
          <el-radio-group v-model="temp.vip">
            <el-radio :label="0">否</el-radio>
            <el-radio :label="1">是</el-radio>
          </el-radio-group>
          <el-alert
            style="margin-top:10px;"
            title="设为vip专属后，'每人限领'和'领取时间'自动失效，优惠券数量即每月发给vip的数量"
            type="warning"/>
        </el-form-item>
</template>
<script>
export default {
    data() {
		return {
            rules: {
                vip: [
                  { required: true, message: '请选择是否vip专属', trigger: 'change' }
                ],
            },
        	vip: 0
		};
	},
}
</script>
```

###### 优惠券到期结束增加vip支持

```php
#api\app\Console\Commands\CouponExpireDispose.php
public function handle()
    {
        $Coupon=Coupon::where('endtime',date('Y-m-d'))->where('state',Coupon::COUPON_STATE_SHOW)->where('vip', Coupon::COUPON_VIP_NO)->get();
    }
```

###### 优惠券自动开启增加vip支持

```php
#api\app\Console\Commands\CouponExpireDispose.php
public function handle()
    {
		Coupon::where('starttime',date('Y-m-d'))->where('state',Coupon::COUPON_STATE_NO)->where('vip', Coupon::COUPON_VIP_NO)->update(['state' => Coupon::COUPON_STATE_SHOW]);
    }
```

###### 优惠券后台添加增加vip支持

```php
#api\app\Http\Controllers\v1\Plugin\Admin\CouponController.php
/**
     * CouponCreate
     * @queryParam  vip int 是否vip专属
     */
    public function create(SubmitCouponRequest $request)
    {
    	...
        $Coupon->limit_get = $request->limit_get ?? 0;
        $Coupon->vip = $request->vip;
        if ($Coupon->vip == Coupon::COUPON_VIP_NO) {
            if (count($request->time) == 2) {
                $Coupon->starttime = $request->time[0];
                $Coupon->endtime = $request->time[1];
                if ($Coupon->starttime == date('Y-m-d')) {
                    $Coupon->state = Coupon::COUPON_STATE_SHOW;
                } else {
                    $Coupon->state = Coupon::COUPON_STATE_NO;
                }
            } else {
                return resReturn(0, '请选择领取时间', Code::CODE_WRONG);
            }
        }
    }
```

###### 优惠券模型增加vip支持

```php
#api\app\Models\v1\Coupon.php
class Coupon extends Model
{
    const COUPON_VIP_YES= 1; //VIP专属：是
    const COUPON_VIP_NO= 0; //VIP专属：否
}
```

###### 

## 如何更新插件

- 将最新版的插件下载，并替换老的插件，后台可一键升级

## 如何卸载插件

- 后台可一键卸载
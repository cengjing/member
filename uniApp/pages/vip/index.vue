<template>  
    <view class="container">  
		<view class="vip-top-bg"></view>
		<view class="vip-box">
			<view class="title">超级会员</view>
			<view class="cu-list grid no-border col-4">
				<view class="cu-item">
					<view class="cuIcon-crownfill vip-box-icon"></view>
					<text>会员尊贵标识</text>
				</view>
				<view class="cu-item">
					<view class="cuIcon-goodsfill vip-box-icon"></view>
					<text>购物折扣单单省</text>
				</view>
				<view class="cu-item">
					<view class="cuIcon-redpacket_fill vip-box-icon"></view>
					<text>每月优惠券领不停</text>
				</view>
				
				<view class="cu-item">
					<view class="cuIcon-deliver_fill vip-box-icon"></view>
					<text>全场包邮到家</text>
				</view>
			</view>
		</view>
		<!-- vip介绍-->
		<view class="vip-discounts">
			<view v-if="user.vip" class="title">您的vip将于{{user.vip_time}}过期</view>
			<view v-else class="title">现在开通 立送<text class="price">¥399</text>优惠券</view>
			<view class="cu-list grid no-border col-3 deadline">
				<view class="cu-item" v-for="(item, index) in list" :key="index">
					<view class="cu-item-ll" :class="{on:item.on}" @click="choosePackage(index)">
						<view class="ll-title">{{item.name}}</view>
						<view class="ll-price text-price">{{item.price}}</view>
						<view class="ll-original text-price">{{item.originalPrice}}</view>
					</view>
				</view>
			</view>
			
			<view class="tt">会员权益</view>
			<view class="cu-list menu-avatar">
				<view class="cu-item">
					<view class="cu-avatar bg-gray round lg cuIcon-crownfill"></view>
					<view class="content">
						<view class="text-black">尊贵标识</view>
						<view class="text-black text-sm flex">
							<view class="text-cut">
								开通超级会员后，享受尊贵标识
							</view>
						</view>
					</view>
				</view>
				<view class="cu-item">
					<view class="cu-avatar bg-gray round lg cuIcon-goodsfill"></view>
					<view class="content">
						<view class="text-black">超级会员全场{{discount}}折优惠</view>
						<view class="text-black text-sm flex">
							<view class="text-cut">
								享受全场超低折扣优惠价，全场{{discount}}折起
							</view>
						</view>
					</view>
				</view>
				<view class="cu-item">
					<view class="cu-avatar bg-gray round lg cuIcon-redpacket_fill"></view>
					<view class="content">
						<view class="text-black">优惠券月月领</view>
						<view class="text-black text-sm flex">
							<view class="text-cut">
								月月可以领优惠券，享受折上折优惠
							</view>
						</view>
					</view>
				</view>
				<view class="cu-item">
					<view class="cu-avatar bg-gray round lg cuIcon-deliver_fill "></view>
					<view class="content">
						<view class="text-black">全场包邮</view>
						<view class="text-black text-sm flex">
							<view class="text-cut">
								全场商品包邮到家，尽情购物
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="pay-type-list">
		
			<view class="type-item b-b" @click="changePayType('weixin')">
				<text class="icon yticon icon-weixinzhifu"></text>
				<view class="con">
					<text class="tit">微信支付</text>
					<text>推荐使用微信支付</text>
				</view>
				<label class="radio">
					<radio value="" color="#d56a18" :checked="payType == 'weixin'"/>
					</radio>
				</label>
			</view>
		</view>
		<view class=" flex flex-direction padding">
			<button class="cu-btn block bg-black round lg" v-if="!user.vip" @click="create()">立即开通会员(¥{{data.price|1000}})</button>
			<button class="cu-btn block bg-black round lg" v-else @click="edit()">续费(¥{{data.price|1000}})</button>
		</view>
		<view class="cu-modal" :class="modalName=='payHint'?'show':''">
			<view class="cu-dialog">
				<view class="cu-bar bg-white justify-end">
					<view class="content">提醒</view>
					<view class="action" @tap="hideModal">
						<text class="cuIcon-close text-red"></text>
					</view>
				</view>
				<view class="padding-xl">
					是否已完成支付
				</view>
				<view class="flex cu-bar bg-white justify-between">
					<button class="margin-left cu-btn line-green text-green" @tap="goBack">取消</button>
					<button class="margin-right cu-btn bg-green margin-left" @tap="goBack">已完成</button>
				</view>
			</view>
		</view>
    </view>  
</template>  
<script>  
	import Vip from '../../api/vip'
	import Pay from '../../api/pay'
	import User from '../../api/user'
	import {  
	    mapMutations  
	} from 'vuex';
    export default {
		components: {
		},
		data(){
			return {
				payType: 'weixin',
				list: [],
				modalName: null,
				discount: 0,
				data: {
					price: 0,
					id: 0
				},
				user: {}
			}
		},
		onLoad(){
			
		},
		onShow(){
			this.loginCheck()
			this.getList()
			this.getUser()
		},
        methods: {
			...mapMutations(['loginCheck']),
			getList(){
				const that = this
				Vip.getList({},function(res){
					that.list = res.deadline
					that.discount = res.discount*10
					that.list.forEach((item,index)=>{
						if(index === 0){
							that.list[index].on = true
							that.data = {
								price: item.price,
								id: index
							}
						}else{
							that.list[index].on = false
						}
					})
					
				})
			},
			getUser(){
				const that = this
				User.detail(function(res){
					that.user = res
				})
			},
			// 选择套餐
			choosePackage(index){
				this.list.forEach((item,index)=>{
					item.on = false
				})
				this.list[index].on = true
				this.data = {
					price: this.list[index].price,
					id: index
				}
			},
			hideModal(e) {
				this.modalName = null
			},
			goBack(){
				this.hideModal()
				this.getUser()
			},
			//开通vip
			create(){
				// #ifdef H5
				Pay.unifiedPayment({
					platform: this.payType,
					type: 'vip',
					trade_type: 'MWEB',
					id: this.data.id,
				},function(res){
					that.showModal('payHint')
					window.location.href = res.mweb_url
				})
				// #endif
				// #ifdef MP
				Pay.unifiedPayment({
					id: this.data.id,
					platform: this.payType,
					trade_type: 'JSAPI',
					type: 'vip'
				},function(res){
					uni.requestPayment({
						timeStamp: res.msg.timestamp,
						nonceStr: res.msg.nonceStr,
						package: res.msg.package,
						signType: res.msg.signType,
						paySign: res.msg.paySign,
						success(res) {
						  // console.log(res)
						  // 订阅消息
						  authMsg(['4iOC-HyjJeKK5HiYORcOtrKHvu2Ho1ScVF0aqP3KkzQ'])
						  if(!that.user.email && !that.user.wechat){
						  	uni.showModal({
						  	  title: '提示',
						  	  content: '您未打开通知功能，无法及时接收重要通知哦，是否现在去开启？',
						  	  success (res) {
						  	    if (res.confirm) {
						  	      uni.redirectTo({
						  	      	url: '/pages/set/message'
						  	      })
						  	    } else if (res.cancel) {
						  	      uni.redirectTo({
						  	      	url: '/pages/money/paySuccess'
						  	      })
						  	    }
						  	  }
						  	})
						  }else{
						  	uni.redirectTo({
						  		url: '/pages/money/paySuccess'
						  	})
						  }
						},
						fail(res) {
							that.$api.msg('支付失败，请重新支付')
						}
					})
					
				})
				// #endif
			},
			//续费vip
			edit(){
				Vip.create(this.data,function(res){
					that.$api.msg('续费成功');
					setTimeout(()=>{
						uni.navigateBack()
					}, 1000)
				})
			}
        }
    }  
</script>  
<style lang='scss'>
	page{
		background: #FFFFFF;
	}
	.vip-discounts{
		margin-top:200rpx;
		padding:30rpx;
		.deadline{
			.cu-item-ll{
				border-radius: 5px;
				border: 1px solid #e7e7e7;
				margin: 10rpx;
				padding: 50rpx 0 50rpx 0;
				.ll-title{
					font-size: 30rpx;
					font-weight: bold;
					padding-bottom: 10rpx;
				}
				.ll-price{
					font-size: 45rpx;
					font-weight: bold;
					padding-bottom: 10rpx;
				}
				.ll-original{
					text-decoration:line-through;
					color: #aaaaaa;
				}
			}
			.cu-item-ll.on{
				border-color:#d56a18;
				color: #d56a18;
				.ll-price{
					color: #783c0e;
				}
				.ll-original{
					color: #d56a18;
				}
			}
		}
		.title{
			text-align: center;
			color: #783c0e;
			font-size: 32rpx;
			font-weight: bold;
			.price{
				color: #e54d42;
				padding-left:4rpx;
				padding-right: 4rpx;
			}
		}
		.tt{
			padding-top:20rpx;
			padding-bottom: 20rpx;
			font-size: 32rpx;
			font-weight: bold;
		}
	}
	.vip-top-bg{
		background-color: #000000;
		height: 300rpx;
	}
	.vip-box{
		position: absolute;
		top:100rpx;
		border-radius:5px;
		background-image: linear-gradient(
		90deg
		, rgba(250, 169, 28, 1.0) 0%, rgba(243, 123, 29, 1.0) 100%);
		margin: 0 20rpx 0 20rpx;
		padding:40rpx;
		.title{
			font-size: 40rpx;
			color: #783c0e;
			font-weight: bold;
		}
		.cu-list.grid>.cu-item uni-text{
			color: #783c0e;
		}
		.vip-box-icon{
			color: #783c0e;
			font-size: 60rpx !important;
		}
		.cu-list.grid{
			background-color:transparent;
		}
	}
	.pay-type-list {
		margin-top: 20upx;
		background-color: #fff;
		padding-left: 60upx;
		
		.type-item{
			height: 120upx;
			padding: 20upx 0;
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding-right: 60upx;
			font-size: 30upx;
			position:relative;
		}
		
		.icon{
			width: 100upx;
			font-size: 52upx;
		}
		.icon-erjiye-yucunkuan {
			color: #fe8e2e;
		}
		.icon-weixinzhifu {
			color: #36cb59;
		}
		.icon-alipay {
			color: #01aaef;
		}
		.tit{
			font-size: $font-lg;
			color: $font-color-dark;
			margin-bottom: 4upx;
		}
		.con{
			flex: 1;
			display: flex;
			flex-direction: column;
			font-size: $font-sm;
			color: $font-color-light;
		}
	}
</style>

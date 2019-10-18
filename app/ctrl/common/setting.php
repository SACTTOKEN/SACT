<?php

/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-14 11:38:07
 * Desc: 配置类
 */

namespace app\ctrl\common;

use app\service\setting as setting_S;

class setting extends setting_S
{

	/* 
	添加配置
	$this->C('标题','iden标识','默认值','类型','分类cate','帮助','验证1必填2数字'); 
	类型	
	text		文本框
	switch		开关
	account		账户多选
	balance		账户单选
	checkbox	多选框
	radio		单选框
	timepicker	时间范围
	upload		上传
	coupon		送红包
	*/
	public function config()
	{
		$this->C('支持账户','transfer_balance','','account','transfer','支持账户',0);
		$this->C('支持账户','imred_balance','','account','imred','支持账户',0);
		$this->C('每日兑币总量', 'exchange_day_sum', '100', 'text', 'block', '币币账户兑法币账户', 1);
		$this->C('镇代理奖励', 'agent_town', '10', 'text', 'agent', '千分比', 2);
		$this->C('代理中心名称', 'agent_name', '运营', 'text', 'agent', '代理中心名称', 1);

		$this->C('积分兑换比例','jfdhbl','8','text','jfyx','多少积分换1RMB',2);
		$this->C('推荐送积分','tjsjf','8','text','jfyx','',2);
		$this->C('注册送积分','zcsjf','8','text','jfyx','',2);
		//$this->C('购物送积分','gwsjf','0','switch','jfyx','',2);

		$this->C('聚合OPENID','juhe_openid','','text','juhe','',0);
		$this->C('话费APPKEY','juhe_hf_appkey','','text','juhe','',0);
		$this->C('流量APPKEY','juhe_ll_appkey','','text','juhe','',0);
		$this->C('油卡APPKEY','juhe_yk_appkey','','text','juhe','',0);
		$this->C('聚合是否开放','juhe_open','0','switch','juhe','',0);
		$this->C('支付比例','juhe_fee','900','text','juhe','千分比 如值900是打九折',0);

		//拼团
		$this->C('拼团广告','group_banner','','upload','group','拼团列表头部广告',0);

		//现金红包
		$this->C('前台是否开启微信自动提现','auto_mobile_wx_withdraw','','switch','yetx','',0);
		$this->C('后台是否开启微信自动提现','auto_admin_wx_withdraw','','switch','yetx','',0);
		$this->C('微信提现方式','wx_withdraw_type','红包发放','radio','yetx','企业付款|红包发送',0); 

		//平台密匙
		$this->C('平台密匙','admin_key','','text','hide','平台密匙',1);

		//小程序
		$this->C('小程序AppId','applets_appid','','text','applets','');
		$this->C('小程序AppSec','applets_appsec','','text','applets','');
		
		
		//互转限制
		$this->C('互转是否只能一条线','transfer_ytx','1','switch','transfer','互转是否只能一条线',0);
		
		//入单设置-定制设置
		$this->C('入单最低数量','dzrd_zdsl','100','text','made','入单最低数量',2);
		$this->C('入单倍数数量','dzrd_bssl','100','text','made','入单倍数数量',2);
		$this->C('入单平台币千分比','dzrd_ptbqfb','200','text','made','入单-平台币千分比',2);
		$this->C('入单-出局千分比','dzrd_cjqfb','3000','text','made','入单-出局千分比',2);
		$this->C('入单-每天静态千分比','dzrd_jtsf','10','text','made','入单-每天静态千分比',2);
		
		$this->C('入单奖励平台币千分比','rdjl_ptb','10','text','made','入单奖励平台币千分比',2);
		
		$this->C('动态释放-USDT千分比','dtsf_usdt','10','text','made','动态释放-USDT千分比',2);
		$this->C('动态释放-平台币千分比','dtsf_ptb','5','text','made','动态释放-平台币千分比',2);
		
		$this->C('攻打星球-最低飞船','gdxq_zdfc','10','text','made','攻打星球-最低飞船',2);
		
		$this->C('攻打星球-获胜奖励千分比','gdxq_jlqfb','50','text','made','攻打星球-获胜奖励千分比',2);
		
		$this->C('飞船价格-USDT','fcjg_usdt','0.1','text','made','飞船价格-USDT',1);
		$this->C('飞船兑换USDT-手续费','fcdh_usdtsxf','50','text','made','飞船兑换USDT-手续费',2);
		$this->C('飞船兑换平台币-手续费','fcdh_ptbsxf','50','text','made','飞船兑换平台币-手续费',2);
		
		$this->C('入单限制数量','dzrd_xzsl','2','text','made','入单限制数量',2);
		
		$this->C('定制分红-开启测试','ocfh_kqcs','0','switch','made','开启分红-每小时发放一次',0);
		
		//大盘交易
		$this->C('大盘交易价格浮动','transation_price','100','text','transation','千分币',2);
		
		
		$this->C('攻打黄金战舰-最低数量','hjzj_zdsl','100','text','made','攻打黄金战舰-最低数量',2);
		$this->C('攻打黄金战舰-倍数','hjzj_bs','100','text','made','攻打黄金战舰-倍数',2);
		$this->C('攻打黄金战舰-手动返航千分比','hjzj_sdqfb','30','text','made','攻打黄金战舰-手动返航千分比',2);
		$this->C('攻打黄金战舰-自动返航千分比','hjzj_zdqfb','50','text','made','攻打黄金战舰-自动返航千分比',2);
		
		
		$this->C('存储平台币名称', 'coin_storage_title', '游戏SATE', 'text', 'coin_trade', '存储平台币名称', 1);
		$this->C('待释放平台币名称', 'viprd_ptb_title', '待释放SATE', 'text', 'coin_trade', '待释放平台币名称', 1);
		$this->C('待释放USDT', 'viprd_usdt_title', '待释放USDT', 'text', 'coin_trade', '待释放USDT', 1);
		
		
		
		$this->C('RH共冲池总量', 'rhgc_zsl', '10000000', 'text', 'made', 'RH共冲池总量', 2);
		$this->C('RH共冲初始价格', 'rhgc_csjg', '1', 'text', 'made', 'RH共冲初始价格', 2);
		$this->C('RH共冲涨价基数', 'rhgc_zjjs', '10000', 'text', 'made', 'RH共冲涨价基数', 2);
		$this->C('RH共冲涨价价格', 'rhgc_zjjg', '0.01', 'text', 'made', 'RH共冲涨价价格', 2);
		$this->C('RH共冲进基金联盟A千分比', 'rhgc_jjlma', '300', 'text', 'made', 'RH共冲进基金联盟A千分比', 2);
		
		$this->C('DAPP-开启测试','dapp_kqcs','1','switch','made','DAPP-开启测试',0);
		
		$this->C('是否开启-飞船闪兑','sfkq_fcsd','1','switch','made','是否开启-飞船闪兑',0);
		
		
	
	}


	/* 
	$this->cj('表名','注释');
	$this->add('表','字段名','类型','注释','默认值'); 
	类型 	v字符串		i数字	d金额	t文本	s短数字
	*/
	public function table()
	{
		$this->cj('sms', '测试');
		$this->add('sms', 'quhao', 'i', '区号', '86');
		$this->add('user_attach', 'noob_coupon', 's', '新手红包 0:未领 1:已领', '0');

		//省市区加镇
		$this->add('iorder', 'mail_town', 'v', '收货镇', '');
		$this->add('mail', 'sender_town', 'v', '发件人镇', '');
		$this->add('supplier', 'town', 'v', '镇', '');
		$this->add('user', 'town', 'v', '镇', '');
		$this->add('user', 'agent_town', 'v', '代理镇', '');
		$this->add('user_address', 'town', 'v', '镇', '');
		$this->add('user_attach', 'shop_town', 'v', '所在镇', '');
		$this->add('agent', 'town', 'v', '镇', '');

		//限时抢购
		$this->cj('rob_time','抢购时区');
		$this->add('rob_time', 'begin_time', 'i', '开始时间');
		$this->add('rob_time', 'end_time', 'i', '结束时间');
		$this->add('product', 'time_id', 'i', '限时抢购时区ID');
		$this->add('product', 'discount_rob', 'd', '限时抢购折扣');
		$this->add('product', 'discount_limit', 'i', '限时抢购数量');
		//积分兑换
		$this->add('product', 'score_rob', 'd', '积分兑换所需积分');
		$this->add('iorder', 'score_rob', 'd', '积分兑换所需积分');
		$this->add('iorder_product', 'score_rob', 'd', '积分兑换所需积分');

		//聚合充值
		$this->cj('juhe_recharge','聚合充值');
		$this->add('juhe_recharge','uid','i','');
		$this->add('juhe_recharge','money','d','流量充值卡ID');
		$this->add('juhe_recharge','cardname','v','充值名称');
		$this->add('juhe_recharge','game_money','i','充值面额200M流量');
		$this->add('juhe_recharge','game_userid','v','充值对象：手机号');
		$this->add('juhe_recharge','game_state','i','充值状态:0充值中 1成功 9撤销，刚提交都返回0');
		$this->add('juhe_recharge','oid','v','自定义单号');
		$this->add('juhe_recharge','types','v','话费/流量/加油卡');
		$this->add('juhe_recharge','juhe_oid','v','聚合交易单号');
		$this->add('juhe_recharge','is_pay','s','是否支付 0:未支付 1：已支付');
		$this->add('juhe_recharge','card_id','v','流量充值卡ID');
		$this->add('juhe_recharge','gas_tel','v','加油持卡人手机号');
		$this->add('juhe_recharge','pay','v','充值方式');
		
		//拼团
		$this->add('cart', 'group_types', 's', '是否单人团0否1是');
		$this->add('cart', 'group_id', 'i', '拼团ID');

		$this->add('product', 'group_people', 'i', '拼团数量');
		$this->add('product', 'group_discount', 'd', '拼团折扣');
		$this->add('product', 'group_time', 'i', '拼团时间');
		$this->add('product', 'group_face', 's', '是否团长面单');

		$this->cj('groups','拼团');
		$this->add('groups', 'uid', 'i', '用户ID');
		$this->add('groups', 'pid', 'i', '商品ID');
		$this->add('groups', 'head_oid', 'i', '团长订单ID');
		$this->add('groups', 'oid', 'i', '订单ID');
		$this->add('groups', 'status', 'i', '状态0拼团未成功1拼团成功2拼团失败');
		$this->add('groups', 'now_people', 'i', '当前数量');
		$this->add('groups', 'group_people', 'i', '拼团数量');
		$this->add('groups', 'group_discount', 'd', '拼团折扣');
		$this->add('groups', 'group_time', 'i', '拼团时间');
		$this->add('groups', 'end_time', 'i', '拼团时间');
		$this->add('groups', 'group_face', 's', '是否团长面单');
		$this->add('groups', 'is_pay', 's', '是否支付');



		//导入会员
		$this->add('user','rating_cn','v','会员等级中文');
		$this->add('user','tid_cn','v','推荐人用户名');
		$this->add('user','stage','v','导入的期数');
		$this->add('user','supply','d','供货款');

		//平台账户
		$this->cj('admin_money','平台账户');
		$this->add('admin_money', 'money', 'd', '平台账户金额');

		//商品来源
		$this->add('product','ly','v','来源');
		$this->add('product','video_pic','v','视频第一帧');

		$this->add('product','come_sid','i','视频第一帧');
		$this->add('product','come_pid','i','视频第一帧');

		//现金红包发送记录表
		$this->cj('redpack_log','现金红包发送记录表');
		$this->add('redpack_log','uid','i','用户ID');
		$this->add('redpack_log','money','d','申请金额');
		$this->add('redpack_log','real_money','d','实提金额');
		$this->add('redpack_log','balance_type','v','金额类型');
		$this->add('redpack_log','oid','v','订单号');
		$this->add('redpack_log','return_oid','v','回调订单号');
		$this->add('redpack_log','pay_way','v','提现方式');
		$this->add('redpack_log','openid','v','只有微信提现有');
		$this->add('redpack_log','ip','v','ip');


		//课程分类表
		$this->cj('lesson_cate','课程分类表');
		$this->add('lesson_cate','cate_name','v','类别');
		$this->add('lesson_cate','cate_pic','v','类别LOGO');

		//课程表
		$this->cj('lesson','课程表');
		$this->add('lesson','cid','i','课程类别ID');
		$this->add('lesson','title','v','课程名');
		$this->add('lesson','piclink','v','课程封面');
		$this->add('lesson','price','d','实价');
		$this->add('lesson','market_price','d','市场价');
		$this->add('lesson','video','v','视频');
		$this->add('lesson','content','v','简介');

		
		//课程节数表
		$this->cj('lesson_stage','课程表集');
		$this->add('lesson_stage','lesson_id','i','属于那个课程ID');
		$this->add('lesson_stage','title','v','课程名');
		$this->add('lesson_stage','stage','v','课程编号');
		$this->add('lesson','video','v','视频');
		$this->add('lesson','is_free','s','0:免费 1:收费', '1');
		$this->add('lesson','hit','i','点击量');


		//插件
		$this->add('plugin','video','v','视频');

		//卡密
		$this->add('iorder_product','card','v','卡密');

		//客服消息
		$this->cj('service_msg','客服消息');	
		$this->add('service_msg','admin_id','i','管理员ID');	
		$this->add('service_msg','rating','i','等级');	
		$this->add('service_msg','rating_cn','v','等级名称');	
		$this->add('service_msg','msg','v','客服消息');	
		$this->add('service_msg','success_num','i','发送成功数');	
		$this->add('service_msg','fail_num','i','发送失败数');	


		//分红
		
		$this->add('user','is_stock','s','每日分红');	
		$this->add('user','day_dividend','d','每日分红奖励');	
		
		
		
		
		//VIP等级
		$this->cj('vip_rating','VIP等级');	
		$this->add('vip_rating','title','v','等级名称');	
		$this->add('vip_rating','piclink','v','等级图片');
		$this->add('vip_rating','ljrd','i','累计入单');	
		$this->add('user','vip_rating','i','VIP等级',1);	
		$this->add('vip_rating','dtsf_usdt','i','释放千分比');	
		
		//节点等级
		$this->cj('jddj_rating','节点等级');	
		$this->add('jddj_rating','title','v','等级名称');	
		$this->add('jddj_rating','zt_num','i','直推人数，条件');	
		$this->add('jddj_rating','sxyj','i','伞下业绩，条件');	
		$this->add('jddj_rating','ztjd_num','i','直推节点人数，条件');	
		$this->add('jddj_rating','ztjd_id','i','直推节点等级，条件');	
		$this->add('jddj_rating','jlqfb','i','伞下所有收益');	
		$this->add('user','jddj_rating','i','节点等级',1);	
		$this->add('dtsf_usdt','title','v','等级名称');	
		$this->add('jddj_rating','dtsf_usdt','i','释放千分比');	
		
		
		//见点奖励
		$this->cj('user_dtjdj','动态见点奖');	
		$this->add('user_dtjdj','zt_num','i','直推人数');	
		$this->add('user_dtjdj','team_award','i','奖励千分比');	
		
		//团队奖励
		$this->cj('user_jttdj','静态团队奖');	
		$this->add('user_jttdj','zt_num','i','直推人数');	
		$this->add('user_jttdj','team_award','i','奖励千分比');	
		
		
		//动态指定日期释放
		$this->cj('user_dtsf','动态指定释放');	
		$this->add('user_dtsf','zt_num','i','周几释放');	
		$this->add('user_dtsf','dtsf_usdt','i','USDT释放比例');	
		$this->add('user_dtsf','dtsf_ptb','i','平台币释放比例');	
		
		
		//星球设置
		$this->cj('mxqsz','M星球设置');	
		$this->add('mxqsz','title','v','等级名称');
		$this->add('mxqsz','piclink','v','星球图片');
		$this->add('mxqsz','ljrd','i','购买金额');
		$this->add('mxqsz','mtsc','i','每天生产');
		$this->add('mxqsz','gmid','i','购买最低VIP等级');
		
		
		//星球团队奖
		$this->cj('user_xqtdj','M星球团队奖');	
		$this->add('user_xqtdj','zt_num','i','直推人数');	
		$this->add('user_xqtdj','team_award','i','奖励千分比');	
		
		
		//攻打星球设置
		$this->cj('gdxqsz','攻打星球设置');	
		$this->add('gdxqsz','title','v','星球名称');
		$this->add('gdxqsz','piclink','v','星球图片');
		
		
		//星球攻打结果
		$this->cj('xqgdjg','星球攻打结果');	
		$this->add('xqgdjg','cdate','i','攻打日期');
		$this->add('xqgdjg','jcjg','i','攻打结果');
		
		
		//会员入单累计金额-累计额度，，累计已发放。
		$this->add('user','viprd_ljje','d','入单累计金额');
		$this->add('user','viprd_zgje','d','入单单次最高金额');
		$this->add('user','viprd_ljed','d','入单累计额度');
		$this->add('user','viprd_ysf','d','入单累计额度');
		$this->add('user','viprd_wsf','d','入单奖励-未释放');
		$this->add('user','viprd_usdt','d','动态奖励-USDT未释放');
		$this->add('user','viprd_ptb','d','动态奖励-平台币未释放');
		$this->add('user','is_dtjlsf','s','是否执行动态未释放');	
		$this->add('user','viprd_jtsf','d','今天VIP静态释放');
		
		
		$this->add('user_attach','vip_buy','d','累计入单金额');
		$this->add('user_attach','vip_ysales','d','一级入单金额');
		$this->add('user_attach','vip_sales','d','累计团队入单金额');
		$this->add('user_attach','vip_yvip','i','VIP直推人数');
		$this->add('user_attach','vip_zvip','i','VIP总人数');
		
		$this->add('user_attach','mxq_buy','d','累计入单金额');
		$this->add('user_attach','mxq_ysales','d','一级入单金额');
		$this->add('user_attach','mxq_sales','d','累计团队入单金额');
		
		$this->add('user','mxq_buy','d','累计入单金额');
		
        //VIP入单记录
		$this->cj('vip_order','VIP入单记录');	
		$this->add('vip_order','oid','v','自定义单号');
		$this->add('vip_order','uid', 'i', '用户ID');
		$this->add('vip_order','vid', 'i', 'VIPID');
		$this->add('vip_order','v_title', 'v', 'VIPID');
		$this->add('vip_order','v_pic', 'v', 'VIP图片');
		$this->add('vip_order','m_money','d','入单金额');
		$this->add('vip_order','m_money1','d','入单金额-使用usdt');
		$this->add('vip_order','m_money2','d','入单金额-使用平台币');
		$this->add('vip_order','m_money3','d','入单金额-使用usdt存储资产');
		$this->add('vip_order','m_money4','d','入单金额-使用平台币存储资产');
		$this->add('vip_order','cj_money','d','出局金额');
		$this->add('vip_order','sc_jtsf','d','静态已释放');
		$this->add('vip_order','rd_time','i','入单时间');
		$this->add('vip_order','sf_time','i','最后释放时间');
		$this->add('vip_order','cd_time','i','出局时间');
		$this->add('vip_order','status','i','状态,0未出局,1已出局');
		$this->add('vip_order','bc_money','d','本次释放金额');
		
		
		
		//会员入单累计金额-累计额度，，累计已发放。
		$this->add('user','mxq_fcsl','d','飞船数量');
		$this->add('user','mxq_jlsl','d','累计获得飞船数量');
		$this->add('user','mxq_cysl','i','累计持有数量');
		$this->add('user','mxq_bcjtsl','d','本次静态奖励数量');
		$this->add('user','mxq_bctdsl','d','本次团队奖励数量');
		$this->add('user','mxq_bcgdsl','d','本次攻打奖励数量');
		
		//M星球入单记录
        $this->cj('mxq_order','M星球入单记录');	
		$this->add('mxq_order','oid','v','自定义单号');
		$this->add('mxq_order','uid', 'i', '用户ID');
		$this->add('mxq_order','mid', 'i', 'M星球ID');
		$this->add('mxq_order','m_title', 'v', 'M星球标题');
		$this->add('mxq_order','m_pic', 'v', 'M星球图片');
		$this->add('mxq_order','m_money','d','入单金额');
		$this->add('mxq_order','m_money1','d','入单金额-平台币');
		$this->add('mxq_order','cj_money','d','出局金额-平台币');
		$this->add('mxq_order','sc_jtsf','d','静态已释放');
		$this->add('mxq_order','rd_time','i','入单时间');
		$this->add('mxq_order','sf_time','i','最后释放时间');
		$this->add('mxq_order','cd_time','i','出局时间');
		$this->add('mxq_order','status','i','状态,0未退出,1已退出');
		$this->add('mxq_order','bc_money','d','本次释放金额');
	
		
		//攻打星球记录
		$this->cj('gdxq_order','攻打星球记录');	
		$this->add('gdxq_order','oid','v','自定义单号');
		$this->add('gdxq_order','uid', 'i', '用户ID');
		$this->add('gdxq_order','gid', 'i', '星球ID');
		$this->add('gdxq_order','g_title', 'v', 'M星球标题');
		$this->add('gdxq_order','g_pic', 'v', 'M星球图片');
		$this->add('gdxq_order','g_money','d','攻打时飞船');
		$this->add('gdxq_order','g_money1','d','结算时用户飞船');
		$this->add('gdxq_order','g_money2','d','结算飞船数量');
		$this->add('gdxq_order','rd_time','i','攻打时间');
		$this->add('gdxq_order','sf_time','i','结算时间');
		$this->add('gdxq_order','status','i','状态,0未结算,1攻打成功,2攻打失败');
		$this->add('gdxq_order','bc_money','d','本次奖励数量'); //已最低飞船数量计算
		$this->add('gdxq_order','is_dtjlsf','s','是否执行奖励');	
		
		//每天运行分红
		$this->cj('run_oc','每天运行分红');	
		$this->add('run_oc','status', 'i', '分红状态');
		$this->add('run_oc','times', 'i', '分红时间');
		
		
		//M星球入单记录
        $this->cj('user_fxtj','奖励统计');	
		$this->add('user_fxtj','uid', 'i', '用户ID');
		$this->add('user_fxtj','tid', 'i', '推荐ID');
		$this->add('user_fxtj','viprd_jtsf','d','今天VIP静态释放');
		$this->add('user_fxtj','is_dtjlsf','s','是否执行奖励');	
		$this->add('user_fxtj','mxq_bcjtsl','d','本次飞船奖励数量');
		$this->add('user_fxtj','vip_yvip','i','VIP等级直推人数');
		
		$this->add('user_fxtj','mxq_cysl','i','累计持有数量');
		$this->add('user_fxtj','mxq_yvip','i','M星球直推购买人数');
		$this->add('user_fxtj','vip_money','d','VIP团队奖释放金额');
		$this->add('user_fxtj','viprd_wsf','d','VIP未释放额度');
		$this->add('user_fxtj','mxq_money','d','M星球奖励释放金额');
		
		for ($x=1; $x<=20; $x++) {
			$this->add('user_fxtj','vip_money'.$x,'d',$x.'代奖励总额');
		} 
		
		for ($x=1; $x<=10; $x++) {
			$this->add('user_fxtj','mxq_money'.$x,'d',$x.'代奖励总额');
		} 
		
		
		$this->add('user_fxtj','jddj_rating','i','会员节点等级');
		$this->add('user_fxtj','jddj_sxzgid','i','伞下最高节点等级');
		$this->add('user_fxtj','vip_sales','d','累计团队入单金额');
		$this->add('user_fxtj','jlqfb','i','节点奖励比例');
		
		
		$this->add('user_fxtj','pid','i','节点匹配ID');
		$this->add('user_fxtj','tdj_yfbl','i','节点级差已发比例');
		$this->add('user_fxtj','sfypp_tdj','i','是否已匹配节点ID');
		$this->add('user_fxtj','jl_sxxzcb_qfb1','i','本次匹配级差比例');
		$this->add('user_fxtj','ljjdsy','d','累计节点收益');
	    //$this->add('user_fxtj','ppid','i','节点匹配ID');
		$this->add('user_fxtj','is_jdsysf','s','节点收益是否已发放');	
		
		$this->add('user_gx','jddj_rating','i','会员节点等级');
		$this->add('user_gx','vip_rating','i','vip等级');
		
		
		//每天运行分红
		$this->cj('run_oc1','每天运行攻打星球');	
		$this->add('run_oc1','status', 'i', '分红状态');
		$this->add('run_oc1','times', 'i', '攻打时间');
		
		$this->cj('bbdh','币币互转');	
		$this->add('bbdh','uid', 'i', '用户ID');
		$this->add('bbdh','oid','v','自定义单号');
		$this->add('bbdh','cate','v','资金类型');
		$this->add('bbdh','money','d','资金数量');
		$this->add('bbdh','actual','d','到账数量');
		$this->add('bbdh','fee','d','手续费数量');
		$this->add('bbdh','status', 'i', '状态');
		
		
		//攻打星球记录
		$this->cj('hjzj_order','攻打黄金战舰记录');	
		$this->add('hjzj_order','oid','v','自定义单号');
		$this->add('hjzj_order','uid', 'i', '用户ID');
		$this->add('hjzj_order','money','d','攻打数量');
		$this->add('hjzj_order','rd_time','i','攻打时间');
		$this->add('hjzj_order','sf_time','i','结算时间');
		$this->add('hjzj_order','status','i','状态,0未结算,1手动返航,2自动返航');
		$this->add('hjzj_order','bc_money','d','本次奖励数量'); //已最低飞船数量计算
		
		
		//每天运行分红
		$this->cj('run_oc2','每天运行黄金战舰');	
		$this->add('run_oc2','status', 'i', '运行状态');
		$this->add('run_oc2','times', 'i', '运行时间');
		
		
	    $this->add('user','USDT_storage','d','共冲USDT');
		$this->add('user','USDT_KY','d','可用USDT');
		$this->add('user','LMJJ','d','联盟基金');
		$this->add('user','XJJJ','d','星际基金');
		$this->add('user','LMJJA','d','联盟基金A');
		$this->add('user','LMJJB','d','联盟基金B');
		
		$this->add('user','LMJJC','d','联盟基金C');
		$this->add('user','sactloop','d','SACTLOOP');
		
		$this->add('user','jtsf_coin_gc','d','今天释放ATA共冲'); 
		$this->add('user','jtsf_usdt_gc','d','今天释放USDT共冲'); 
		$this->add('user','is_mtgcsf','s','是否执行共冲释放');	
		//优先释放到可用USDT-释放出去值为0--	coin_storage
		//判断价值共冲USDT释放到可用USDT--重置释放值
		//
			
			
			
		$this->cj('rhgc_tjb','RH共冲统计表');	
		$this->add('rhgc_tjb','money','d','累计减少数量');
		$this->add('rhgc_tjb','money1','d','累计星际基金');
		$this->add('rhgc_tjb','money2','d','联盟基金A');
		
		
		
		$this->cj('rhgc','RH共冲记录');	
		$this->add('rhgc','uid', 'i', '用户ID');
		$this->add('rhgc','oid','v','自定义单号');
		$this->add('rhgc','cate','v','资金类型');
		$this->add('rhgc','money','d','资金数量');
		$this->add('rhgc','actual','d','到账数量');
		$this->add('rhgc','fee','d','兑换单价');
		$this->add('rhgc','status', 'i', '状态');
		
		$this->add('transaction','buylx', 'i', '购买类型');
		

	}

}

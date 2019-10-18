<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 */
namespace app\validate;

class ActivityValidate extends BaseValidate
{
    protected $rule = [
        'discount_rob' => 'require|number',
        'discount_limit'  => 'require|isPositiveInteger',
        'time_id'  => 'require|isPositiveInteger',
        'score_rob' => 'require|number',
        'group_people' => 'require|isPositiveInteger',
        'group_discount' => 'require|isMoney',
        'group_time' => 'require|isPositiveInteger',
        'group_face' => 'require|in:0,1',
    ];

    protected $message = [
        'discount_rob'     => '折扣必须是数字,不打折填10',
        'discount_limit' => '请填写限时抢购限购数量',
        'time_id' => '请选择限时区间',
        'score_rob'   => '兑换的积分必须',
        'group_people' => '请输入拼团人数',
        'group_discount' => '请输入拼团价格',
        'group_time' => '请输入拼团时间',
        'group_face' => '请选择是否团长面单',
    ];

    protected $scene  = [
        'limited_time'  =>  ['discount_rob','discount_limit','time_id'],//限时特惠
        'redeem' =>  ['score_rob'],//积分兑换
        'group' =>['group_people','group_discount','group_time','group_face'],//拼团
    ];
}

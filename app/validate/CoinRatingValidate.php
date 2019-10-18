<?php
namespace app\validate;

class CoinRatingValidate extends BaseValidate
{
    protected $rule = [
        'title'    => 'require|chsDash', 
        'zt_num'    => 'number', 
        'td_num'    => 'number', 
        'coin_buy'    => 'number', 
        'title'    => 'require|chsDash', 
        'trading_fee'    => 'number', 
        'send_lv'    => 'number', 
        'direct_award'    => 'number', 
        'direct_rating'    => 'number', 
        'direct_rating_number'    => 'number', 
    ];

    protected $message = [
        'title.require'    => '请提交等级标题', 
        'title.chsDash'    => '只能是汉字、字母、数字和下划线_及破折号-', 
        'zt_num'    => '直推人数只能是正整数', 
        'td_num'    => '团队人数只能是正整数', 
        'coin_buy'    => '累计消费只能是金额类型', 
        'trading_fee'    => '手续费比例只能是正整数', 
        'send_lv'    => '请选择矿机', 
        'direct_award'    => '请填写直推奖', 
        'direct_rating'    => '请选择直推等级', 
        'direct_rating_number'    => '请填写直推等级人数', 
    ];


	protected $scene  = [
        'add'  =>  ['title'],
        'edit'  =>  ['title','zt_num','td_num','coin_buy','trading_fee','send_lv','direct_award','direct_rating','direct_rating_number'],
    ];

}

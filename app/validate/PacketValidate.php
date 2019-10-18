<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 09:34:59
 * Desc: 红包验证
 */


namespace app\validate;

class PacketValidate extends BaseValidate
{
    protected $rule = [
        'id'         => 'require',
        'title'   => 'require',
        'money'       => 'require|isMoney',
        'cdn_xfm'  => 'require|isMoney',
        'cdn_pid'  =>'number',
        'cdn_sid'  =>'number',
        'limit_num'  =>'number',
        'jf_change'  =>'number',
        'full_num' =>'number',
        'lifetime'  =>'require|isPositiveInteger',

     ];

    protected $message = [
        'money.require'      => '请填写金额',
        'money.isMoney'      => '金额必须是正整数',
        'cdn_xfm.require'    => '请填写累计消费满',
        'cdn_xfm.isMoney'    => '累计消费金额必须是正整数',
        'cdn_pid'    => '指定商品ID为数字',
        'cdn_sid'    => '指定商家ID为数字', 
        'limit_num'  => '每人限量格式为数字',
        'jf_change'  => '积分兑换格式为数字',
        'limit_lv'   => '等级格式为数字',
        'full_num'   => '发放总数为数字',
        'lifetime'   => '过期天数必须',
    ];

    protected $scene  = [
        'scene_find'   =>  ['id'],
        'scene_del'    =>  ['id'],
        'scene_add'    =>  ['title'=>'require|unique:packet','money','cdn_xfm','cdn_pid','cdn_sid','limit_num','jf_change','limit_lv','full_num','lifetime'],
        'scene_edit'   =>  ['id'],
        'scene_checkID' =>  ['id_str'],
        'scene_saveedit' => ['id','money','cdn_xfm','cdn_pid','cdn_sid','limit_num','jf_change','limit_lv','full_num'],
    ];

     
}

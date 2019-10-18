<?php
namespace app\validate;

class AllsearchValidate extends BaseValidate
{
    protected $rule = [
        'cate_id'               => 'number',      //商品分类
        'sid_cn'                => 'chsAlpha',  //商户名
        'show'                  => 'number',   //1已上架 0下架 仓库中
        'stock'                 => 'number',  //已售空0
        'is_check'              => 'number', //1已审核 0未审核  

        'oid'                   => 'chsDash', //订单号
        'uid'                   => 'number',
        'sid'                   => 'number',
        'mail_name'             => 'chsAlpha',
        'mail_address'          => 'chsDash',
        'mail_tel'              => 'number|max:11',
        'pay'                   => 'chs',
        'mail_oid'              => 'alphaNum',
        'mail_province'         => 'chs',
        'created_time_begin'    => 'number',
        'created_time_end'      => 'number',
        'pay_time_begin'        => 'number',
        'pay_time_end'          => 'number',
        'complete_time_begin'   => 'number',
        'complete_time_end'     => 'number',
        'status'                => 'chsDash',
        'username'              => 'chsDash',
        'rating'                => 'number',
        'province'              => 'chs',
        'city'                  => 'chs',
        'area'                  => 'chs',
        'town'                  => 'chs',
        'tel'                   => 'chsDash',
        'other_username'        => 'chsDash',
        'types'    => 'number',
    ];



    protected $scene  = [
        'scene_product'   =>  ['cate_id','title','sid_cn','show','stock','is_check','types'],     
    ];
}

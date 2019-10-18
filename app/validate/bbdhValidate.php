<?php
namespace app\validate;

class bbdhValidate extends BaseValidate
{
    protected $rule = [
        'iden'       => 'require',
      //  'username'      => 'require',
       // 'im'      => 'require',
       // 'oid'      => 'require',
        'money'      => 'require|isMoney',
      //  'content'      => 'chsDash|max:10',
      //  'password'      => 'require',
    ];


    protected $message = [
        'iden'     => '币种不存在',
       // 'username'    =>  '请输入转账账号',
      //  'im'    =>  '请输入IM账号',
      //  'oid'    =>  '请上传订单号',
        'money.require'      =>  '请填写兑换数量',
        'money.isMoney'      =>  '兑换数量必须是正整数',
     //   'content.chsDash'      =>  '留言只能数字汉字英文',
      //  'content.max'      =>  '留言长度不能超过8个字符',
      //  'password'      =>  '请输入支付密码',
    ];

    protected $scene  = [
        'saveadd'  =>  ['iden','money','password'],
       // 'saveadd'  =>  ['iden','money'],
       // 'red_send'  =>  ['iden','money','password'],
      //  'red_open'  =>  ['oid'],
    ];

}

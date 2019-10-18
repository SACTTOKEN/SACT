<?php
namespace app\validate;

class DelValidate extends BaseValidate
{
    protected $rule = [
        'id_str' => 'require|checkcartid',  
    ];

  

}

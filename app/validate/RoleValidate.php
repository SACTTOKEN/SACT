<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 */
namespace app\validate;

class RoleValidate extends BaseValidate
{
    //'role_name' => 'require|unique',
    protected $rule = [
        'role_name' => 'require|unique:admin_role',
        'role_con' => 'require',
    ];

    protected $message = [
        'role_name'    => '角色名称必须',  
        'role_con'    => '权限ID串必须',      
    ];






}

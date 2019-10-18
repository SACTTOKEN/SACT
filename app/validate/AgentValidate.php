<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-02-25 11:50:38
 * Desc: 供应商验证
 */


namespace app\validate;

class AgentValidate extends BaseValidate
{
    protected $rule = [
        'title'    => 'require',
        'province'    => 'require',
        'city'    => 'require',
        'area'    => 'require',
        'town'    => 'require',
        'add'    => 'require',
        'name'    => 'require',
        'card'    => 'require',
        'cardpositive'    => 'require',
        'cardnegative'    => 'require',
        'license'    => 'require',
        'types'    => 'require|in:1,2,3,4',
    ];

    protected $message = [
        'title'    => '请提交公司名称',
        'province'    => '请提交区域',
        'city'    => '请提交区域',
        'area'    => '请提交区域',
        'town'    => '请提交区域',
        'add'    => '请提交地址',
        'name'    => '请提交法人姓名',
        'card'    => '请提交法人身份证号',
        'cardpositive'    => '请上传法人身份证正面',
        'cardnegative'    => '请上传法人身份证反面',
        'license'    => '请上传营业执照',
        'types'    => '请选择代理类型',
    ];


    protected $scene  = [
        'agent_company_title'  =>  ['title'],
        'agent_company_region'  =>  ['types'],
        'agent_company_add'  =>  ['add'],
        'agent_company_name'  =>  ['name'],
        'agent_company_card'  =>  ['card'],
        'agent_company_cardpositive'  =>  ['cardpositive'],
        'agent_company_cardnegative'  =>  ['cardnegative'],
        'agent_company_license'  =>  ['license'],
    ];


}

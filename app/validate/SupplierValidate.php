<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-02-25 11:50:38
 * Desc: 供应商验证
 */


namespace app\validate;

class SupplierValidate extends BaseValidate
{
    protected $rule = [
    	'id'        => 'require',
        'uid'       => 'require',
        'id_str'    => 'require|checkcartid',
        'title'    => 'require',
        'province'    => 'require',
        'city'    => 'require',
        'area'    => 'require',
        'add'    => 'require',
        'name'    => 'require',
        'card'    => 'require',
        'cardpositive'    => 'require',
        'cardnegative'    => 'require',
        'license'    => 'require',
        'image'    => 'require',
        'money'    => 'require|isMoney',
    ];

    protected $message = [
        'uid'    => '用户ID必须',  
        'title'    => '请提交公司名称',
        'province'    => '请提交区域',
        'city'    => '请提交区域',
        'area'    => '请提交区域',
        'add'    => '请提交地址',
        'name'    => '请提交法人姓名',
        'card'    => '请提交法人身份证号',
        'cardpositive'    => '请上传法人身份证正面',
        'cardnegative'    => '请上传法人身份证反面',
        'license'    => '请上传营业执照',
        'image'    => '请上传主营商品图片',
        'money'    => '请输入付款金额',
        
    ];


    protected $scene  = [
        'scene_find'  =>  ['id'],
        'scene_del'   =>  ['id_str'],
        'scene_add'   =>  ['uid'=>'require'],
        'scene_edit'  =>  ['uid'],
        'supplier_company_title'  =>  ['title'],
        'supplier_company_region'  =>  ['province','city','area','town'],
        'supplier_company_add'  =>  ['add'],
        'supplier_company_name'  =>  ['name'],
        'supplier_company_card'  =>  ['card'],
        'supplier_company_cardpositive'  =>  ['cardpositive'],
        'supplier_company_cardnegative'  =>  ['cardnegative'],
        'supplier_company_license'  =>  ['license'],
        'supplier_company_product'  =>  ['image'],
        'pay'  =>  ['money'],
    ];


}

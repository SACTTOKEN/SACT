<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-15 09:16:11
 * Desc: 商品验证
 */
namespace app\validate;

class ProductValidate extends BaseValidate
{
    protected $rule = [
    	'id'     => 'require|unique:config',   	
        'title'    => 'require',
        'cate_id' => 'require',
        'id_str' => 'require|checkcartid',    
        'title' => 'require',   
        'cate_id' => 'require',   
        'price' => 'require|isMoney',   
        'cost_price' => 'number|egt:0',   
        'is_integral' => 'require|in:0,1',   
        'is_mail' => 'require|in:0,1',   
        'weight' => 'number|egt:0',   
        'stock' => 'number|egt:0',   
        'piclink' => 'require',   
        'content' => 'require',   
    ];


    protected $message = [
        'title.require'    => '商品名称必须',
        'cate_id'    => '请选择类别',
        'title' => '请提交标题',   
        'cate_id' => '请提交分类',   
        'price' => '请提交价格',   
        'cost_price' => '请提交成本价格',   
        'is_integral' => '选择是否积分抵用',   
        'is_mail' => '选择是否免邮',   
        'weight' => '提交重量',   
        'stock' => '提交库存',   
        'piclink' => '提交封面图',   
        'content' => '提交详情图',   
    ];

	protected $scene  = [
        'scene_find'  =>  ['id'=>'require'],
        'scene_list'  =>  ['cate'],
        'scene_add'   =>  ['title','cate_id'],
        'scene_checkID' =>  ['id_str'],
        'supplier_arr'  =>  ['title','cate_id','price','cost_price','hid','is_integral','is_mail','weight','stock','piclink'],
    ];

  

}

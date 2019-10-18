<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-15 09:16:11
 * Desc: 配置验证
 */
namespace app\validate;

class ProductSkuValidate extends BaseValidate
{
    protected $rule = [  
        'id' 	   => 'require',      //商品ID
        'iden' => 'require',    	  //sku串
    ];


	protected $scene  = [
        'scene_find'  =>  ['id'],     
        'scene_pro_sku_info' =>  ['iden','id'],
    ];

}

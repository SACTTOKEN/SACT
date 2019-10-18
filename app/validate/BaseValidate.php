<?php
namespace app\validate;


use core\lib\Request;
use core\lib\Validate;

/**
 * Class BaseValidate
 * 验证类的基类
 */
class BaseValidate extends Validate{
    /**
     * 检测所有客户端发来的参数是否符合验证类规则
     * 基类定义了很多自定义验证方法
     * 这些自定义验证方法其实，也可以直接调用
     * @return true
     */
    public function goCheck($scene='')
    {
        //必须设置contetn-type:application/json
        $request = Request::instance();
        $params = $request->post();
        if (!$this->check($params,[],$scene)) {
            $msg= is_array($this->error) ? implode(';', $this->error) : $this->error;
            error($msg,10000);
        }
        return true;
    }

    protected function checkcartid($values){
        if(empty($values)){
            error('请提交删除id串',10000);
        }
        $values=explode("@",$values);
        foreach ($values as $value)
        {
            if($value){
            	$result = $this->isPositiveInteger($value);
            	if($result!==true){
                error('删除ID只能是正整数',10000);
            	}
            }        
        }
        return true;
    }

  
    //没有使用TP的正则验证，集中在一处方便以后修改
    //不推荐使用正则，因为复用性太差
    //手机号的验证规则
    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|6|7|8|9)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    
}
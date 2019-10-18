<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 商品等级价格
 */
namespace app\model;

class product_price extends BaseModel
{
    public $title = 'product_price';
  
    /**
     * 按商品ID 删除数据规则
     * @param $pid 商品ID
     * @return BOOL
     */
    public function del_pid($pid){
        $this->delete($this->title,['pid'=>$pid]);
        return $this->doo();  
    }
}

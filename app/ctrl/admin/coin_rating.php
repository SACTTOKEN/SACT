<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 19:21:58
 * Desc: 区块链主流币
 */

namespace app\ctrl\admin;

use app\model\coin_rating as coin_rating_Model;
use app\validate\IDMustBeRequire;
use app\validate\CoinRatingValidate;


class coin_rating extends BaseController{
	
	public $coin_rating_M;
	public function __initialize(){
		$this->coin_rating_M = new coin_rating_Model();
	}

	/*按id查找*/
    public function edit(){    	
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->coin_rating_M->find($id);
        empty($data) && error('数据不存在',404);
        $data['direct_rating_cn']=$this->coin_rating_M->find('title',$data['direct_rating']);
        return $data;      
    }	


	/*保存修改*/
	public function saveedit()
	{
		(new IDMustBeRequire())->goCheck();	
        $id=post("id");
        if($id==1){
            $data = post(['title','trading_fee','direct_award']);
        }else{
            $data = post(['title','zt_num','td_num','coin_buy','trading_fee','send_lv','direct_award','direct_rating','direct_rating_number']);
        }
        (new CoinRatingValidate())->gocheck('edit');
        $res= $this->coin_rating_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改虚拟币等级',$id);  
 		return $res; 
    }
    

	/*列表*/
	public function lists()
	{
		$user_M = new \app\model\user();
		$coin_machine_M = new \app\model\coin_machine();
        $data=$this->coin_rating_M->lists_all();
        foreach($data as &$vo){
            $vo['coin_title']=$coin_machine_M->find($vo['send_lv'],'m_title');
            $vo['user_number']=$user_M->new_count(['coin_rating'=>$vo['id']]);
            $vo['direct_rating_cn']=$this->coin_rating_M->find($vo['direct_rating'],'title');
        }
        empty($data) && error('数据不存在',404);
        return $data; 
	}


    /* 保存添加 */
    public function saveadd()
    {
        $title=post('title');
        (new CoinRatingValidate())->gocheck('add');
        $data['title']=$title;
        $data['zt_num']=999;
        $ar=$this->coin_rating_M->save($data);
        empty($ar) && error('添加失败',10006);
		admin_log('添加虚拟币等级',$ar);  
        return $ar; 
    }

    /* 删除 */
    public function del()
    {
        $id = post('id');
        if($id==1){
			error('不能删除游客',400);	 
		}
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->coin_rating_M->is_find($id);
        empty($data) && error('数据不存在',404);
        $where['coin_rating']=$data['id'];
        $user_ar=(new \app\model\user())->have($where);
        $user_ar && error('有该等级的会员，请先修改会员等级');
        $this->coin_rating_M->del($id);
		admin_log('删除虚拟币等级',$id);  
        return "删除成功";
    }

}
<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 19:21:58
 * Desc: 区块链主流币
 */

namespace app\ctrl\admin;

use app\model\jddj_rating as jddj_rating_Model;
use app\validate\IDMustBeRequire;
use app\validate\JddjRatingValidate;


class jddj_rating extends BaseController{
	
	public $jddj_rating_M;
	public function __initialize(){
		$this->jddj_rating_M = new jddj_rating_Model();
	}

	/*按id查找*/
    public function edit(){    	
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->jddj_rating_M->find($id);
        empty($data) && error('数据不存在',404);
        $data['direct_rating_cn']=$this->jddj_rating_M->find('title',$data['direct_rating']);
        return $data;      
    }	


	/*保存修改*/
	public function saveedit()
	{
		(new IDMustBeRequire())->goCheck();	
        $id=post("id");
        if($id==1){
            $data = post(['title','zt_num','sxyj','ztjd_num','ztjd_id','jlqfb','dtsf_usdt']);
        }else{
            $data = post(['title','zt_num','sxyj','ztjd_num','ztjd_id','jlqfb','dtsf_usdt']);
        }
        (new JddjRatingValidate())->gocheck('edit');
        $res= $this->jddj_rating_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改节点等级',$id);  
 		return $res; 
    }
    

	/*列表*/
	public function lists()
	{
		//$user_M = new \app\model\user();
		//$coin_machine_M = new \app\model\coin_machine();
        $data=$this->jddj_rating_M->lists_all();
        //foreach($data as &$vo){
        //    $vo['coin_title']=$coin_machine_M->find($vo['ztjd_id'],'title');
           // $vo['user_number']=$user_M->new_count(['jddj_rating'=>$vo['id']]);
            //$vo['direct_rating_cn']=$this->jddj_rating_M->find($vo['direct_rating'],'title');
       // }
        empty($data) && error('数据不存在',404);
		
		
		foreach($data as &$one){
			
			$jddj_rating_M = new \app\model\jddj_rating();
			$bid=$jddj_rating_M->find($one['ztjd_id'],'title');
			if(empty($bid)){
				$one['m_title'] ="不存在";
			}
			else{
				$one['m_title'] =$bid;
		     }
		}	 
        return $data; 
	}


    /* 保存添加 */
    public function saveadd()
    {
        $title=post('title');
		$zt_num=post('zt_num');
		$sxyj=post('sxyj');
		$ztjd_num=post('ztjd_num');
		$ztjd_id=post('ztjd_id');
		$jlqfb=post('jlqfb');
		$dtsf_usdt=post('dtsf_usdt');
        (new JddjRatingValidate())->gocheck('add');
        $data['title']=$title;
        $data['zt_num']=$zt_num;
		$data['sxyj']=$sxyj;
		$data['ztjd_num']=$ztjd_num;
		$data['ztjd_id']=$ztjd_id;
		$data['jlqfb']=$jlqfb;
		$data['dtsf_usdt']=$dtsf_usdt;
        $ar=$this->jddj_rating_M->save($data);
        empty($ar) && error('添加失败',10006);
		admin_log('添加节点等级',$ar);  
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
    	$data = $this->jddj_rating_M->is_find($id);
        empty($data) && error('数据不存在',404);
        $where['jddj_rating']=$data['id'];
        $user_ar=(new \app\model\user())->have($where);
        $user_ar && error('有该等级的会员，请先修改会员等级');
        $this->jddj_rating_M->del($id);
		admin_log('删除节点等级',$id);  
        return "删除成功";
    }

}
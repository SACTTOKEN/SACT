<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-05-06 19:21:58
 * Desc: 区块链主流币
 */

namespace app\ctrl\admin;

use app\model\vip_rating as vip_rating_Model;
use app\validate\IDMustBeRequire;
use app\validate\VipRatingValidate;


class vip_rating extends BaseController{
	
	public $vip_rating_M;
	public function __initialize(){
		$this->vip_rating_M = new vip_rating_Model();
	}

	/*按id查找*/
    public function edit(){    	
    	$id = post('id');
    	(new IDMustBeRequire())->goCheck();
    	$data = $this->vip_rating_M->find($id);
        empty($data) && error('数据不存在',404);
        $data['direct_rating_cn']=$this->vip_rating_M->find('title',$data['direct_rating']);
        return $data;      
    }	


	/*保存修改*/
	public function saveedit()
	{
		(new IDMustBeRequire())->goCheck();	
        $id=post("id");
        if($id==1){
            $data = post(['title','ljrd','piclink','dtsf_usdt']);
        }else{
            $data = post(['title','ljrd','piclink','dtsf_usdt']);
        }
        (new VipRatingValidate())->gocheck('edit');
        $res= $this->vip_rating_M->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('修改VIP等级',$id);  
 		return $res; 
    }
    

	/*列表*/
	public function lists()
	{
		
        $data=$this->vip_rating_M->lists_all();
      
        empty($data) && error('数据不存在',404);
        return $data; 
	}


    /* 保存添加 */
    public function saveadd()
    {
        $title=post('title');
		$ljrd=post('ljrd');
		$piclink=post('piclink');
		$dtsf_usdt=post('dtsf_usdt');
        (new VipRatingValidate())->gocheck('add');
        $data['title']=$title;
        $data['ljrd']=$ljrd;
		$data['piclink']=$piclink;
		$data['dtsf_usdt']=$dtsf_usdt;
        $ar=$this->vip_rating_M->save($data);
        empty($ar) && error('添加失败',10006);
		admin_log('添加VIP等级',$ar);  
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
    	$data = $this->vip_rating_M->is_find($id);
        empty($data) && error('数据不存在',404);
        $where['vip_rating']=$data['id'];
        $user_ar=(new \app\model\user())->have($where);
        $user_ar && error('有该等级的会员，请先修改会员等级');
        $this->vip_rating_M->del($id);
		admin_log('删除VIP等级',$id);  
        return "删除成功";
    }

}
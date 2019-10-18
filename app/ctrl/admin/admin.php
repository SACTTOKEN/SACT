<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 管理员类
 */
namespace app\ctrl\admin;

use app\validate\IDMustBeRequire;
use app\validate\AdminValidate;
use app\model\admin as AdminModel;
use app\model\role as RoleModel;

class admin extends BaseController{

	public $admin_M;
	public $role_M;
	public function __initialize()
	{
		$this->admin_M = new AdminModel();
		$this->role_M = new RoleModel();
	}


	/*列表*/
	public function lists()
	{		
		(new \app\validate\AllsearchValidate())->goCheck();
		$data=$this->admin_M->lists_all();
        foreach($data as &$rs){
             $rs['role_name'] = $this->role_M->find($rs['role_id'],'role_name');
        }
        unset($rs);
	    return $data; 
	}

	
	/*保存添加*/
	public function saveadd()
	{
		(new AdminValidate())->goCheck('scene_saveadd');
		$data = post(['username','role_id','nick_name']);

		$roleM = new \app\model\role;
        $data['role_con'] = $roleM->find($data['role_id'],'role_con');
       
		$password = post('password');	
		$password = rsa_decrypt($password);
		if(empty($password)){
			error('密码未正确加密',400);
		}
		$data['last_login'] = time();
		$data['password'] = md5($password.'inex10086');
		$res=$this->admin_M->save($data); //返回了新生成的管理员ID
		$res = intval($res);

		/*判定是否开通 平台客服系统 ,如开通,则注册IM平台*/
		$im_open = plugin_is_open('btkfxt');

		if($im_open==1 && $res>0){
			$im_S = new \app\service\im();		
			$head=(new \app\model\banner())->have(['cate'=>'head'],'piclink');
			$im_S->login_admin($res,$data['username'],$data['nick_name'],$head);
		}

		empty($res) && error('添加失败',400);  
		admin_log('添加管理员',$res);      
		return $res;
	}

	
	/*修改*/
	public function edit()
	{
		(new IDMustBeRequire())->goCheck();
		$id=post("id");
		$res=$this->admin_M->find($id);
		empty($res) && error('数据不存在',404); 

		/*登录IM*/
		$im_open = plugin_is_open('btkfxt');
		if($im_open==1){
			$im_admin_C = new \app\ctrl\admin\im_admin();
			$sig = $im_admin_C -> login_im();
			$res['sig'] = $sig;
		}

		return $res;
	}

	
	/*保存修改*/
	public function saveedit()
	{
		(new IDMustBeRequire())->goCheck();	
		$id=post("id");
		$data = post(['role_id','tel','nick_name']);
		$roleM = new \app\model\role;
        $data['role_con'] = $roleM->find($data['role_id'],'role_con');
		
		$admin=$this->admin_M->find($id);
     
		if($admin['im']){
			$im = new \app\service\im();  
			$head=c('logo');
            $im->edit_info($admin['im'],$data['nick_name'],$head,2);
		}
		
		$res=$this->admin_M->up($id,$data);

		
		$info=$this->admin_M->find($id);
		$redis = new \core\lib\redis();
    	$rd_name = 'admin:'.$id;
    	$rd_key  = 'info';
    	$redis->hset($rd_name,$rd_key,$info);


		empty($res) && error('修改失败',404);
		admin_log('修改管理员',$id);  
 		return $res; 
	}


	/*是否显示*/
	public function show()
	{
		(new IDMustBeRequire())->goCheck();	
		$id=post("id");
		$data['show'] = post('show');
		$res=$this->admin_M->up($id,$data);
		empty($res) && error('修改失败',404);
		if($data['show']!=1){
		admin_log('禁用管理员',$id);  
		}else{
		admin_log('启用管理员',$id); 	
		}

		$info=$this->admin_M->find($id);
		$redis = new \core\lib\redis();
    	$rd_name = 'admin:'.$id;
    	$rd_key  = 'info';
		$redis->hset($rd_name,$rd_key,$info);
		
 		return $res; 
	}

	/*是否客服*/
	public function service()
	{
		(new IDMustBeRequire())->goCheck();	
		$id=post("id");
		$data['service'] = post('service');
		$res=$this->admin_M->up($id,$data);
		empty($res) && error('修改失败',404);
		if($data['service']!=1){
		admin_log('管理员设为非客服',$id);  
		}else{
		admin_log('管理员设为客服',$id); 	
		}

		$info=$this->admin_M->find($id);
		$redis = new \core\lib\redis();
    	$rd_name = 'admin:'.$id;
    	$rd_key  = 'info';
		$redis->hset($rd_name,$rd_key,$info);
		
 		return $res; 
	}

	
	/*删除*/
	public function del()
	{
		(new IDMustBeRequire())->goCheck();
	
		$id=post("id");
		$res=$this->admin_M->is_find($id);
		empty($res) && error('数据不存在',400); 
		
		$redis = new \core\lib\redis();
		$rd_name = 'admin:'.$id;
		$redis->hdel($rd_name);
            
		
		$res=$this->admin_M->del($id);
		empty($res) && error('删除失败',404);
		admin_log('删除管理员',$id);  
 		return $res; 
	}


	/*修改密码 注：解密前的$new_password与$re_password不同*/
	public function change_password()
	{
		
		$id = $GLOBALS['admin']['id'];

		$res= $this->admin_M->is_find($id);
		empty($res) && error('数据不存在',400); 

		$old_password = post('old_password');
		$new_password = post('new_password');
		$re_password = post('re_password'); //
				
		$admin_info = $this->admin_M->find($id);      
        $old_password = rsa_decrypt($old_password);
        $old_password = md5($old_password.'inex10086');
        $auth = $this->admin_M->check_user($admin_info['username'],$old_password);
        empty($auth) && error('原密码不正确');
        
        $new_password = rsa_decrypt($new_password);
        $re_password = rsa_decrypt($re_password);

        if(strlen(trim($new_password))<5){
        	error('密码长度需六位以上',400);
        }

        if(trim($new_password)=='' || trim($re_password)==''){
        	error('密码不能为空',400);
        }

		if($new_password != $re_password){
			error('两次密码不相同',400);
		}

        $new_password = md5($new_password.'inex10086');
        $res = $this->admin_M->up($id,['password'=>$new_password]);
        empty($res) && error('修改失败',400);

        admin_log('修改管理员密码',$id);  

        return '修改成功';
	}

}
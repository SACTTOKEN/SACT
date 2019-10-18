<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-18 15:52:42
 * Desc: 商家客户跟进
 */
namespace app\ctrl\mobile;
use app\model\drag_follow as drag_follow_Model;
use app\model\drag_follow_log as drag_follow_log_Model;
use app\validate\DragFollowValidate;
use app\validate\IDMustBeRequire;
class drag_follow extends BaseController{

    public $drag_follow_M;
    public $drag_follow_log_M;

	public function __initialize(){
		$this->drag_follow_M = new drag_follow_Model();
        $this->drag_follow_log_M = new drag_follow_log_Model();
	}

    //商户 点 我的客户 显示全部客户
    public function my_custom(){      
        $sid = $GLOBALS['user']['id'];
        $custom_type = post('custom_type',5);

        $where['custom_type'] = $custom_type;
        $where['sid'] = $sid;
        $page=post("page",1);
        $page_size = post("page_size",10);
        $ar = $this->drag_follow_M->lists($page,$page_size,$where);

        foreach($ar as $key=>$one){
            $user = user_info($one['uid']);
            $nickname = $user['nickname'];
            $username = $user['username'];
            $nickname = $nickname ? $nickname : $username;
            $ar[$key]['avatar'] = $user['avatar'];
            $ar[$key]['tel'] = $user['tel'];
            $ar[$key]['nickname'] = $nickname;
            $where_log['follow_id'] = $one['id'];
            $where_log['ORDER'] = ['id'=>'DESC'];
            $ar_log = $this->drag_follow_log_M->lists_all($where_log);
            $ar[$key]['content'] = $ar_log;
        }

        return $ar; 
    }

    //写跟进 follow_id=drag_follow表ID
    public function follow(){
        (new DragFollowValidate())->goCheck('scene_follow');  
        $follow_id = post('follow_id');
        $data['custom_type'] = post('custom_type'); //1-4对A-D
        //$data['tel'] = post('tel');
        $data_log['follow_id'] = $follow_id;
        $data_log['follow_time'] = post('follow_time');
        $data_log['content'] = post('content');
        $res1 = $this->drag_follow_M->up($follow_id,$data);
        $res2 = $this->drag_follow_log_M->save($data_log);   
        (empty($res1) || empty($res2)) && error('保存失败',400);
        return $res2;
    }

    //标注客户类型
    public function follow_type(){
        (new DragFollowValidate())->goCheck('scene_type');
        $follow_id = post('follow_id');
        $data['custom_type'] = post('custom_type');  
        $res = $this->drag_follow_M->up($follow_id,$data);  
        empty($res) && error('保存失败',400);
        return $res;
    }


    //跟进记录
    public function follow_log(){
        $sid = $GLOBALS['user']['id']; //商户ID
        $where['sid'] = $sid;
        $page=post("page",1);
        $page_size = post("page_size",10);

        $where['custom_type[>]'] = 0;
        $where['sid'] = $sid;
        $ar = $this->drag_follow_M->lists($page,$page_size,$where);
 
        foreach($ar as &$one){
            $id = $one['id'];

            $where_log['ORDER'] = ['id'=>'DESC'];
            $where_log['follow_id'] = $one['id'];

            $log_ar = $this->drag_follow_log_M ->lists_all($where_log);
            if(empty($log_ar)){
                $one['log'] = [];
            }else{
                $one['log'] = $log_ar;
            }
        }
        unset($one);

        foreach($ar as $key=>$one){
            $u_ar = user_info($one['uid']);
            $ar[$key]['avatar'] = $u_ar['avatar'];
            $nickname = $u_ar['nickname'];
            $username = $u_ar['username'];
            $nickname = $nickname ? $nickname : $username;
            $ar[$key]['nickname'] = $nickname;
        }

        return $ar;
    }
    
}

 
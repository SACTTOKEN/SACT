<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-26 19:24:25
 * Desc: 聚力兽 云平台
 */
namespace app\ctrl\mobile;
use app\model\drag as drag_Model;
use app\model\drag_num as drag_num_Model;
use app\model\drag_follow as drag_follow_Model;

use app\validate\DragValidate;
use app\validate\CloudValidate;
use app\validate\IDMustBeRequire;
class cloud extends BaseController{

    public $drag_num_M;
    public $drag_M;

	public function __initialize(){
        $zyfh = plugin_is_open('zyfh');
        if(!$zyfh){return false;}
		$this->drag_num_M = new drag_num_Model();
        $this->drag_M = new drag_Model();
        $this->drag_follow_M = new drag_follow_Model();
	}


    public function msg_tel(){
        (new CloudValidate())->goCheck('scene_msg');
        $tel = post('msg_tel','');
        $uid = $GLOBALS['user']['id'];
        $pid = post('pid');

        $where['uid'] = $uid;
        $where['pid'] = $pid;
        $this->drag_M->up_all($where,['msg_tel'=>$msg_tel]);



    }

    public function ask_tel(){
        (new CloudValidate())->goCheck('scene_ask');
        $tel = post('ask_tel','');



    }

  
   




}

 
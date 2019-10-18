<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-15 17:02:46
 * Desc: 我的团队
 */
namespace app\ctrl\mobile;

use app\Validate\IDMustBeRequire;
use app\model\user as UserModel;
use app\ctrl\mobile\BaseController;

class team extends BaseController
{
	public $user_M;
	
	public function __initialize(){
		$this->user_M = new UserModel();	
	}
        
    /*我的团队(带分页) level参数为 1||2||3，分别查一代，二代，三代*/
    public function my_team(){
        $uid = $GLOBALS['user']['id'];        
        $lv = post('level');
        switch ($lv) {
            case '1':
                $ar = $this->my_team_one($uid);
                break;
            case '2':
                $ar = $this->my_team_two($uid);
                break;
            case '3':
                $ar = $this->my_team_three($uid);
                break;                    
            default:
                $ar = [];
                break;
        }

        if(!empty($ar)){
            $rating_M = new \app\model\rating();
            foreach($ar as &$rs){ //&不同的名字访问同一个变量内容,引用传递,函数的引用返回,按传扯
                $rs['rating_name'] = $rating_M->find($rs['rating'],'title');
            }
            unset($rs);
        }
        return $ar; 
    }


    /*1代*/
    public function my_team_one($uid=0){
        $ar = $this->user_M->find_son($uid);
        return $ar;
    }

    /*2代*/
    public function my_team_two($uid=0){
        $ar = $this->my_team_one($uid);
        $ar_2 = [];
        foreach($ar as $uid_ar){
            $new_ar = $this->user_M->find_son($uid_ar['id']);
            $ar_2 = array_merge($ar_2,$new_ar);
        }
        return $ar_2;
    }

    /*3代*/
    public function my_team_three($uid=0){
        $ar = $this->my_team_two($uid);
        $ar_3 = [];
        foreach($ar as $uid_ar){
            $new_ar = $this->user_M->find_son($uid_ar['id']);
            $ar_3 = array_merge($ar_3,$new_ar);
        }
        return $ar_3;
    }

              

         


}







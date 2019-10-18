<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-09 15:13:50
 * Desc: 签到了
 */
namespace app\ctrl\mobile;

use app\Validate\IDMustBeRequire;
use app\model\sign_in as SignInModel;
use app\ctrl\mobile\BaseController;

class sign_in extends BaseController
{
	public $sign_in_M;
	
	public function __initialize(){
		$this->sign_in_M = new SignInModel();
	}
        
    //连续签到送积分配置
    public function reward(){
        $config_M = new \app\model\config();
        $data=$config_M->lists_all('sign_get_jf');
        return $data; 
    }    

    
    //改变提醒签到
    public function change_sign_remark(){
        $uid = $GLOBALS['user']['id'];
        $remark = post('remark');
        $remark = $remark ? 1 : 0;
        $user_M = new \app\model\user();
        $data['is_sign_remark'] = $remark;
        $res = $user_M -> up($uid,$data);
        empty($res) && error('修改失败',400);
        return $res;
    } 

    //是否提醒签到
    public function is_remark(){
        $uid = $GLOBALS['user']['id'];
        $user_M = new \app\model\user();
        $ar = $user_M -> find($uid);
        return $ar['is_sign_remark'];
    }

    //当天是否签到
    public function is_sign(){
        $stage = date('Ymd');
        $uid = $GLOBALS['user']['id'];
        $where_1['stage'] = $stage;
        $where_1['uid'] = $uid;
        $is_have_sign = $this->sign_in_M->is_have($where_1);
        return $is_have_sign;
    }

    //点击签到
    public function sign_ok(){
        $config_M = new \app\model\config();
        $where_3['cate'] = 'sign_get_jf';
        $config_ar = $config_M->have($where_3);
        $balance_type = $config_ar['help'];
        $balance_type_cn = find_reward_redis($balance_type);

        $user = $GLOBALS['user'];
        $uid = $GLOBALS['user']['id'];
        $ip = ip();
        $score = $user[$balance_type];
        $stage = date('Ymd');

        //是否已经签到 
        $where_1['stage'] = $stage;
        $where_1['uid'] = $uid;
        $is_have_sign = $this->sign_in_M->is_have($where_1);
        $is_have_sign && error('今天已签到',400);

        //是否连续签到，是则continu_num+1
        $pre_stage = date('Ymd',strtotime('-1 days'));
        $where['stage'] = $pre_stage;
        $where['uid'] = $uid;
        $is_have = $this->sign_in_M->is_have($where);
        $new_num = 1;
        if($is_have){
            $ar = $this->sign_in_M->have($where);
            $new_num = $ar['continu_num'] + 1;
            if($new_num>7){$new_num = 1;}
        }

        //连续签到天数相应奖励
        $win = [];
        $win[1] = c('sign_jf_1');
        $win[2] = c('sign_jf_2');
        $win[3] = c('sign_jf_3');
        $win[4] = c('sign_jf_4');
        $win[5] = c('sign_jf_5');
        $win[6] = c('sign_jf_6');
        $win[7] = c('sign_jf_7');

        $score_plus = $win[$new_num];
        $data['stage'] = $stage;
        $data['uid'] = $uid;
        $data['ip'] = $ip;
        $data['score'] = $score;
        $data['score_plus'] = $score_plus;
        $data['continu_num'] = $new_num;

        $money_S = new \app\service\money();

        //回滚BEGIN
            flash_god($uid);

            $model = new \core\lib\Model();
            $redis = new \core\lib\redis();  
            $model->action();
            $redis->multi();

            $id = $this->sign_in_M->save($data);
            empty($id) && error('签到失败',400);
            $oid = date('Ymd').rand(100,999).$id;
            $remark = '签到送'.$balance_type_cn;
            $money_S->plus($uid,$score_plus,$balance_type,"mobile_sign",$oid,$uid,$remark);

            $model->run();
            $redis->exec();
  
        return $score_plus;
    }


    //会员七天内可获得积分数组  显示连续签到的天数
    public function seven_day(){
        if(!plugin_is_open('sqhb')){
            error('暂未开放',400);
        }

        $uid = $GLOBALS['user']['id'];
        $stage1 = date('Ymd');
        $stage2 = date('Ymd',strtotime('+1 days'));
        $stage3 = date('Ymd',strtotime('+2 days'));
        $stage4 = date('Ymd',strtotime('+3 days'));
        $stage5 = date('Ymd',strtotime('+4 days'));
        $stage6 = date('Ymd',strtotime('+5 days'));
        $stage7 = date('Ymd',strtotime('+6 days'));
   
        $where['uid'] = $uid;
        $where['stage'] = date('Ymd',strtotime('-1 days'));      
        $where['ORDER'] = ['id'=>'DESC'];
        $win = [];
        $win[1] = renew_c('sign_jf_1');
        $win[2] = renew_c('sign_jf_2');
        $win[3] = renew_c('sign_jf_3');
        $win[4] = renew_c('sign_jf_4');
        $win[5] = renew_c('sign_jf_5');
        $win[6] = renew_c('sign_jf_6');
        $win[7] = renew_c('sign_jf_7');

        $ar = $this->sign_in_M->have($where); //昨天是否签到
        $flag = 0;

        $where_1['stage'] = date('Ymd');
        $where_1['uid'] = $uid;
        $today_is_sign = $this->sign_in_M->is_have($where_1);
        $today_is_sign = $today_is_sign ? 1 : 0;

        if($ar){    
            
            $c_num = $ar['continu_num'];
            $back = 7 - intval($c_num);

            $j=1;
            for($i=$c_num;$i>=1;$i--){        
                $my_time = '-'.$i.' days';
                $res[$i] = ['is_sign'=>1,'jf'=>$win[$j],'stage'=> date('Ymd',strtotime($my_time))];
                $j++;
            }
       
            $k = 0;
            for($i=$c_num+1; $i<=7; $i++){
               
                $my_time = '+'.$k.' days';
                if($i==$c_num+1){
                     $res2[$i] = ['is_sign' => $today_is_sign,'jf'=>$win[$i],'stage'=> date('Ymd',strtotime($my_time))];
                }else{
                     $res2[$i] = ['is_sign'=>0,'jf'=>$win[$i],'stage'=> date('Ymd',strtotime($my_time))];
                }
                $k++;
            }

            $res = array_merge($res,$res2);

        }else{
            $res[1] = ['is_sign'=>$today_is_sign,'jf'=>$win[1],'stage'=>$stage1];
            $res[2] = ['is_sign'=>0,'jf'=>$win[2],'stage'=>$stage2];
            $res[3] = ['is_sign'=>0,'jf'=>$win[3],'stage'=>$stage3];
            $res[4] = ['is_sign'=>0,'jf'=>$win[4],'stage'=>$stage4];
            $res[5] = ['is_sign'=>0,'jf'=>$win[5],'stage'=>$stage5];
            $res[6] = ['is_sign'=>0,'jf'=>$win[6],'stage'=>$stage6];
            $res[7] = ['is_sign'=>0,'jf'=>$win[7],'stage'=>$stage7];
        }

        $result['res'] = $res;

        //签到奖励类型
        $config_M = new \app\model\config();
        $where_3['cate'] = 'sign_get_jf';
        $config_ar = $config_M->have($where_3);
        $result['type'] = ['value'=>$config_ar['help'],'label'=>find_reward_redis($config_ar['help'])];


        return $result; 
    }


  

         


}







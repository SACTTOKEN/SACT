<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-23 19:19:17
 * Desc: 反馈问题
 */

namespace app\ctrl\mobile;
use app\model\feedback as FeedbackModel;
use app\validate\IDMustBeRequire;
use app\validate\FeedbackValidate;

class feedback extends BaseController{
	
	public $feedback_M;
	public function __initialize(){
		$this->feedback_M = new FeedbackModel();
	}

    public function feedback_type(){
        $ar = ['商家问题','账号问题','支付问题','其他问题'];
        return $ar;
    }

    //前端反馈
    public function saveadd(){
        (new FeedbackValidate())->goCheck('scene_saveadd');
        $content = post('content');      
        $types = post('types');
        $piclink = post('piclink');
        $man = post('man');
        $tel = post('tel');
        $where['uid'] =  $GLOBALS['user']['id'];
        $where['stage'] = date('Ymd');
        $is_have = $this->feedback_M->is_have($where);
        $is_have && error('亲，一天只能反馈一次哟',400);
        $data['uid'] = $GLOBALS['user']['id'];
        $data['content'] = $content;
        $data['types'] = $types;
        $data['piclink'] = $piclink;
        $data['man'] = $man;
        $data['tel'] = $tel;
        $data['stage'] = date('Ymd');
        flash_god($data['uid']);
        $res = $this->feedback_M->save($data);
        empty($res) && error('提交失败',400);
        return $res;
    }





  

}
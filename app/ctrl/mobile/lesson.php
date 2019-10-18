<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-22 10:25:40
 * Desc: 前端内容付费 课程
 */
namespace app\ctrl\mobile;

use app\model\lesson as LessonModel;
use app\model\lesson_cate as LessonCateModel;
use app\model\lesson_stage as LessonStageModel;
use app\ctrl\mobile\BaseController;
class lesson extends BaseController{

    public $lesson_M;
    public $lesson_cate_M;
    public $lesson_stage_M;
    public function __initialize()
    {
        $this->lesson_M = new LessonModel();
        $this->lesson_cate_M = new LessonCateModel();
        $this->lesson_stage_M = new LessonStageModel();
    }

    public function cate_lists(){
        $ar = $this->lesson_cate_M->lists_all();
        return $ar;
    }


    public function lesson_lists(){
        (new \app\validate\IDMustBeRequire())->goCheck();
        $cid = post('id');
        $where = [];
        $where['cid'] = $cid;
        $page=post("page",1);
        $page_size = post("page_size",10);
        $ar =  $this->lesson_M->lists($page,$page_size,$where);
        return $ar;
    }

    public function lesson_stage_lists(){
        (new \app\validate\IDMustBeRequire())->goCheck();
        $lesson_id = post('id');
        $where = [];
        $where['lesson_id'] = $lesson_id;
        $page=post("page",1);
        $page_size = post("page_size",10);
        $this->lesson_M->up($lesson_id,['hit[+]'=>1]);
        $ar =  $this->lesson_stage_M->lists($page,$page_size,$where);
        return $ar;
    }

    public function lesson_hot(){
        $where = [];
        $where['LIMIT'] = 10;
        $where['ORDER'] = ['hit'=>'DESC'];
        $ar =  $this->lesson_M->lists_all($where);
        return $ar;     
    }


    //下单 参考支付会员VIP







}
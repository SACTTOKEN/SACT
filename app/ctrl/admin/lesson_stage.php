<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-22 10:25:40
 * Desc: 内容付费 课程集数
 */
namespace app\ctrl\admin;

use app\model\lesson_cate as LessonCateModel;
use app\model\lesson as LessonModel;
use app\model\lesson_stage as LessonStageModel;

class lesson_stage extends BaseController{

    public $lesson_cate_M;
    public $lesson_M;
    public $lesson_stage_M;
    public function __initialize()
    {
        $this->lesson_cate_M = new LessonCateModel();
        $this->lesson_M = NEW LessonModel();
        $this->lesson_stage_M = new LessonStageModel();
    }

    public function saveadd(){
    	(new \app\validate\LessonStageValidate())->goCheck('sence_add');
        $ar = post(['lesson_id','title','video','is_free']);
        $where['lesson_id'] = $ar['lesson_id'];
        $is_have = $this->lesson_M->is_have(['id'=>$ar['lesson_id']]);
        empty($is_have) && error('课程不存在',400);

        $num = $this->lesson_stage_M->new_count($where);
        $ar['stage'] = $num+1;
        $res = $this->lesson_stage_M->save($ar);
        empty($res) && error('添加失败',400);
        return $res;      
    }

    public function saveedit(){
    	(new \app\validate\LessonStageValidate())->goCheck('sence_edit');
    	(new \app\validate\IDMustBeRequire())->goCheck();
    	$id = post('id');
        $ar = post(['lesson_id','title','video','stage','is_free']);
        $is_have = $this->lesson_M->is_have(['id'=>$ar['lesson_id']]);
        empty($is_have) && error('课程不存在',400);

        $res = $this->lesson_stage_M->up($id,$ar);
        empty($res) && error('修改失败',400);
        return $res; 
    }

    public function del(){
        (new \app\validate\DelValidate())->goCheck();
        $id_str = post('id_str');
        $id_ar = explode('@',$id_str);
        $res = $this->lesson_stage_M -> del($id_ar);
        empty($res) && error('删除失败',400);
        return $res;
    }

    public function lists(){
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id = post('id');
        $where = [];
        $where['lesson_id'] = $id;
        $data=$this->lesson_stage_M->lists_all($where);
        return $data;
    }


}
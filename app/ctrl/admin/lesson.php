<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-22 10:25:40
 * Desc: 内容付费 课程
 */
namespace app\ctrl\admin;

use app\model\lesson_cate as LessonCateModel;
use app\model\lesson as LessonModel;
use app\model\lesson_stage as LessonStageModel;

class lesson extends BaseController{

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
    	(new \app\validate\LessonValidate())->goCheck('sence_add');
        $ar = post(['cid','title','piclink','price','market_price','video','content']);
        $res = $this->lesson_M->save($ar);
        empty($res) && error('添加失败',400);
        return $res;      
    }

    public function saveedit(){
    	(new \app\validate\LessonValidate())->goCheck('sence_edit');
    	(new \app\validate\IDMustBeRequire())->goCheck();
    	$id = post('id');
        $ar = post(['cid','title','piclink','price','market_price','video','content']);
        $res = $this->lesson_M->up($id,$ar);
        empty($res) && error('修改失败',400);
        return $res; 
    }


    /*按id删除*/
    public function del(){
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id = post('id'); //课程ID
        $is_have = $this->lesson_stage_M->is_have(['lesson_id'=>$id]);
        !empty($is_have) && error('请先删除该课程下的内容信息',400);
        $res=$this->lesson_M->del($id);
        empty($res) && error('删除失败',400);
        admin_log('删除课程',$id);
        return $res;
    }


    //获取课程信息
    public function lesson_one(){
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id = post('id');

        $data = $this->lesson_M->find($id);

        $lesson_stage_M = new \app\model\lesson_stage();
        $data['stage_ar'] = $lesson_stage_M->lists_all(['lesson_id'=>$id]);
        return $data;
    }


    public function lists(){
        (new \app\validate\PageValidate())->goCheck();
        $where = [];
        $title = trim(post('title'));
        $price_begin = trim(post('price_begin'));
        $price_end = trim(post('price_end'));
        $cid = post('cid');
        if($title){
            $where['title[~]'] = $title;
        }
        if($cid){
            $where['cid'] = $cid;
        }

        if (is_numeric($price_begin)) {
            $where['price[<>]'] = [$price_begin, $price_end];
        }

        $page=post("page",1);
        $page_size = post("page_size",10);
        $data=$this->lesson_M->lists($page,$page_size,$where);
        $count = $this->lesson_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res;
    }


}
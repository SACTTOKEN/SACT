<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-08-22 10:25:40
 * Desc: 内容付费 课程分类
 */
namespace app\ctrl\admin;

use app\model\lesson_cate as LessonCateModel;

class lesson_cate extends BaseController{

    public $lesson_cate_M;
    public function __initialize()
    {
        $this->lesson_cate_M = new LessonCateModel();
    }

    public function saveadd(){
    	(new \app\validate\LessonCateValidate())->goCheck('sence_add');
        $cate_name = post('cate_name');
        $cate_pic  = post('cate_pic');
        $res = $this->lesson_cate_M->save(['cate_name'=>$cate_name,'cate_pic'=>$cate_pic]);
        empty($res) && error('添加失败',400);
        return $res;      
    }

    public function saveedit(){
    	(new \app\validate\LessonCateValidate())->goCheck('sence_edit');
    	(new \app\validate\IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$cate_name = post('cate_name');
        $cate_pic  = post('cate_pic');
        $is_have = $this->lesson_cate_M->have(['cate_name'=>$cate_name]);
        if($is_have && $is_have['id']!=$id){
        	error('类别名称重复');
        }
        $data['cate_name'] = $cate_name;
        $data['cate_pic']  = $cate_pic;
        $res = $this->lesson_cate_M->up($id,$data);
        empty($res) && error('修改失败',400);
        return $res; 
    }

    /*按id删除*/
    public function del(){
        (new \app\validate\IDMustBeRequire())->goCheck();
        $id = post('id');
        $lesson_M = new \app\model\lesson();
        $is_have = $lesson_M->is_have(['cid'=>$id]);
        !empty($is_have) && error('请先删除该分类下课程信息',400);
        $res=$this->lesson_cate_M->del($id);
        empty($res) && error('删除失败',400);
        admin_log('删除课程分类',$id);
        return $res;
    }


    /*列表*/
    public function lists(){
        (new \app\validate\PageValidate())->goCheck();
        $where = [];
        $page=post("page",1);
        $page_size = post("page_size",10);
        $title = post('title');
        if($title){
            $where['cate_name[~]'] = $title;
        }
        $data=$this->lesson_cate_M->lists($page,$page_size,$where);
        $count = $this->lesson_cate_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res;
    }


    /*列表*/
    public function lists_all(){
        $where = [];
        $data=$this->lesson_cate_M->lists_all($where);
        return $data;
    }
    


}
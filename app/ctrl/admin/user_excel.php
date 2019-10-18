<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-22 21:41:50
 * Desc: 用户与excel
 */

namespace app\ctrl\admin;

use app\model\user as UserModel;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;
use core\lib\model as Model;

class user_excel extends BaseController{
    
    public $model;
    public $user_M;
    public function __initialize(){
        $this->user_M = new UserModel();
        $this->model = new Model;   
    }


    //步骤一 获取excel中活动表
    public function excel_get_sheet(){
        include(IMOOC."/extend/phpexcel/PHPExcel.php");
        $filepath = post('filename','');

        $filepath =  IMOOC.str_replace('/api/', 'public/', $filepath);
        //$filepath = IMOOC.'public/resource/excel/demo.xlsx';

        $encode = 'UTF-8';
        $filetypes = explode('.',$filepath); 
        $file_type = $filetypes[count($filetypes)-1];
        
        if(strtolower($file_type!="xlsx" && strtolower($file_type!="xls"))){
            error('不是excel文件，请重新上传');
        }

        if (strtolower($file_type) == 'xls')//判断excel表类型为2003还是2007
        {
            include(IMOOC."extend/phpexcel/PHPExcel/Reader/Excel5.php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        } elseif (strtolower($file_type) == 'xlsx') {
            include(IMOOC."extend/phpexcel/PHPExcel/Reader/Excel2007.php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }

        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filepath);
        $sheet_ar = $objPHPExcel->getSheetNames();  //  Array ( [0] => Sheet1 [1] => Sheet2 [2] => Sheet3 )

        foreach($sheet_ar as $key=>&$one){
            $sheet=$objPHPExcel->getSheet($key);//获取一个工作表   
            $number =$sheet->getHighestRow();//取得总行数
            $res['sheet'] = $one;
            $res['number'] = $number;
            $my_res[$one] = $res;
        }
        return $my_res;
    }



    //步骤二 获取excel的第一行字段做 下拉选项
    public function excel_row_one(){
        $sheet_name = post('sheet','Sheet1');
        $filepath = post('filename','');
        $filepath =  IMOOC.str_replace('/api/', 'public/', $filepath);
        //$filepath = IMOOC.'public/resource/excel/demo.xlsx';
        $ar = $this->excel_to_array($filepath,$sheet_name);
        return $ar[1];
    }

    public function show_title(){   
        $ar = [];
        $ar = [
            ['title'=>'用户昵称','iden'=>'nickname'],
            ['title'=>'电话号码','iden'=>'tel'],
            ['title'=>'会员等级','iden'=>'rating_cn'],
            ['title'=>'余额','iden'=>'money'],
            ['title'=>'佣金','iden'=>'amount'],
            ['title'=>'积分','iden'=>'integral'],
            ['title'=>'注册时间','iden'=>'created_time'],
            ['title'=>'注册IP','iden'=>'reg_ip'],
            ['title'=>'openid','iden'=>'openid'],
            ['title'=>'推荐人','iden'=>'tid_cn'],
        ];
        return $ar;
    }

    //步骤三 
    public function excel_in(){
        $stage = date('YmdHis');
        $sheet_name = post('sheet','Sheet1');
        $filepath = post('filename','');
        $filepath =  IMOOC.str_replace('/api/', 'public/', $filepath);
        $iden_ar = post(['nickname','tel','rating_cn','money','amount','integral','created_time','reg_ip','openid','tid_cn']);
            
        $iden_ar = array_filter($iden_ar); //去除空数组,下标不变
        $ar = $this->excel_to_array($filepath,$sheet_name);
      
        //cs($iden_ar,1); //OK

        $new_ar = [];
        $new_iden = array_flip($iden_ar); //键名和键值互换
      
        //cs($new_iden,1); //ok  [会员等级]=>rating_cn
     
        foreach($ar as $key=>$one){  //键名关系映射
            if($key==1){
                foreach($one as $num=>$title){
                  
                    $title = trim($title);
                    if($new_iden[$title]!=""){
                        $new_key = $new_iden[$title]; // rating_cn
                        $new_ar[$new_key] = $num; //$new_ar['rating_cn'] =10                                                                                              
                    }
                }      
            }
        } 

        foreach($ar as $key=>$one){       
            foreach($one as $num=>$title){
                foreach($new_ar as $rs_key=>$rs){
                    if($rs == $num){
                        $my_ar[$key-1][$rs_key] = $title;
                    }
                }
            }
        }

        unset($my_ar[0]);

        $money_S = new \app\service\money(); //导入资金从流水中写入
        
        foreach($my_ar as &$one){
            $one['stage'] = $stage;
            $one_plus = $one;
            unset($one['money']);
            unset($one['amount']);
            unset($one['integral']);
         
            $res = $this->user_M->save_by_excel($one);

            $oid = date('Ymd').rand(100,999).$res;
            if($one_plus['money']>0 && $res>0){
                $remark = 'excel表导入金额';  
                $money_S->plus($res,$one_plus['money'],'money',"excel_money",$oid,$res,$remark);
            }
            if($one_plus['amount']>0 && $res>0){
                $remark = 'excel表导入佣金';
                $money_S->plus($res,$one_plus['amount'],'amount',"excel_amount",$oid,$res,$remark);
            }
            if($one_plus['integral']>0 && $res>0){
                $remark = 'excel表导入积分';
                $money_S->plus($res,$one_plus['integral'],'integral',"excel_integral",$oid,$res,$remark);
            }            
        }

        $sql = "update user set rating=IFNULL((select id from rating where title=user.rating_cn),1)";
        $ar = $this->model::$medoo->query($sql)->fetchAll();

        $sql2 = "update user set tid=IFNULL((select id from (select * from user) temp where tel=user.tid_cn),0)";
        $ar2 = $this->model::$medoo->query($sql2)->fetchAll();

        return $res;
    }


    //excel转成数组
    public function excel_to_array($filepath,$sheet_name='Sheet1'){
        include(IMOOC."/extend/phpexcel/PHPExcel.php");
        $encode = 'UTF-8';
        $filetypes = explode('.',$filepath); 
        $file_type = $filetypes[count($filetypes)-1];
        
        if(strtolower($file_type!="xlsx" && strtolower($file_type!="xls"))){
            error('不是excel文件，请重新上传');
        }

        if (strtolower($file_type) == 'xls')//判断excel表类型为2003还是2007
        {
            include(IMOOC."/extend/phpexcel/PHPExcel/Reader/Excel5.php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        } elseif (strtolower($file_type) == 'xlsx') {
            include(IMOOC."/extend/phpexcel/PHPExcel/Reader/Excel2007.php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }

        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filepath);
        //$objWorksheet = $objPHPExcel->getActiveSheet();

        $objWorksheet = $objPHPExcel->getSheetByName($sheet_name);
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();

        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }

        return $excelData;
    }

    /*所有期号 和 其有的条数*/
    public function stage_all_num(){
        $res = $this->user_M->stage_all();
        $ar = [];
        foreach($res as $key=>$one){
            $where['stage'] = $one;
            $num = $this->user_M->new_count($where);
            $str  = $one;
            $str1 = substr($str,0,4)."-".substr($str,4,2)."-".substr($str,6,2)." ".substr($str,8,2).":".substr($str,10,2).":".substr($str,12,2);
            $stage_time = strtotime($str1);
            $ar[$key]['stage']=$stage_time; //期数改成期数时间
            $ar[$key]['num'] = $num;
        }
        return $ar;
    }


    /*删除某期导入的会员数据*/
    public function del_by_stage(){
        $stage = post('stage');
        empty($stage) && error('期号不能为空',400);
        $where['stage'] = $stage;
        $ar = $this->user_M->lists_all($where);
        foreach($ar as $one){
            $res = $this->user_M->del($one['id']);
            empty($res) && error('删除失败',400);
        }
        return $res;
    }

}
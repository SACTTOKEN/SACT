<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-02 10:09:25
 * Desc: 自定义excel表字段 控制器
 */

namespace app\ctrl\admin;

use app\model\excel_auto as ExcelAutoModel;
use app\model\excel_field as ExcelFieldModel;
use app\validate\IDMustBeRequire;
use app\ctrl\admin\BaseController;

class excel_auto extends BaseController{
	
	public $auto_M;
	public $field_M;
	public function __initialize(){
		$this->auto_M = new ExcelAutoModel();
		$this->field_M = new ExcelFieldModel();
	}
    
	public function lists(){
		$ar = $this->field_M->lists_all();
		$iden_ar = [];
		$con_ar = [];
		$my_ar = [];
		foreach($ar as $one){
			if($one['is_list']==1){
				$iden_ar[] = $one['iden']; //列表显示的字段
				$con_ar[] = $one['con'];
			}
		}		
		$page=post("page",1);
		$page_size = post("page_size",10);		
		$where = [];
		$search_ar = post(['a1','a2','a3','a4','a5','a6','a7','a8','a9','a10','a11','a12','a13','a14','a15','a16','a17','a18','a19','a20','a21','a22','a23','a24','a25','a26','a27','a28','a29','a30']);
		foreach($search_ar as $key=>$one){
			if($one!=''){
				$where[$key.'[~]'] = $one;
			}
		}

		$stage = post('stage');
		if($stage){
			$where['stage'] = $stage;
		}

		//cs($where,1);
		$iden_ar_plus = $iden_ar;
		$iden_ar_plus[] = 'id';	


		$where['ORDER']	 = ["id"=>"DESC"];
		$my_order = post('my_order'); //1,2
		if($my_order==1){
			$where['ORDER'] = ["a5[SIGNED]"=>"DESC"];
		}
		if($my_order==2){
			$where['ORDER'] = ["a5[SIGNED]"=>"ASC"];  //字符串当数字排序
		}


		$data = $this->auto_M->lists($page,$page_size,$where,$iden_ar_plus);
		//cs($this->auto_M->log(),1);
		$count = $this->auto_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 

        foreach($iden_ar as $key=>$one){
			$my_ar[$key]['iden'] = $one;
			$my_ar[$key]['title'] = $con_ar[$key];
		}
        $res['title_ar'] = $my_ar;

        $all_money = $this->auto_M->find_sum('a5');
        $res['all_money'] = $all_money;

        return $res;
	}


	public function edit_price(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$price = post('price'); //可以为负数
		$data['a30'] = $price;
		$res = $this->auto_M->up($id,$data);
		empty($res) && error('修改失败',400);
		return $res;
	}



	public function edit(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$ar = $this->field_M->lists_all();

		$iden_ar = [];
		$title_ar = [];
		$con_ar = [];
		foreach($ar as $one){
			if($one['is_show']==1){
				$iden_ar[] = $one['iden']; //列表显示的字段
				$title_ar[] = $one['con'];
			}
		}	
		$res['data'] = $this->auto_M->find($id);

		foreach($iden_ar as $key=>$one){
			$con_ar['iden'] = $one;
			$con_ar['title'] = $title_ar[$key];
			$res['con_ar'][] = $con_ar;
		}
		
		return $res;
	}


	public function saveedit(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		for($i=1;$i<=30;$i++){
			$b = 'a'.$i;
			$$b = post($b,'');
			if($$b!=''){
				$data[$b] = $$b;
			}
		}
		$res = $this->auto_M->up($id,$data);
		admin_log('修改自定义EXCEL',$id);
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


    //步骤二 
    public function show_title(){

    	$where['is_show']==1;
    	$ar = $this->field_M->lists_all($where,['iden','con']);
    	foreach($ar as &$one){
    		$one['title'] = $one['con'];
    	}
   		unset($one);
    	return $ar;
    }

    public function excel_in(){
    	//最新stage
    	$stage = $this->auto_M->max_stage();
    	$bank_name_iden = c('bank_name_iden'); //a1-a30
    	$begin_num_iden = c('begin_num_iden'); //a1-a30
    	$bank_M = new \app\model\excel_bank_name();
    	$old_bank_ar = $bank_M ->lists_all();
    	$bank_ar = [];
    	foreach($old_bank_ar as $one){
    		$bank_ar[ $one['begin_num'] ] = $one['bank_name'];
    	}
    	//

    	$sheet_name = post('sheet','Sheet1');
    	$filepath = post('filename','');
		$filepath =  IMOOC.str_replace('/api/', 'public/', $filepath);
		//$filepath = IMOOC.'public/resource/excel/demo.xlsx';
		$iden_ar = post(['a1','a2','a3','a4','a5','a6','a7','a8','a9','a10','a11','a12','a13','a14','a15','a16','a17','a18','a19','a20','a21','a22','a23','a24','a25','a26','a27','a28','a29','a30']);

		$iden_ar = array_filter($iden_ar); //去除空数组,下标不变

		// $iden_ar['a1'] = '地址';  
		// $iden_ar['a2'] = '标题'; 
		// $iden_ar['a3'] = '日期';

		$new_iden = array_flip($iden_ar); //键名和键值互换
		$ar = $this->excel_to_array($filepath,$sheet_name);

		// [    [1] => Array
		//         (
		//             [0] => 标题
		//             [1] => 日期
		//             [2] => 地址
		//         )

		//     [2] => Array
		//         (
		//             [0] => 林志铃嫁给小日本了
		//             [1] => 43648
		//             [2] => 东京金城太子酒店
		//         )

		//     [3] => Array
		//         (
		//             [0] => 林志铃嫁给小日本了！！
		//             [1] => 43648
		//             [2] => 东京金城太子酒店！！
		//         )
		// ]

		//cs($ar,1);
		$new_ar = [];

		foreach($ar as $key=>$one){
			if($key==1){
				foreach($one as $num=>$title){
					if($new_iden[$title]!=""){
						$new_key = $new_iden[$title]; // a1
						$new_ar[$new_key] = $num;
					}
				}
			}
		}

		//cs($new_ar,1); 
		// Array
		// (
		//     [a2] => 0
		//     [a1] => 1
		//     [a3] => 2
		// )

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
		foreach($my_ar as $one){
			$one['stage'] = $stage;
			//根据卡位前五位，生成相应银行名 $begin_num_iden 卡号字段  $bank_name_iden 银行卡名字段
			if($one[$begin_num_iden]){
				$begin = substr($one[$begin_num_iden],0,5);
				if(isset($bank_ar[$begin])){
					$one[$bank_name_iden] = $bank_ar[$begin];
				}
			}

			$res = $this->auto_M->save($one);
		}

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



    /*可搜选项*/
    public function search_option(){
		$ar = $this->field_M->lists_all();
		foreach($ar as $one){
			if($one['is_show']==1){
				$iden_ar[] = $one['iden']; //列表显示的字段
				$title_ar[] = $one['con'];
			}
		}

		foreach($iden_ar as $key=>$one){
			$my_ar[$key]['iden'] = $one;
			$my_ar[$key]['title'] = $title_ar[$key];
		}       
        return $my_ar;
    }


    public function del()
    {
		(new \app\validate\DelValidate())->goCheck();
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);
		$res=$this->auto_M->del($id_ar);
		empty($res) && error('删除失败',400);
		return $res;
	}

	//返回所有期号,供前端删除某一期用
	public function stage_all(){
		$res = $this->auto_M->stage_all();
		return $res;
	}

	public function stage_all_num(){
		$res = $this->auto_M->stage_all();
		$ar = [];
		foreach($res as $key=>$one){
			$where['stage'] = $one;
			$num = $this->auto_M->new_count($where);
			$ar[$key]['stage']=$one;
			$ar[$key]['num'] = $num;
		}
		return $ar;
	}


	public function del_by_stage(){
		$stage = post('stage');
		empty($stage) && error('期号不能为空',400);
		$where['stage'] = $stage;
		$res = $this->auto_M->del_all($where);
		empty($res) && error('删除失败',400);
		return $res;
	}




	//全部汇总  
	public function sum_money_all(){
		
		$where = [];
		$size_begin = post('size_begin');
		$size_begin = $size_begin ? $size_begin : 0;

		$where['ORDER'] = ['id'=>'DESC'];		
		$where['LIMIT'] = [$size_begin,100];		
		$all = $this->auto_M->lists_all($where);
		if(empty($all)){
			return 0;
		}

		$auto_all_M = new \app\model\excel_auto2();

		$auto3_M = new \app\model\excel_auto3();

		if(intval($size_begin)==0){
			$this->auto_M->del_excel_auto2();
			$this->auto_M->del_excel_auto3();
		}

   		$model = new \core\lib\Model();
	    $redis = new \core\lib\redis();  
	    $model->action();
	    $redis->multi();

	    //a3 商户 a4 银行卡 a5金额

		foreach($all as $one){

			$is_have = $auto_all_M -> is_have(['a4'=>$one['a4'],'a3'=>$one['a3']]);
			if($is_have){
				$ar = $auto_all_M -> have(['a4'=>$one['a4'],'a3'=>$one['a3'] ]);
				$auto_all_M->up($ar['id'],['a5[+]'=>round($one['a5'],2)]);
				$auto_all_M->up($ar['id'],['a30[+]'=>round($one['a30'],2)]);
			}else{
				unset($one['id']);
				$one['a5'] = round($one['a5'],2);
				$one['a30'] = round($one['a30'],2);
				$auto_all_M->save($one);
			}

			$oid = $one['a3'];
			$title = $one['a7'];
			$tel = $one['a8'];
			$is_have_oid = $auto3_M -> have(['oid'=>$oid]);  
			$b5 = substr($one['a4'],0,5);

			if(!$is_have_oid){
				$money_plus = floatval($one['a5']) + floatval($one['a30']);
				$auto3_M->save(['oid'=>$oid,'money'=>round($one['a5'],2), 'money_plus'=>round($money_plus,2),'bank_home'=>$b5]);
			}else{
				$money_plus = floatval($one['a5']) + floatval($one['a30']);
				$auto3_M->up($is_have_oid['id'],['money[+]'=>round($one['a5'],2)]); 
				$auto3_M->up($is_have_oid['id'],['money_plus[+]'=>round($money_plus,2)]);
				if($b5){	
					$new_bank = $is_have_oid['bank_home']."@@".$b5;
					$auto3_M->up($is_have_oid['id'],['bank_home'=>$new_bank]);
				}
			}

		}

        $model->run();
        $redis->exec();

        $size_begin = $size_begin+100;
		return $size_begin;
	}



	//汇总数据列表 以excel_auto3的商户为单位
	public function auto2_list(){

		$ar = $this->field_M->lists_all();
		$iden_ar = [];
		$con_ar = [];
		$my_ar = [];
		foreach($ar as $one){
			if($one['is_list']==1){
				$iden_ar[] = $one['iden']; //列表显示的字段
				$con_ar[] = $one['con'];
			}
		}		
		$page=post("page",1);
		$page_size = post("page_size",10);		
		$where = [];

		$oid = post('oid');
		if($oid){
			$where['oid[~]'] = $oid;
		}

		$title = post('title');
		if($title){
			$where['title[~]'] = $title;
		}

		$tel = post('tel');
		if($tel){
			$where['tel[~]'] = $tel;
		}

		$my_order = post('my_order',1);
		if($my_order == 1){ 
			$my_order = SORT_DESC;
			$all_order = 'DESC';
		}
		if($my_order == 2){ 
			$my_order = SORT_ASC;
			$all_order = 'ASC';
		}

		$bank_begin =post('bank_begin');  //银行卡前五位
		if($bank_begin){
			$where['bank_home[~]'] = $bank_begin;
		}

		$auto2_M = new \app\model\excel_auto2();
		$auto3_M = new \app\model\excel_auto3();
		$auto4_M = new \app\model\excel_auto4();

		
		$where['ORDER'] = ['money'=>$all_order];
		$data = $auto3_M->lists($page,$page_size,$where); //商户号列表
		$new_ar = [];

	
		foreach($data as $key=>$one){
			//把auto4的相同商户号的数据整合过来
			$ar4 = $auto4_M->have(['oid'=>$one['oid']]);
			if($ar4){
				$auto3_M->up_all(['oid'=>$one['oid']],['title'=>$ar4['title'],'tel'=>$ar4['tel'],'remark_1'=>$ar4['remark_1']]);
			}

			$where_2 = [];
			$oid = $one['oid'];
			$where_2['a3'] = $oid;
			
			$oid_ar = [];
			$oid_ar = $auto2_M -> lists_all($where_2);

			foreach($oid_ar as $k2=>$rs){
				$oid_ar[$k2]['change_money'] = floatval($rs['a5']) + floatval($rs['a30']); //单条改变后金额
				$oid_ar[$k2]['length'] = $auto2_M->new_count($where_2);
				// if($bank_begin){
				// $begin = '';
				// $begin = substr($rs['a4'],0,5);
				// if($begin != $bank_begin){	
				// 	unset($oid_ar[$k2]);		
				// }
				// }		
			}
			unset($rs);

			if(!empty($oid_ar)){
			$new_ar[$key]['ar'] = $oid_ar;
			$new_ar[$key]['money'] = $one['money']; //商户总额
			$new_ar[$key]['title'] = $one['title'];
			$new_ar[$key]['tel'] = $one['tel'];
			$new_ar[$key]['remark_1'] = $one['remark_1'];
			$new_ar[$key]['remark_2'] = $one['remark_2'];
			$new_ar[$key]['remark_3'] = $one['remark_3'];
			$new_ar[$key]['money_plus'] = $one['money_plus'];
			}

		}

		//二维数组排序
		// $money_order = array_column($new_ar,'money');
		// array_multisort($money_order,$my_order,$new_ar);
		$count = $auto3_M->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $new_ar; 

        foreach($iden_ar as $key=>$one){
			$my_ar[$key]['iden'] = $one;
			$my_ar[$key]['title'] = $con_ar[$key];
		}
        $res['title_ar'] = $my_ar;
        return $res;

	}






}
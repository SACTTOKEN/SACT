<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-21 14:43:50
 * Desc: 导入导出excel类
 */
namespace core\lib;

use core\lib\Exception;

class phpexcel{
    /**
    *导出exal表格
    * @param array $data 需要导出的数据
    * @param array $title 字段数组
    * @测试地址：http://www.api.com/admin/democontroller/excel_out
    * @调用方法：
        $newsM = new \app\model\news();
        $data = $newsM->select('news',['id','title','cate_id','content']);
        $title = ['ID','文章标题','类别ID','内容'];
        $phpexcel = new \core\lib\phpexcel();
        echo $phpexcel->wlw_excel_out($data,$title);
    **/
    function wlw_excel_out($data, $title, $name='')
    {
    include(IMOOC."/extend/phpexcel/PHPExcel.php");

    if (empty($data)) {
        error("需导出数据不能为空", 400);
        return;
    }
    if (empty($title)) {
        error("需要的标题数组不能为空", 400);
        return;
    }
    if (count($title) > 26) {
       error("数组字段过多",400);
        return;
    }
    if (count($title) != count($data[0])) {
       error("数组字段不一致",400);
        return;
    }

    $objPHPExcel = new \PHPExcel();
    $letter = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
    foreach ($data as $key => $val) {
        $row[$key][1] = $key + 1;
        $ac = 2;
        foreach ($val as $ti) {
            $row[$key][$ac] = $ti;
            $ac++;
        }
    }
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '序号');
    $ac = 1;
    foreach ($title as $vals) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letter[$ac] . '1', $vals);
        $ac++;
    }
    $i = 2;
    foreach ($data as $key => $val) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $i, " " . $row[$key][1]);
        $ac = 1;
        foreach ($title as $vals) {
            if($row[$key][$ac + 1]=="1970-01-01 08:00:00"){
                $row[$key][$ac + 1]='';
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letter[$ac] . $i, "\t".$row[$key][$ac + 1]."\t");
            $ac++;
        }
        $i++;
    }

    $objPHPExcel->getActiveSheet()->setTitle('Simple');
    $objPHPExcel->setActiveSheetIndex(0);
    $excel_name = $name.date('YmdHis');
    header('Content-Type: applicationnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $excel_name . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter->save('php://output');

    //以下方法会在服务器生成本地文件
    //$objWriter->save($excel_name.'.xls');
    // $file_name = $excel_name.".xls";
    // header('Content-Type:xlsx');
    // header('Content-Disposition:attachment;filename="'.$file_name.'"');
    // readfile($file_name);
    }




    /**
    *导入exal表格
    * @param array $filename 文件地址带文件名,encode:编码，file_type 后缀名 
    * @param array 数组 
    * @测试：http://www.api.com/admin/democontroller/excel_in
    * 调用：       
    *   $filepath = IMOOC."public/static/1.xls";
        $phpexcel = new \core\lib\phpexcel();
        $ar = $phpexcel->wlw_excel_in($filepath);
    **/
    function wlw_excel_in($filepath,$sheet_name='')
    {
        include(IMOOC."/extend/phpexcel/PHPExcel.php");
        //include(IMOOC."api/phpexcel/phpexcel/Calculation.php");
        //include(IMOOC."api/phpexcel/phpexcel/IOFactory.php");  
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
    

    //步骤一 获取excel中活动表
    public function excel_get_sheet($filepath){
        include(IMOOC."/extend/phpexcel/PHPExcel.php");
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

    
}
<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-04-01 14:50:15
 * Desc: 国家编码(国际短信)
 */
namespace app\ctrl\admin;

use app\model\country as CountryModel;


class country{
	public $country_M;
	public function __initialize(){
		$this->country_M = new CountryModel();
	}

	public function lists()
	{
		$data=$this->country_M->lists_all();
        return $data; 
	}

//================= 以上是基础方法 ==================

	/*导入EXCEL
	public function ex(){
		
		exit();

	$filepath = IMOOC."public/static/2.xls";
    $phpexcel = new \core\lib\phpexcel();


    $ar = $phpexcel->wlw_excel_in($filepath);

    $country_M = new \app\model\country();
    foreach($ar as $one){
    	$data['title_en'] = $one[0];
    	$data['title'] = $one[1];
    	$data['en_simpli'] = $one[2];
    	$data['code'] = $one[3];
    	$data['money'] = $one[4];	
    	$country_M->save($data);
    	unset($data);
    }
   	exit();
	}
*/
}
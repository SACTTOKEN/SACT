<?php
/**
 * Created by yayue_god
 * User: yayue
 * Date: 2018/12/13
 * Desc: DEMO模型
 */
namespace app\model;

class plugin_early_war extends BaseModel
{
    public $title = 'plugin_early_war'; 

    /*VUE赛季排名*/
    public function ranking($war){

    	$where_1['war'] = $war;
    	$where_1['champion'] =1;
    	$where_2['war'] = $war;
    	$where_2['champion'] =2;
    	$where_3['war'] = $war;
    	$where_3['champion'] =3;
    	$res_1 = $this->count($this->title,'id',$where_1);
		$res_2 = $this->get($this->title,'recode',$where_1);
    	$res_3 = $this->count($this->title,'id',$where_2);
    	$res_4 = $this->get($this->title,'recode',$where_2);	
    	$res_5 = $this->count($this->title,'id',$where_3);
		$res_6 = $this->get($this->title,'recode',$where_3);
		$res_1 = $res_1 ? $res_1 : 0;
		$res_2 = $res_2 ? $res_2 : 0;
		$res_3 = $res_3 ? $res_3 : 0;
		$res_4 = $res_4 ? $res_4 : 0;
		$res_5 = $res_5 ? $res_5 : 0;
		$res_6 = $res_6 ? $res_6 : 0;
        $rank['1'] = $res_1;
        $rank['2'] = $res_2;
        $rank['3'] = $res_3;
        $rank['4'] = $res_4;
        $rank['5'] = $res_5;
        $rank['6'] = $res_6;
        return $rank;
    }      
}

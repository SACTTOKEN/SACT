<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-25 09:16:58
 * Desc: 大转盘奖励等级
 */

namespace app\ctrl\admin;

use app\model\plugin_big_win as pbw_win_Model;

use app\ctrl\admin\BaseController;
use app\validate\IDMustBeRequire;
use app\validate\DelValidate;
use app\validate\PluginBigWheelValidate;


class plugin_big_win extends BaseController{
	
	public $pbw_win_M;
	public function __initialize(){
		$this->pbw_win_M = new pbw_win_Model();		
	}

	


}
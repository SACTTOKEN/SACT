<?php 
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-07-18 15:52:42
 * Desc: 商户单日数据汇总
 */
namespace app\ctrl\mobile;
use app\model\drag_day as drag_day_Model;
use app\validate\DragValidate;
use app\validate\IDMustBeRequire;
class drag_day extends PublicController{

    public $drag_day_M;

	public function __initialize(){
		$this->drag_day_M = new drag_day_Model();
	}

    //数据看板
    public function databoard(){
        $sid = $GLOBALS['user']['id']; //商户ID
        $where_1['sid'] = $sid;
        $num_1 = $this->drag_day_M->find_sum('hit',$where_1); //总点击量


        $where_2['stage'] = date('Ymd');
        $where_2['sid'] = $sid;
        $num_2 = $this->drag_day_M->find_sum('hit',$where_2); //今日点击



        $where_3['stage'] = date("Ymd", strtotime('-1 day'));
        $where_3['sid'] = $sid;
        $num_3 = $this->drag_day_M->find_sum('hit',$where_3); //昨日点击



        $where_4['is_ask'] = 1;
        $where_4['sid']  = $sid;
        $num_4 = $this->drag_day_M->new_count($where_4); //总咨询量
       

        $where_5['is_ask'] = 1;
        $where_5['sid']  = $sid;
        $where_5['stage'] = date('Ymd');
        $num_5 = $this->drag_day_M->new_count($where_5); //今日咨询
  
        $where_6['is_ask'] = 1;
        $where_6['sid']    = $sid;
        $where_6['stage']  = date("Ymd", strtotime('-1 day'));
        $num_6 = $this->drag_day_M->new_count($where_6); //昨日咨询
        
        $res = [
            'num_1' => $num_1,
            'num_2' => $num_2,
            'num_3' => $num_3,
            'num_4' => $num_4,
            'num_5' => $num_5,
            'num_6' => $num_6,
        ];
        return $res;
    }

    //客户访问记录排序列表
    public function custom_list(){
        $sid = $GLOBALS['user']['id']; //商户ID
        $where['sid'] = $sid;

        $page=post("page",1);
        $page_size = post("page_size",10);

        $sn = post('sn','id'); 
        // 时间排行：最新浏览的排序  列下来 last_time
        // 时长排行：访问总计时间最长的排序  view_time
        // 次数排行：根据总访问次数最多的排序  hit
        // 资源排行：被点击最多的产品排序   product
        if($sn!='product'){
            $where['ORDER'] = ['last_time'=>'DESC'];
            return $this->drag_day_M->lists($page,$page_size,$where);          
        }else{

            $product_M = new \app\model\product();
            $ar = $product_M->lists_all(['sid'=>$sid]);

            foreach($ar as &$one){
                $one['hit_all'] = $this->drag_day_M->find_sum('hit',['pid'=>$one['id']]); //单个商品的点击数
                unset($one['content']);
                unset($one['attr']);
                unset($one['sku_json']);
            }

            $hit_order = array_column($ar, 'hit_all');
            array_multisort($hit_order,SORT_DESC,$ar);

            return $ar;
        }

    }
    





  
    









}

 
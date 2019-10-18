<?php 
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-01-08 10:28:15
 * Desc: 首页控
 */
namespace app\ctrl\mobile;
use app\model\product as productModel;

class product extends PublicController{

	public $productM;
	public $create;
	public function __initialize(){
		$this->productM = new ProductModel();
	}

    public function lists(){
		(new \app\validate\AllsearchValidate())->goCheck();	
		(new \app\validate\PageValidate())->goCheck();		
		$page=post("page",1);
		$page_size = post("page_size",10);
		$cate_id = post('cate_id');
		$title = post('title');
		$sid_cn = post('sid_cn');
		$order_sn = post('order_sn');
		$sid = post('sid');
		
		$where = [];
		if($cate_id || $cate_id==='0'){
            $cate=(new \app\service\product)->find_tree_id($cate_id);
            if($cate){
                $cate=array_merge([$cate_id],$cate);
            }else{
                $cate=$cate_id;
            }
            $where['OR']['cate_id'] = $cate;
            $where['OR']['cate_ar[~]'] = '"'.$cate_id.'"';
        }       	
		if($title){
            $data['uid']  = $GLOBALS['user']['id'];
            $data['title']=$title;
            $search_M = new \app\model\search();
            $search_M->save($data);
            $where['title[~]'] = $title;
        }
		if($sid_cn){
			$user_M = new \app\model\user();
			$sid = $user_M -> find_uid($sid_cn);
			$where['sid'] = $sid;
        }
        if($sid){
            $where['sid']=$sid;
        }
        $where['show'] = 1;
        $where['is_check'] = 1;
        $where['types'] = [0,1,6];

        switch ($order_sn)
        {
        case 'price_desc':
            $order['price[SIGNED]']='DESC';
            break;  
        case 'price_asc':
            $order['price[SIGNED]']='ASC';
            break;
        case 'invent_sale_desc':
            $order['invent_sale']='DESC';
            break;  
        default:
            $order['is_top']='DESC';
        }
        $order['sort']='DESC';
        $order['id']='DESC';

        $where['ORDER']=$order;
        $data=$this->productM->lists_by_mobile($page,$page_size,$where);	
     
        $res['data'] = $data;
        return $res;        
    }
    
    public function search()
    {
        $where['uid']=$GLOBALS['user']['id'];
        $where['LIMIT']=[0,20];
        $where['ORDER']=['id'=>'DESC'];
        $data=(new \app\model\search())->lists_all($where,'title');
        return $data;
    }
    
    /*商品详细*/
    public function info(){
    	(new \app\validate\IDMustBeRequire())->goCheck();
        $id = post('id'); 
        $where['id']=$id;
        $where['show'] = 1;
        $where['is_check'] = 1;
        $data = $this->productM->have($where);
        empty($data) && error('商品不存在',10007);	

    	//sku图片内容
    	if($data['content']){$data['content'] = str_replace('@link=@','src=',$data['content']);}
    	if($data['sku_json']){$data['sku_json'] = json_decode($data['sku_json'],true);}
        $data['img_json'] = (new \app\model\image())->list_cate('product',$id);
        $my_pic_ar = array(['aid'=>$id,'cate'=>'product','id'=>0,'piclink'=>$data['piclink']]);
        if(empty($data['img_json'])){ $data['img_json'] = $my_pic_ar;}
        $data['attr']=(new \app\model\product_attr())->show_attr($data['id']);

        //评价
        $product_review_M = new \app\model\product_review();
        $where_talk['ORDER'] = ["id"=>"DESC"];
        $where_talk['pid'] = $id;
        $new_one = $product_review_M->have($where_talk,['uid','content']);    
        if($new_one){
            $talker= user_info($new_one['uid']);
            $one_talk['content'] = $new_one['content'];
            $one_talk['uid'] = $talker['id'];
            $one_talk['avatar']  = $talker['avatar'];
            $one_talk['nickname'] = $talker['nickname'] ? $talker['nickname'] : $talker['username']; 
            $one_talk['rating_cn']  =  $talker['coin_rating_cn'];
            $one_talk['created_time']  = $talker['created_time'];
            $data['one_talk']  = $one_talk;
            $data['talk_num'] = $product_review_M->new_count(['pid'=>$id]);
        }
        //优惠券
        if($data['is_coupon']==1){
        $data['coupon'] = (new \app\service\coupon())->product_coupon($id,$data['sid']);
        }
        //店铺信息
        if($data['sid']){
            $users=user_info($data['sid']);
            $shop['shop_title']=$users['shop_title'];
            $shop['shop_logo']=isset($users['shop_logo'])?$users['shop_logo']:'';
            $shop['shop_address']=$users['shop_province'].$users['shop_city'].$users['shop_area'].$users['shop_town'];
            $shop['score']=5;
            $shop['im']=$users['im'];
            $data['shop']=$shop;
        }

        //推荐商品
        $rec_where['id[!]']=$data['id'];
        $rec_where['cate_id']=$data['cate_id'];
        $rec_where['sid']=$data['sid'];
        $data['recommend']=$this->productM->lists_tj($rec_where,9);

        //置顶商品
        $hot_where['id[!]']=$data['id'];
        $hot_where['sid']=$data['sid'];
        $hot_where['is_top']=1;
        $data['hot']=$this->productM->lists_tj($hot_where,9);

        //购物车
        $pid=$this->productM->lists_all(['types'=>0],'id');
        $data['cart_number']=(new \app\model\cart())->new_count(['uid'=>$GLOBALS['user']['id'],'pid'=>$pid]);
     
        //客服
        if(plugin_is_open("imhyjsnt")){
            if($data['sid']){
                if(isset($data['shop']['im'])){
                    $data['kf_im']='/im/mes?id='.$data['shop']['im'];
                }
            }else{
                $data['kf_im']='/im/kf';
            }
        }
        //是否收藏
		$collect_where['uid']=$GLOBALS['user']['id'];
		$collect_where['aid']=$id;
        $data['collect'] = (new \app\model\product_collect())->is_have($collect_where);
        
        //活动商品
        if($data['types']){
            switch ($data['types'])
            {
            case 2:
                //积分兑换
                break;
            case 3:
                //砍价
                break;
            case 4:
                //拼团
                break;
            case 5:
                //众筹
                break;
            case 6:
                //预约商品
                break;
            case 7: 
                $data=$this->limited_time($data);    //限时特惠
                break;
            default:
            }
        }
        $data['send_score_cn']=find_reward_redis('integral');
        $data['piclink']=str_replace("https://","http://",$data['piclink']);
        return $data;  
    }

    

    //限时特惠
    public function limited_time($data)
    {
        $rob_time_M = new \app\model\rob_time();
        $where['id']=$data['time_id'];
        $where['begin_time[<]']=time();
        $where['end_time[>]']=time();
        $rob_ar=$rob_time_M->have($where);
        empty($rob_ar) && error('活动未开始',10007);
        $price = explode("-", $data['price']);
        $data_ar['limited_price']='';
        if (is_array($price)) {
            foreach ($price as $vos) {
                if ($vos) {
                        $data_ar['limited_price'] .= $vos*$data['discount_rob']/10 . '-';
                }
            }
        }
        $data_ar['limited_price'] = rtrim($data_ar['limited_price'], "-");
        $data_ar['distance_end_time']=$rob_ar['end_time']-time();
        $data['activity']=$data_ar;
        return $data;
    }
    
	
}
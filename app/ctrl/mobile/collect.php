<?php 
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2019-07-08 10:28:15
 * Desc: 各类收藏
 */
namespace app\ctrl\mobile;

class collect extends BaseController{

	public $page_S;
	public function __initialize(){
		$this->page_S = new \app\service\page();
	}

	//收藏商品
	public function product_collect()
	{
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id = post('id');
		$news_ar = (new \app\model\product())->find($id,['title','piclink','price']);
		empty($news_ar) && error('商品不存在',10007);
		$product_collect_M=new \app\model\product_collect();
		$uid=$GLOBALS['user']['id'];
		$where['uid']=$uid;
		$where['aid']=$id;
		$res = $product_collect_M->is_have($where);
		if(!$res){
			$data['uid']=$uid;
            $data['aid']=$id;
            if(isset($news_ar['piclink'])){
            $data['piclink']=$news_ar['piclink'];
            }
			$data['title']=$news_ar['title'];
			$data['price']=$news_ar['price'];
			$news=$product_collect_M->save($data);
			empty($news) && error('收藏失败',10006);

			if(c('is_scyjsp')==1){
				$new_duty_S = new \app\service\new_duty();
    			$new_duty_S->paid_reward($uid,'scyjsp');//新手任务-收藏一件商品
			}
			
            return "收藏成功";
		}else{
            $product_collect_M->del_all($where);
            return "取消收藏";
        }
    }


    //店铺收藏 id 商户id product表的sid
    public function shop_collect()
	{
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id = post('id');
		$sid_info = user_info($id); //商家信息
		$shop_collect_M=new \app\model\shop_collect();
		$uid=$GLOBALS['user']['id'];
		$where['uid']=$uid;
		$where['aid']=$id;
		$res = $shop_collect_M->is_have($where);
		if(!$res){
			$data['uid']=$uid;
            $data['aid']=$id;
            if(isset($sid_info['shop_logo'])){
            $data['piclink']=$sid_info['shop_logo'];
            }
			$data['title']=$sid_info['shop_title'];
			$news=$shop_collect_M->save($data);
			empty($news) && error('收藏失败',10006);
            return "收藏成功";
		}else{
            $shop_collect_M->del_all($where);
            return "取消收藏";
        }
    }

    
    //收藏新闻
	public function news_collect()
	{
		(new \app\validate\IDMustBeRequire())->goCheck();
		$id = post('id');
		$news_ar = (new \app\model\news())->find($id,['title','piclink']);
		empty($news_ar) && error('新闻不存在',10007);
		$news_collect_M=new \app\model\news_collect();
		$uid=$GLOBALS['user']['id'];
		$where['uid']=$uid;
		$where['aid']=$id;
		$res = $news_collect_M->is_have($where);
		if(!$res){
			$data['uid']=$uid;
            $data['aid']=$id;
            if(isset($news_ar['piclink'])){
            $data['piclink']=$news_ar['piclink'];
            }
			$data['title']=$news_ar['title'];
            $news=$news_collect_M->save($data);
			empty($news) && error('收藏失败',10006);
			return "收藏成功";
		}else{
            $news_collect_M->del_all($where);
            return "取消收藏";
        }
    }


    //商品收藏列表
    public function product_collect_lists()
	{
		$uid=$GLOBALS['user']['id'];
		$where['uid']=$uid;
		$p_collect_M = new \app\model\product_collect();		
		$page=post("page",1);
		$page_size = post("page_size",10);
		$res = $p_collect_M->lists($page,$page_size,$where);
		return $res;	
    }

    //新闻收藏列表
    public function news_collect_lists()
	{
		$uid=$GLOBALS['user']['id'];
		$where['uid']=$uid;
		$news_collect_M = new \app\model\news_collect();			
		$page=post("page",1);
		$page_size = post("page_size",10);
		$res = $news_collect_M->lists($page,$page_size,$where);
		return $res;	
    }

    //店铺收藏列表
    public function shop_collect_lists()
    {
    	$uid = $GLOBALS['user']['id'];
    	$where['uid'] = $uid;
    	$shop_collect_M = new \app\model\shop_collect();
    	$page=post("page",1);
		$page_size = post("page_size",10);
		$res = $shop_collect_M->lists($page,$page_size,$where);
		return $res;	
    }

    
    
    
    




}
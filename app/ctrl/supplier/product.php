<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-18 13:50:53
 * Desc: 商品控制器
 */

namespace app\ctrl\supplier;

use app\model\product as productModel;
use app\validate\ProductValidate;
use app\validate\IDMustBeRequire;
use app\service\create_html;
use app\validate\AllsearchValidate;


class product extends BaseController
{

	public $productM;
	public $create;
	public function __initialize()
	{
		$this->productM = new ProductModel();
		$this->create = new create_html();
	}

	/*查某类product @参数cate分类标识*/
	public function lists()
	{
		(new ProductValidate())->goCheck('scene_list');
		(new AllsearchValidate())->goCheck();
		(new \app\validate\PageValidate())->goCheck();

		$page = post("page", 1);
		$page_size = post("page_size", 10);
		$cate_id = post('cate_id');
		$title = post('title');
		$sid_cn = post('sid_cn');
		$show = post('show');
		$stock = post('stock');
		$is_check = post('is_check');
		$price_begin = post('price_begin');
		$price_end = post('price_end');
		$cost_price_begin = post('cost_price_begin');
		$cost_price_end = post('cost_price_end');
		$is_mail = post('is_mail');
		$is_top = post('is_top');
		$is_coupon = post('is_coupon');
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
		
		if ($title) {
			$where['title[~]'] = $title;
		}
		if ($sid_cn) {
			$user_M = new \app\model\user();
			$sid = $user_M->find_uid($sid_cn);
			$where['sid'] = $sid;
		}
		if (is_numeric($show)) {
			$where['show'] = $show;
			if ($show == 1) {
				$where['stock[!]'] = 0;
				$where['show'] = 1;
				$where['is_check'] = 1;
			} else {
				$where['show'] = 0;
			}
		}
		if (is_numeric($stock)) {
			$where['stock'] = $stock;
		}
		if (is_numeric($is_check)) {
			$where['is_check'] = $is_check;
		}
		if (is_numeric($is_mail)) {
			$where['is_mail'] = $is_mail;
		}
		if (is_numeric($is_top)) {
			$where['is_top'] = $is_top;
		}
		if (is_numeric($is_coupon)) {
			$where['is_coupon'] = $is_coupon;
		}
		if (is_numeric($price_begin)) {
			$where['price[<>]'] = [$price_begin, $price_end];
		}
		if (is_numeric($cost_price_begin)) {
			$where['cost_price[<>]'] = [$cost_price_begin, $cost_price_end];
		}
		$where['sid']=$GLOBALS['user']['id'];
		$data = $this->productM->admin_lists($page, $page_size, $where);
		foreach ($data as &$vo) {
			$vo['pv'] = '';
			$price = explode("-", $vo['price']);
			if (is_array($price)) {
				foreach ($price as $vos) {
					if ($vos) {
						if (isset($vo['cost_price'])) {
							$vo['cost_price'] = $vo['cost_price'] ? $vo['cost_price'] : 0;
							$vo['pv'] .= ($vos - $vo['cost_price']) . '-';
						}
					}
				}
			}
			if (empty($vo['pv'])) {
				$vo['pv'] = rtrim($vo['pv'], "-");
			} else {
				$vo['pv'] = $vo['cost_price'];
			}
			$vo['qr']='http://api.k780.com:88/?app=qr.get&level=L&size=6&data='.$web_url.'/shop/shopdetails?id='.$vo['id'];
		}
		$count = $this->productM->new_count($where);

		//cs($this->productM->log(),1);

		$res['all_num'] = $count;
		$res['all_page'] = ceil($count / $page_size);
		$res['page'] = $page;
		$res['data'] = $data;
		return $res;
	}




	public function edit()
	{
		$id = post('id');
		(new ProductValidate())->goCheck('scene_find');
		$data = $this->productM->have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
		$data['send_score'] = sprintf("%.2f", $data['send_score']);
		if ($data['content']) {
			$data['content'] = str_replace('@link=@', 'src=', $data['content']);
		}
		$cate_id = $data['cate_id'];
		$product_cate_M = new \app\model\product_cate();
		$data['cate_father'] = $product_cate_M->find_father($cate_id, [$cate_id]);

		$pro_attr_M = new \app\model\product_attr();
		$pro_sku_M = new \app\model\product_sku();
		$sku_M = new \app\model\sku();
		$pro_price_M = new \app\model\product_price();
		$data['attr'] = $pro_attr_M->lists_all(['pid' => $id], ['parent_title', 'parent_id', 'sku_id(id)', 'sku_title(title)', 'piclink']);
		foreach ($data['attr'] as &$vo) {
			$vo['parent_is_pic'] = $sku_M->find($vo['parent_id'], 'is_pic');
		}
		$data['attr'] = json_encode($data['attr'], JSON_UNESCAPED_UNICODE);

		$data['sku_json'] = $pro_sku_M->list_cate($id);
		$ratings = (new \app\model\rating())->lists_all([], ['title', 'id', 'price']);
		$ratings = array_column($ratings, null, 'id');
		if ($data['sku_json']) {
			$sku_title = $sku_M->lists_all_title();
			foreach ($data['sku_json'] as &$vo) {
				if ($vo['iden']) {
					$iden_ar = explode('@', $vo['iden']);
					foreach ($iden_ar as $one_key => $one) {
						$one_ar = explode(":", $one);
						$vo[$one_key] = $sku_title[$one_ar[1]];
					}
				}
				$rating_json = $pro_price_M->lists_all(['pid' => $id, 'sku_id' => $vo['id']]);
				$vo['rating_json'] = $ratings;
				if ($rating_json) {
					foreach ($rating_json as $vos) {
						$vo['rating_json'][$vos['rating']]['price'] = spr_mall($vos['price']);
					}
				}
				$vo['cost_price'] = spr_mall($vo['cost_price']);
				$vo['price'] = spr_mall($vo['price']);
			}
		}

		$image_M = new \app\model\image();
		$image_ar = $image_M->list_cate('product', $id);
		$data['img_json'] = $image_ar;
		empty($data) && error('数据不存在', 404);
		return $data;
	}


	/*添加商品,price从sku_json中提取，格式：低价-高价*/
	public function saveadd()
	{
		$data = post(['is_virtual','sid', 'types', 'title', 'sub_title', 'cost_price', 'cate_id', 'piclink', 'content', 'show', 'sort', 'sku_id', 'invent_sale', 'real_sale', 'send_score', 'day_limit_buy', 'all_limit_buy', 'is_coupon', 'is_realname', 'is_top', 'discount_number', 'discount', 'is_mail', 'attr', 'sku_json', 'weight', 'weight_fix', 'made_01', 'made_02', 'made_03','day_limit']);
		$data['sid']=$GLOBALS['user']['id'];
		//属于多个分类时，把其它分类ID 1@2@3 拆分并查出类别串 写进cate_ar 字段
		
		//属于多个分类时，把其它分类ID 1@2@3 拆分并查出类别串 写进cate_ar 字段
		$cate_str = post('cate_ar');
		if ($cate_str) {
			$product_cate_M = new \app\model\product_cate();
			$cate_ar = explode('@', $cate_str);
			foreach ($cate_ar as $one) {
				if ($one) {

					$cate_ar_up[] = $product_cate_M->find_father($one, [$one]);
				}
			}
			$cate_ar_up = json_encode($cate_ar_up);
			$data['cate_ar'] = $cate_ar_up;
		}

		$sku_json = post('sku_json');
		$sku_json_ar = json_decode($sku_json, true);
		$img_json = post('img_json');
		$img_json_ar = json_decode($img_json, true);
		$attr = post('attr');
		(new ProductValidate())->goCheck('scene_add');

		$redis = new \core\lib\redis();
		$Model = new \core\lib\Model();
		$Model->action();
		$redis->multi();

		$data['stock'] = post('stock', '999');
		$res = $this->productM->save($data);
		$pro_id = $res;

		if (!empty($sku_json_ar)) {
			$data_up = $this->save_pro_sku($sku_json_ar, $pro_id);
			$this->productM->up($pro_id, $data_up);
		}

		if (!empty($img_json_ar)) {
			$aid = $pro_id;
			$cate = 'product';
			if ($data['piclink'] == '' &&  isset($img_json_ar[0]['piclink'])) {
				$this->productM->up($pro_id, ['piclink' => $img_json_ar[0]['piclink']]); //如没有设主图，默认第一张为主图
			}
			$this->save_pro_img($img_json_ar, $aid, $cate);
		}

		if ($attr) {
			$this->save_pro_attr($attr, $pro_id);
		}
		empty($res) && error('添加失败', 400);
		admin_log('添加商品', $res);

		//cs($this->productM->log(),1);
		$Model->run();
		$redis->exec();
		return $res;
	}


	/*按id修改 @直接修改内容html，不生成新的HTML*/
	public function saveedit()
	{
		$id = post('id');
		(new ProductValidate())->goCheck('scene_find');
		$is_have = $this->productM->is_have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
		empty($is_have) && error('该商品不存在', 404);

		$redis = new \core\lib\redis();
		$Model = new \core\lib\Model();
		$Model->action();
		$redis->multi();
		//图片修改
		$img_json = post('img_json');
		$img_json_ar = json_decode($img_json, true);
		if (!empty($img_json_ar)) {
			$image_M = new \app\model\image();
			foreach ($img_json_ar as $rs) { //$key是图片在image表中的id
				$img_ar=array();
                if(isset($rs['id']) && $rs['id']>0){
					$img_ar['piclink'] = $rs['piclink'];
					$res = $image_M->up($rs['id'], $img_ar);
				} else {
					$img_ar['aid'] 	   = $id;
					$img_ar['cate']    = 'product';
					$img_ar['piclink'] = $rs['piclink'];
					$res = $image_M->save($img_ar);
				}
			}
		}

		$data = [];
		$data = post(['is_virtual','sid', 'types', 'title', 'sub_title', 'cost_price', 'cate_id', 'piclink', 'content', 'show', 'sort', 'sku_id', 'invent_sale', 'real_sale', 'send_score', 'day_limit_buy', 'all_limit_buy', 'is_coupon', 'is_realname', 'is_top', 'discount_number', 'discount', 'is_mail', 'attr', 'sku_json', 'is_check', 'weight', 'weight_fix', 'made_01', 'made_02', 'made_03','day_limit']);
		if (!empty($data)) {
			$rs = $this->productM->find($id);


			//主图
			if (!empty($img_json_ar)) {
				if ($data['piclink'] == '' &&  isset($img_json_ar[0]['piclink'])) {
					$data['piclink'] = $img_json_ar[0]['piclink']; //如没有设主图，默认第一张为主图
				}
			}

			//属于多个分类时，把其它分类ID 1@2@3 拆分并查出类别串 写进cate_ar 字段
			$cate_str = post('cate_ar');
			if ($cate_str) {
				$product_cate_M = new \app\model\product_cate();
				$cate_ar = explode('@', $cate_str);
				foreach ($cate_ar as $one) {
					if ($one) {
						$cate_ar_up[] = $product_cate_M->find_father($one, [$one]);
					}
				}
				$cate_ar_up = json_encode($cate_ar_up);
				$data['cate_ar'] = $cate_ar_up;
			} else {
				$data['cate_ar'] = '';
			}

			//商品属性改变 则清空product_attr 和 product_sku 中 pid为当前商品ID的记录再添加，否则只重写商品库存
			if ($rs['attr'] != $data['attr'] and !(empty($data['attr']) and isset($rs['iden']))) {
				$pro_attr_M = new \app\model\product_attr();
				$pro_sku_M = new \app\model\product_sku();
				$product_price_M = new \app\model\product_price();
				$cart_M = new \app\model\cart();
				$pro_attr_M->del_pid($id);
				$pro_sku_M->del_pid($id);
				$product_price_M->del_pid($id);
				$cart_M->del_pid($id);

				$this->save_pro_attr($data['attr'], $id);
				$sku_json = post('sku_json');
				$sku_json_ar = json_decode($sku_json, true);
				$p_s = $this->save_pro_sku($sku_json_ar, $rs['id']);
				$data['price'] = $p_s['price'];
				$data['stock'] = $p_s['stock'];
			}else{
				if ($rs['sku_json'] != $data['sku_json']) {
					$sku_json = post('sku_json');
					$sku_json_ar = json_decode($sku_json, true);
					$p_s = $this->save_pro_sku($sku_json_ar, $rs['id']);
					$data['price'] = $p_s['price'];
					$data['stock'] = $p_s['stock'];
				}
			}
			$res = $this->productM->up($id, $data);
		}
		
		empty($res) && error('修改失败', 404);
		//cs($this->productM->log());
		$Model->run();
		$redis->exec();
		return $res;
	}
	


	/*保存商品图片组*/
	public function save_pro_img($ar = [], $aid, $cate)
	{
		$image_M = new \app\model\image();
		foreach ($ar as $rs) {
			if (!empty($rs)) {
				$data['piclink'] = $rs['piclink'];
				$data['aid'] = $aid;
				$data['cate'] = $cate;
				$res = $image_M->save($data);
			}
		}
	}

	/*保存后台选定的产品属性*/
	public function save_pro_attr($ar, $pro_id)
	{
		$pro_attr_M = new \app\model\product_attr();
		if (isset($ar)) {
			$attr_rs = json_decode($ar, true);
			if (isset($attr_rs)) {
				foreach ($attr_rs as $rs) {
					$data_attr = array();
					$data_attr['sku_id'] = $rs['id'];
					$data_attr['sku_title'] = $rs['title'];
					$data_attr['parent_id'] = $rs['parent_id'];
					$data_attr['parent_title'] = $rs['parent_title'];
					if (isset($rs['piclink'])) {
						$data_attr['piclink'] = $rs['piclink'];
					}
					$data_attr['pid'] = $pro_id;
					$res = $pro_attr_M->save($data_attr);
					empty($res) && error('添加属性失败', 400);
				}
			}else{
            	error('添加属性失败', 400);
            }
		}
		return true;
	}

	/*保存库存 @从选择属性后生成的SKU表中填写对应价格，库存等  $ar为二维数组*/
	public function save_pro_sku($ar = [], $pid)
	{
		if(empty($ar)){
			error('请选择sku', 400);
		}
		$pro_sku_M = new \app\model\product_sku();
		$product_price_M = new \app\model\product_price();
		$price_ar = [];
		$price_ar_all = array();
		$stock = 0;
		if (!empty($ar)) {
		foreach ($ar as $rs) {
			if (empty($rs['price']) || $rs['price'] <= 0) {
				error('请输入销售价', 400);
			}
			if ($rs['price'] < $rs['cost_price']) {
				error('销售价必须大于等于成本价', 400);
			}
			$cost_price = $rs['cost_price'];
			$price_ar_all[] = $rs['price'];
			$stock=$stock+$rs['stock'];
			$new_ar = array();
			$sku_where=array();
			$sku_where['pid']=$pid;
			$sku_where['iden']=$rs['iden'];
			$sku_ar=$pro_sku_M->have($sku_where);
			if ($sku_ar) {
				//存在skuID
				$new_ar['price'] = $rs['price'];
				$new_ar['cost_price'] = $rs['cost_price'];
				$new_ar['hid'] = $rs['hid'];
				$new_ar['stock'] = $rs['stock'];
				$new_ar['iden'] = $rs['iden'];
				$res = $pro_sku_M->up($rs['id'], $new_ar);
				if (isset($rs['rating_json'])) {
				foreach ($rs['rating_json'] as $vo) {
					if ($vo['price'] == 0) {
						$price_where['sku_id']=$rs['id'];
						$price_where['rating']=$vo['id'];
						$product_price_M->del_all($price_where);
					} else {
						$price_where['sku_id']=$rs['id'];
						$price_where['rating']=$vo['id'];
						$price_ar_s=$product_price_M->have($price_where);
						if(empty($price_ar_s)){
							//$price_ar_all[] = $vo['price'];
							$price_ar = array();
							$price_ar['sku_id'] = $rs['id'];
							$price_ar['pid'] = $pid;
							$price_ar['price'] = $vo['price'];
							$price_ar['rating'] = $vo['id'];
							$product_price_M->save($price_ar);
						}else{
							//$price_ar_all[] = $vo['price'];
							$price_ar = array();
							$price_ar['price'] = $vo['price'];
							$product_price_M->up($price_ar_s['id'],$price_ar);
						}
					}
				}
				}else{
					$price_where['sku_id']=$rs['id'];
					$price_where['rating']=$vo['id'];
					$product_price_M->del_all($price_where);
				}
			}else{
				//不存在skuID
				$new_ar['pid'] = $pid;
				$new_ar['price'] = $rs['price'];
				$new_ar['cost_price'] = $rs['cost_price'];
				$new_ar['hid'] = $rs['hid'];
				$new_ar['stock'] = $rs['stock'];
				$new_ar['iden'] = $rs['iden'];
				$res = $pro_sku_M->save_back_id($new_ar);
				if (isset($rs['rating_json'])) {
					foreach ($rs['rating_json'] as $vo) {
						if ($vo['price'] != 0) {
							//$price_ar_all[] = $vo['price'];
							$price_ar = array();
							$price_ar['sku_id'] = $res;
							$price_ar['pid'] = $pid;
							$price_ar['price'] = $vo['price'];
							$price_ar['rating'] = $vo['id'];
							$product_price_M->save($price_ar);
						}
					}
				}
			}
		}
		}
		
		//求最低价，最高价
		$min_price = min($price_ar_all);
		$max_price = max($price_ar_all);
		if ($min_price == $max_price) {
			$price = $max_price;
		} else {
			$price = $min_price . "-" . $max_price;
		}
		$data['cost_price'] = $cost_price;
		$data['price'] = $price;
		$data['stock'] = $stock;
		return $data;
	}


	/*按id删除*/
	public function del()
	{
		$id = post('id');
		(new ProductValidate())->goCheck('scene_find');
		$rs = $this->productM->is_have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
		empty($rs) && error(' 商品不存在',404);
		//判断是否有红包是这个商品有的话提示有单品红包不让删除
		$this->check_have_coupon($id);


		//回滚开始
		$model = new \core\lib\Model();
		$redis = new \core\lib\redis();
		$model->action();
		$redis->multi();

		//没有的话删除的时候把会员领取的红包未使用的is_use状态给2
		$this->change_coupon($id);

		$where['pid'] = $id;
		(new \app\model\product_sku())->del_all($where);
		(new \app\model\product_attr())->del_all($where);
		(new \app\model\product_price())->del_all($where);
		$where_pic = (new \app\model\product_review())->find($where, 'id');
		(new \app\model\product_review_pic())->del_all($where_pic);
		(new \app\model\product_review())->del_all($where);
		$res = $this->productM->del($id);
		empty($res) && error('删除失败', 400);
		admin_log('删除商品', $id);

		$model->run();
		$redis->exec();
		//回滚END

		return $res;
	}

	
	public function check_have_coupon($id){
		$packet_M = new \app\model\packet();
		$where['cdn_pid'] = $id;
		$is_have = $packet_M->is_have($where);
		$is_have && error('商品有单品红 包不能删除',400);
	}


	//会员领取的红包未使用的is_use状态给2
	public function change_coupon($id){
		$coupon_M = new \app\model\coupon();
		$where['pid'] = $id;
		$where['is_use'] = 0;
		$rs = $coupon_M->lists_all($where);
		$data['is_use'] = 2 ;
		 if($rs){
			 foreach($rs as $one){
				$coupon_M->up($one['id'],$data);
			}
		}
	}





	/*批量删除*/
	public function del_all()
	{
		

		(new ProductValidate())->goCheck('scene_checkID');
		$id_str = post('id_str');
		$id_ar = explode('@', $id_str);
		$new_ar = [];


		//回滚开始
		$model = new \core\lib\Model();
		$redis = new \core\lib\redis();
		$model->action();
		$redis->multi();


		foreach ($id_ar as $one) {
			if ($one) {
				$rs = $this->productM->is_have(['id'=>$one,'sid'=>$GLOBALS['user']['id']]);
				empty($rs) && error('商品不存在',404);
				$where['pid'] = $one;
				(new \app\model\product_sku())->del_all($where);
				(new \app\model\product_attr())->del_all($where);
				(new \app\model\product_price())->del_all($where);
				$where_pic = (new \app\model\product_review())->find($where, 'id');
				(new \app\model\product_review_pic())->del_all($where_pic);
				(new \app\model\product_review())->del_all($where);
				$res = $this->productM->del($one);
				empty($res) && error('删除失败', 400);
			}
		}
		admin_log('删除商品', $id_str);

		$model->run();
		$redis->exec();
		//回滚END


		return $res;
	}


	/*批量上下架*/
	public function show_change()
	{
		(new ProductValidate())->goCheck('scene_checkID');
		$id_str = post('id_str');
		$show_type = post('show_type');
		$show_type = $show_type ? 1 : 0;
		$id_ar = explode('@', $id_str);
		if (!empty($id_ar)) {
			foreach ($id_ar as $one) {
				if ($one) {
					$rs = $this->productM->is_have(['id'=>$one,'sid'=>$GLOBALS['user']['id']]);
					empty($rs) && error('商品不存在',404);
					$id = $one;
					$data['show'] = $show_type;
					$res = $this->productM->up($id, $data);
					empty($res) && error('上架商品ID' . $id . '失败', 400);
				}
			}
		}
		if ($show_type == "1") {
			admin_log('批量上架商品', $id_str);
		} else {
			admin_log('批量下架商品', $id_str);
		}
		return $res;
	}





	/*复制商品 参数：商品id*/
	public function copy_product()
	{
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$rs = $this->productM->is_have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
		empty($rs) && error('商品不存在',404);
		$ar = $this->productM->find($id);
		unset($ar['id']);
		$new_id = $this->productM->save($ar);
		empty($new_id) && error('修改失败', 404);
		admin_log('复制商品', $new_id);

		//复制SKU  product_attr product_sku product_price images
		$product_attr_M = new \app\model\product_attr();
		$product_sku_M  = new \app\model\product_sku();
		$product_price_M = new \app\model\product_price();
		$image_M = new \app\model\image(); //aid,cate=product

		$where['pid'] = $id;
		$where_plus['aid'] = $id;
		$where_plus['cate'] = 'product';
		$ar_1 = $product_attr_M->lists_all($where);
		if ($ar_1) {
			foreach ($ar_1 as $one) {
				unset($one['id']);
				$one['pid'] = $new_id;
				$product_attr_M->save($one);
			}
		}
		$ar_2 = $product_sku_M->lists_all($where);
		if ($ar_2) {
			foreach ($ar_2 as $one) {
				$old_sku_id = $one['id'];
				unset($one['id']);
				unset($one['hid']);
				$one['pid'] = $new_id;
				$new_sku_id = $product_sku_M->save_back_id($one);
				$sku_change[$old_sku_id] = $new_sku_id; //product_price表的sku_id是product_sku表的id
				//echo $old_sku_id."==".$new_sku_id;
			}
		}


		$ar_3 = $product_price_M->lists_all($where);
		if ($ar_3) {
			foreach ($ar_3 as $one) {
				unset($one['id']);
				$one['pid'] = $new_id;
				$one['sku_id'] = $sku_change[$one['sku_id']];
				$product_price_M->save($one);
			}
		}

		$ar_4 = $image_M->lists_all($where_plus);
		if ($ar_4) {
			foreach ($ar_4 as $one) {
				unset($one['id']);
				$one['aid'] = $new_id;
				$image_M->save($one);
			}
		}
		return $new_id;
	}


	/*视频上传*/
	public function video_product()
	{
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$rs = $this->productM->is_have(['id'=>$id,'sid'=>$GLOBALS['user']['id']]);
		empty($rs) && error('商品不存在',404);
		$video = post("video");
		$data['video'] = $video;
		$res = $this->productM->up($id, $data);
		empty($res) && error('修改失败', 404);
		admin_log('修改商品商品', $res);
		return $res;
	}

	
	/*查SKU树*/
	public function show_sku()
	{
		$sku_id = post('sku_id', 0);
		$sku_M = new \app\model\sku();
		$data = $sku_M->find_tree($sku_id);
		return $data;
	}

    /*输入数字排序*/
	public function sort_by_number(){
		$sort = post('sort');
		$id = post('id');	
		$data['sort'] = $sort;
		$res = $this->productM->up($id,$data);
		empty($res) && error('排序失败',400);		
		admin_log('商品排序',$id);    
		return $res;
	}
}

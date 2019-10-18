<?php
/**
 * Created by yayue__god 
 * User: GOD
 * Date: 2019-03-14 09:49:07
 * Desc: 链接预选项
 */
namespace app\ctrl\admin;

class link extends BaseController
{
	/*配置*/
	public function lists()
	{
		$data = array(
			'homepage' => [
				'title' => '传统商城',
				'url' => '/page/vip?iden=home',
			],
			'vippage' => [
				'title' => '会员商城',
				'url' => '/page/member_shop',
			],
			'member' => [
				'title' => '会员中心',
				'url' => '/member/member',
			],
			'cart' => [
				'title'	=> '购物车',
				'url' => '/shop/shopcard',
			],
			'toappupload' => [
				'title'	=> '下载APP',
				'url' => '/toappupload',
			],
			'myshare' => [
				'title' => '推广二维码',
				'url' => '/member/ewm',
			],
			'product' => [
				'title' => '全部商品',
				'url' => '/shop/shopall',
			],
			'shopcate' => [
				'title' => '商品分类',
				'url' => '/shop/shopcate',
			],
			'product_cate' => [
				'title' => '指定商品分类',
				'url' => '',
			],
			'choose_product' => [
				'title' => '指定产品',
				'url' => '',
			],
			'news' => [
				'title' => '资讯快讯',
				'url' => '/news',
			],
			'news_cate' => [
				'title' => '文章分类',
				'url' => '',
			],
			'news_view' => [
				'title' => '指定文章',
				'url' => '',
			],
			'business' => [
				'title'  => '插件',
				'url' => '',
				'data'   => (new \app\model\plugin())->links(),
			],
			'custom_link' => [
				'title' => '自定义链接',
				'url' => '',
			],
		);
		return $data;
	}
}

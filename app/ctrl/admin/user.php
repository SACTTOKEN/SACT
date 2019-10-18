<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-24 13:48:22
 * Desc: 用户控制器
 */

namespace app\ctrl\admin;

use app\model\user as UserModel;
use app\validate\UserValidate;
use app\validate\IDMustBeRequire;

class user extends BaseController{
	
	public $userM;
	public function __initialize(){
		$this->userM = new UserModel();
	}

    /*保存,同时给副表生成一个UID*/
	public function saveadd(){
        (new UserValidate())->goCheck('admin_scene_add');
        
        $data = post(['password','pay_password','quhao','tel']);
		$data['password'] = md5($data['password'].'inex10086');
        $data['pay_password'] = md5($data['pay_password'].'inex10086');
        $data['username'] = $this->userM->get_sharecode(); //生成六位英文与数字的随机推广码,是求唯一，英文不能有o
        if($data['quhao']==''){
            $data['quhao']=86;
        }
		$res=$this->userM->save($data);
		empty($res) && error('添加失败',400);	 
        //im注册
        if(plugin_is_open("imhyjsnt")){
        $im = new \app\service\im();  
        $im->login_one($res,$data['username']);
        }
		admin_log('新建用户',$res);    
		return "添加成功";
	}

	/*按id删除,同时删除副表uid等于id的*/
	public function del()
	{
		(new UserValidate())->goCheck('scene_del');
		$id_str = post('id_str');
		$id_ar = explode('@',$id_str);

        if(is_array($id_ar)){
            foreach($id_ar as $one){
                $username = user_info($one,'username');
                $where['uid'] = $one;
                $is_tjr = $this->userM->is_tjr($one); //是否推荐人 该ID是其他会员的推存人时不能删
                if($is_tjr){                    
                    error($username."是其他会员的推荐人,请勿删除,可更改用户推荐人",'400');
                }
                $money_M = new \app\model\money();    
                $where2['uid'] = $one;   
                $where2['cate[!]'] = 'integral';           
                $is_have_1 = $money_M->is_have($where2);
                if($is_have_1){    
                    error($username."有流水记录,请勿删除,可更改用户信息",'400');
                }
                $c2c_buy_M = new \app\model\c2c_buy();
                $is_have_2 = $c2c_buy_M->is_have($where);   
                if($is_have_2){      
                    error($username."有C2C记录,请勿删除,可更改用户信息",'400');
                }
                $coin_order_M = new \app\model\coin_order();
                $order_M = new \app\model\order();
                $is_have_3 = $coin_order_M->is_have($where);
                $where3['uid'] = $one;   
                $where3['status[!]'] = '已关闭';          
                $is_have_4 = $order_M->is_have($where3);
                if($is_have_3 || $is_have_4){
                    error($username."有订单记录,请勿删除,可更改用户信息",'400');
                }
                $res=$this->userM->del($one);
                empty($res) && error('删除失败',400);
                admin_log('删除用户',$one);    
            }
        }

		return $res;
	}


	/*按id修改*/
	public function saveedit()
	{	
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
        $data_1 = post(['nickname','tel','avatar','province','city','area','town','quhao','designation']);
        if($data_1['quhao']==''){
            $data_1['quhao']=86;
        }
    	$res_1 = $this->userM->up($id,$data_1);
    	empty($res_1) && error('修改失败',404);
    	$user_attach_M = new \app\model\user_attach();
    	$data_2 = post(['name','card','admin_remark']);
    	$res_2 = $user_attach_M->up($id,$data_2);
        empty($res_2) && error('修改失败~',404);	
		admin_log('修改用户信息',$id);   
 		return $res_1; 
	}

    
	public function saveedit_password()
	{	
		$id = post('id');
    	(new UserValidate())->goCheck('scene_find');
    	$data = post(['username','password']);
    	$data['password'] = md5($data['password'].'inex10086');
		$res=$this->userM->up($id,$data);
		empty($res) && error('修改失败',404);
		admin_log('重置用户密码',$id);   
 		return $res; 
	}

	/*查所有*/
	public function lists()
	{   
        (new \app\validate\AllsearchValidate())->goCheck();
        (new \app\validate\PageValidate())->goCheck();
        $where = [];
        $username = post('username');
        $nickname = post('nickname');
        $rating   = post('rating');
        $province = post('province');
        $city     = post('city');
        $area     = post('area');
        $town     = post('town');
        $tel      = post('tel');
        $show     = post('show');
        $tid      = post('tid');
        $order_sn = intval(post('order_sn')); //1:按积分排序 2：按消费金额 3：按下级会员数 
        $created_time_begin = post('created_time_begin'); 
        $created_time_end   = post('created_time_end');

        $last_oid_time_begin  = post('last_oid_time_begin'); 
        $last_oid_time_end    = post('last_oid_time_end');  //最后下单时间 止

        $zxf_begin = post('zxf_begin');  //总消费起
        $zxf_end = post('zxf_end');

        $zcdd_begin = post('zcdd_begin');    //总出订单笔数
        $zcdd_end = post('zcdd_end');      

        $coin_rating = post('coin_rating'); //星球等级

       
        $last_ip = post('last_ip');
        $reg_ip = post('reg_ip');
        $types = post('types');


        $show = post('show');

        if($username){
            $where['user.username[~]'] = $username;
        }

        if($nickname){
            $where['user.nickname[~]'] = $nickname;
        }

        if($rating){
            $where['user.vip_rating'] = $rating;
        } 
        if($province){
            $where['user.province'] = $province;
        } 
        if($city){
            $where['user.city'] = $city;
        } 
        if($area){
            $where['user.area'] = $area;
        } 
        if($town){
            $where['user.town'] = $town;
        } 
        if($tel){
            $where['user.tel[~]'] = $tel;
        } 
        if(is_numeric($show)){
            $where['user.show'] = $show;
        } 
        if(is_numeric($tid)){
            $where['user.tid'] = $tid;
        }

        if(is_numeric($zcdd_begin)){
            $where['user_attach.zcdd[<>]'] = [$zcdd_begin,$zcdd_end];  //总出订单
        }

        if(is_numeric($zxf_begin)){
            $where['user_attach.buy[<>]'] = [$zxf_begin,$zxf_end]; 
        }

        if($created_time_begin>0){
            $created_time_end = $created_time_end ? $created_time_end : time();
            $created_time_end = $created_time_end + 3600*24;
            $where['user.created_time[<>]'] = [$created_time_begin,$created_time_end];
        }


        if($last_oid_time_begin>0){
            $order_M = new \app\model\order();
            $last_oid_time_end = $last_oid_time_end + 3600*24;
            $uid_ar = $order_M->find_time_oid($last_oid_time_begin,$last_oid_time_end); //返回最后下单的用户组
            $where['user.id'] = $uid_ar;
        }

        if($coin_rating){
            $where['user.jddj_rating'] = $coin_rating;
        }

        if($last_ip){
            $where['user.last_ip'] = $last_ip;
        }

        if($reg_ip){
            $where['user.reg_ip'] = $reg_ip;
        }
        if($types){
            switch ($types) { //1:普通会员2供应商3代理商
                case '1':
                    $where['is_supplier'] = 0;
                    $where['is_agent'] = 0;
                    break;
                case '2':
                     $where['is_supplier'] = 1;
                    break;
                case '3':
                    $where['is_agent[>]'] = 0;
                    break;    
                default:
            }
        }

        switch ($order_sn) { //1:按积分排序 2：按累计消费金额 3：按总下级会员数 4:币消费金额   5: 矿机总分销人数(币下级人数)
            case '1':
                $where['ORDER'] = ["user.integral"=>"DESC"];
                break;
            case '2':
                $where['ORDER'] = ["user_attach.buy"=>"DESC"];
                break;
            case '3':
                $where['ORDER'] = ["user_attach.znumber"=>"DESC"];
                break;    
            case '4':
                $where['ORDER'] = ["user_attach.coin_buy"=>"DESC"];
                break;
            case '5':
                $where['ORDER'] = ["user_attach.coin_zvip"=>"DESC"];
                break; 
            case '6':
                $where['ORDER'] = ["user.coin"=>"DESC"];
                break;
            case '7':
                $where['ORDER'] = ["user.coin_storage"=>"DESC"];    
                break;
            default:
                $where['ORDER'] = ["user.id"=>"DESC"];
        }    
      
		$page=post("page",1);
		$page_size = post("page_size",10);		
		$data=$this->userM->lists($page,$page_size,$where);
        // var_dump($this->userM->log());
        // exit();

		$reward_M = new \app\model\reward();
		$reward_ar = $reward_M->title_by_types(2); //types为2的奖励组
        $coin=['coin','coin_storage','integrity','USDT','BTC','ETH','LTC','BCH'];

        $jddj_rating_M = new \app\model\jddj_rating();
		 $vip_rating_M = new \app\model\vip_rating();
		foreach($data as &$rs){
            $rs['tid_cn']  = user_info($rs['tid'],'username');
            if($rs['jddj_rating']){
                $rs['coin_rating_cn'] = $jddj_rating_M->find($rs['jddj_rating'],'title'); //矿机等级中文 和商城等级不同，只要等级中文和ID。
            }
			if($rs['vip_rating']){
                $rs['rating_cn'] = $vip_rating_M->find($rs['vip_rating'],'title'); //矿机等级中文 和商城等级不同，只要等级中文和ID。
            }
            $reward=array();
			foreach($reward_ar as &$one){
                $one['value'] = $rs[$one['iden']] ? $rs[$one['iden']] : '0.000000';
                if(in_array($one['iden'], $coin)){
                    $reward[0][]=$one;
                }else{
                    $reward[1][]=$one;
                }
			}
			$rs['reward_ar'] = $reward;
			unset($one);
		}
		unset($rs);
        unset($where['ORDER']);
        $count = $this->userM->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}
	

//================= 以上是基础方法,以下为相应功能方法 ==================

	/*激活*/
	public function change_show(){
		(new UserValidate())->goCheck('scene_check');
		$id = post("id",'');
		$data['show'] = post('show',1);
        $res = $this->userM->up($id,$data);
        if($data['show']==1){
            admin_log('激活用户',$id);   
        }else{
            admin_log('冻结用户',$id);   
        }
		return $res;
	}

	/*关注*/
	public function im_attention(){
		(new IDMustBeRequire())->goCheck();
		(new UserValidate())->goCheck('is_im_attention');
		$id = post("id");
		$data['is_im_attention'] = post('is_im_attention');
        $res = $this->userM->up($id,$data);
        if($data['is_im_attention']==1){
            admin_log('关注用户',$id);   
        }else{
            admin_log('取消关注用户',$id);   
        }
		return $res;
    }
    
	/*关注列表*/
	public function im_attention_lists(){
        (new \app\validate\PageValidate())->goCheck();
        (new \app\validate\AllsearchValidate())->goCheck();
        $page=post("page",1);
        $page_size = post("page_size",10);		
        
        $title = post("title");
        if($title){
            $where['OR']['username[~]'] = $title;
            $where['OR']['nickname[~]'] = $title;
            $where['OR']['tel[~]'] = $title;
            $where['OR']['im[~]'] = $title;
        }else{
            $where['is_im_attention']=1;
        }
		$data=$this->userM->lists_have($page,$page_size,$where,['username','im','nickname','avatar']);
		
		$count = $this->userM->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
	}


	/*详情*/
	public function info(){
		(new IDMustBeRequire())->goCheck();
		$id = post('id');
		$data = [];
		$data['base'] = user_info($id);
        $users=user_info($data['base']['tid']);
		$data['base']['openid'] = $this->userM->find($id,'openid');
		$data['base']['tid_cn'] = $users['username'];
        $data['base']['tid_nickname'] = $users['nickname'];

        $data['base']['last_ip_address'] = ip_address($data['base']['last_ip']);
        $data['base']['reg_ip_address']  = ip_address($data['base']['reg_ip']);




        //消费能力
        $user_attach_M = new \app\model\user_attach();
        $attach = $user_attach_M->find($id);
        $data['attach'] = $attach;
        $coin_where['uid']=$id;
        $coin_where['status']=1;
        $data['attach']['coin_number'] = (new \app\model\coin_order())->new_count($coin_where);
        $data['xf']['zxfjr'] = $attach['buy']; //总消费金额
        $data['xf']['byxf']  = $attach['buy_month']; //本月消费


        //强制更新，如前台下单时写入数据，这里可注释掉
        $order_M = new \app\model\order();
        $where_order['uid'] = $id;
        $data['xf']['zcdd'] = $order_M->new_count($where_order); //总出订单数
        $user_attach_M = new \app\model\user_attach();
        $user_attach_M->up($id,['zcdd'=>$data['xf']['zcdd']]);
        //强制更新END


        $order_M = new \app\model\order();
        $where_a['uid'] = $id;
        $order = $order_M->lists(1,10,$where_a);
        $data['order'] = $order;


		//会员业绩
		// $user_attach_M = new \app\model\user_attach();
		// $attach = $user_attach_M->find($id);
		// $data['attach'] = $attach;

        $data['sc']['ynumber'] = $attach['ynumber']; //一级人数
        $data['sc']['znumber']  = $attach['znumber'];  //市场总出订单
        $data['sc']['yvip']  = $attach['yvip'];   //一级分销
        $data['sc']['zvip']  = $attach['zvip']; //总分销
        $data['sc']['ysales']  = $attach['ysales'];  //一级业绩
        $data['sc']['zsales']  = $attach['zsales']; //总级业绩

		$reward_M = new \app\model\reward();
		$style_cn = $reward_M->lists();
        $style_cn = array_column($style_cn,NULL,'iden');
        
		//市场能力
		$money_M = new \app\model\money();
        $where_money['cate[!]'] = ['coin','coin_storage','integrity','USDT','BTC','ETH','LTC','BCH'];
		$money = $money_M->lists_one($id,1,100,$where_money);
		foreach($money as $key=>$rs){
            $users=user_info($rs['uid']);
			//会员账号 与 昵称 图像 等级
			$money[$key]['username']  = $users['username'];
			$money[$key]['nickname']  = $users['nickname'];
			$money[$key]['avatar']  =  $users['avatar'];
			$money[$key]['rating_cn']  = $users['rating_cn'];
			
       		//加减符号 1加2减   
       		if($rs['types'] == 2){
       			$money[$key]['money'] = "-".$money[$key]['money'];
       		}else{
       			$money[$key]['money'] = "+".$money[$key]['money'];
       		}

       		//奖励类型 到reward中去查
       		$money[$key]['style_cn'] = $style_cn[$rs['cate']]['title']; 
       		//来源 
			$users=user_info($rs['ly_id']);
            $money[$key]['ly_name'] =  $users['username']; 
			$money[$key]['ly_nickname']  = $users['nickname'];
       		$money[$key]['ly_rating_cn'] = $users['rating_cn'];
       	}	
        $data['money'] = $money;
        

        $coin_where_money['cate'] = ['coin','coin_storage','integrity','USDT','BTC','ETH','LTC','BCH'];
		$coin_money = $money_M->lists_one($id,1,100,$coin_where_money);
		foreach($coin_money as $key=>$rs){
            $users=user_info($rs['uid']);
			//会员账号 与 昵称 图像 等级
			$coin_money[$key]['username']  = $users['username'];
			$coin_money[$key]['nickname']  = $users['nickname'];
			$coin_money[$key]['avatar']  =  $users['avatar'];
			$coin_money[$key]['rating_cn']  = $users['coin_rating_cn'];
			
       		//加减符号 1加2减   
       		if($rs['types'] == 2){
       			$coin_money[$key]['money'] = "-".$coin_money[$key]['money'];
       		}else{
       			$coin_money[$key]['money'] = "+".$coin_money[$key]['money'];
       		}

       		//奖励类型 到reward中去查
       		$coin_money[$key]['style_cn'] = $style_cn[$rs['cate']]['title']; 
       		//来源 
			$users=user_info($rs['ly_id']);
       		$coin_money[$key]['ly_name'] =  $users['username']; 
            $coin_money[$key]['ly_nickname']  = $users['nickname'];
       		$coin_money[$key]['ly_rating_cn'] = $users['coin_rating_cn'];
       	}	
		$data['coin_money'] = $coin_money;

        //签到
        
        $sign_in_M = new \app\model\sign_in();

        $where_sign_in['uid'] = $id;
        $sign_in = $sign_in_M->lists(1,10,$where_sign_in);

        $data['sign_in'] = $sign_in;

        //优惠券        
        $coupon_M = new \app\model\coupon();
        $where_coupon['uid'] = $id;
        $coupon = $coupon_M ->lists(1,10,$where_coupon);
        $data['coupon'] = $coupon;

		//下级会员
		// $where_b['tid'] = $id;
		// $page=post("page",1);
		// $page_size = post("page_size",10);	
		// $next_user_ar = $this->userM->lists($page,$page_size,$where_b);
		// $data['next_user'] = $next_user_ar;
		
		//收款信息 副表attach
		//var_dump($this->userM->log());
		return $data;
	}


	/*编辑会员*/
    public function edit(){
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$data = $this->userM->find_all($id);
    	empty($data) && error('数据不存在',404);    	
        return $data;      
    }

    /*设等级界面*/	
    public function rating_edit(){
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$data['id'] = $id;
    	$data['rating'] = $this->userM->find($id,'vip_rating');
        $data['lock_rating'] = $this->userM->find($id,'lock_rating');
        $data['coin_rating'] = $this->userM->find($id,'jddj_rating');
    	return $data;
    }

    /*等级界面保存*/
    public function rating_saveedit(){
    	(new IDMustBeRequire())->goCheck();
        
        (new UserValidate())->goCheck('rating_saveedit');
    	$id = post('id');
    	$user = $this->userM->find($id,['tid','vip_rating','lock_rating','jddj_rating']);

    	$data['vip_rating'] = post('rating');
        $data['lock_rating'] = post('lock_rating');
        $data['jddj_rating'] = post('coin_rating');
		
        if($data['vip_rating']!=$user['vip_rating']){
            $data2['upgrade_time']=time();
            (new \app\model\user_attach())->up($id,$data2);
        }
        if($data['jddj_rating']!=$user['jddj_rating']){
            $data2['coin_upgrade_time']=time();
            (new \app\model\user_attach())->up($id,$data2);
        }
    	$res = $this->userM->up($id,$data);
        empty($res) && error('修改失败',404);
        
        $data_rating['vip_rating']=$data['vip_rating'];
        $data_rating['jddj_rating']=$data['jddj_rating'];
        $user_gx_M = new \app\model\user_gx();
        $user_gx_M->up($id,$data_rating);
        /*
        $data_t_rating['t_rating']=$data['rating'];
        $data_t_rating['t_coin_rating']=$data['coin_rating'];
        $user_gx_M->up_all(['tid'=>$id],$data_t_rating);

        if($user['rating']==1 && $data['rating']>1){
            if($user['tid']){
                $users = new \app\service\user();
                $users -> mall_recommend_vip($id);
            }
        }
		*/
        if($user['vip_rating']<3 && $data['vip_rating']>2){
            if($user['tid']){
                $users = new \app\service\user();
                $users -> vip_recommend_vip($id);
            }
        }
		/*
        if($user['tid'] && $data['coin_rating']>$user['coin_rating'] && $user['coin_rating']>1){
        $rating = new \app\service\rating();
        $rating -> coin($user['tid']);
        }
		*/
		admin_log('修改用户星球等级：'.$data['vip_rating'].'，节点等级：'.$data['jddj_rating'].'',$id);   
		return $res;    
    }


    /*修改推荐人*/
    public function change_tid(){
        
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$tid_cn = post('tid_cn');
    	if(empty($tid_cn)){
    		$tid=0;
    	}else{
    		$tid=$this->userM->find_uid($tid_cn);
    		if(!$tid){error('该推荐人不存在',404);}
    		$user_S = new \app\service\user();
    		$judge_res = $user_S->judge_tid($id,$tid);
    		empty($judge_res) && error('该推荐人是你的下级会员！',404);
        }
        
    	$tid_yl=$this->userM->find($id,"tid");
        if($tid==$tid_yl){
            error('该用户已经是你的推荐人！',404);
        }

        $redis = new \core\lib\redis();
        $Model = new \core\lib\Model();
        $Model->action();
        $redis->multi();

        $data['tid'] = $tid;
        $res = $this->userM->up($id,$data);
        empty($res) && error('修改失败',404);
       
        //统计会员数据
        $users = new \app\service\user();
        if($tid_yl){
            $rating=$this->userM->find($id,["rating","coin_rating","vip_rating"]);
            if($rating['vip_rating']>2){
                $users -> vip_recommend_vip($id,'-');
            }
            if($rating['coin_rating']>1){
                $users -> coin_recommend_vip($id,'-');
            }
            if($rating['rating']>1){
                $users -> mall_recommend_vip($id,'-');
            }
            $users -> recommend_remove($id);
        }
        if($data['tid']){
            $rating=$this->userM->find($id,["rating","coin_rating","vip_rating"]);
            $users -> recommend($id,$data['tid'],1,$rating['rating'],$rating['coin_rating']);
            $users -> subordinate($id,$data['tid']);
            if($rating['coin_rating']>1){
                $users -> coin_recommend_vip($id);
            }
             if($rating['vip_rating']>2){
                $users -> vip_recommend_vip($id);
            }
            if($rating['rating']>1){
                $users -> mall_recommend_vip($id);
            }
            
        }
        $Model->run();
        $redis->exec();
		admin_log('修改用户推荐人原推荐人：'.$tid_yl.'，修改后推荐人：'.$tid.'',$id);   
		return $res;    
    }

    /*发送站内信*/
    public function send_letter(){
    	(new IDMustBeRequire())->goCheck();
        (new UserValidate())->goCheck('scene_letter');
        
    	$id = post('id');
    	$content = post('content');
    	$user_letter_M = new \app\model\user_letter();
    	$data['uid'] = $id;
    	$data['content'] = $content;
    	$res = $user_letter_M->save($data);
    	empty($res) && error('修改失败',404);
		admin_log('发送站内信',$res);   
    	return $res;
    }

    /*后台重置会员登录密码*/
    public function reset_password(){
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$username = post('username');
    	$my_name = $this->userM->find($id,'username');
    	($my_name!=$username) && error('该会员不存在',400);    	
    	$new = rand(10000000,99999999);
    	$new_password = md5($new.'inex10086');
    	$res = $this->userM->change_password($id,$new_password);
    	empty($res) && error('重置失败',400);
		admin_log('重置密码',$id);   
        return $new;
    }

    /*后台重置会员支付密码*/ 
    public function reset_pay_password(){
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$username = post('username');
    	$my_name = $this->userM->find($id,'username');
    	($my_name!=$username) && error('该会员不存在',400);   	
    	$new = rand(100000,999999);
    	$new_password = md5($new.'inex10086');
    	$res = $this->userM->change_pay_password($id,$new_password);
    	empty($res) && error('重置失败',400);
		admin_log('重置支付密码',$id);   
        return $new;
    }

    /*重置二维码*/
    public function reset_ewm(){
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$username = post('username');
    	$my_name = $this->userM->find($id,'username');
    	($my_name!=$username) && error('该会员不存在',400);
    	$data['wx_ewm'] = '';
    	$data['wy_ewm'] = '';
    	$user_attach_M = new \app\model\user_attach();
    	$res = $user_attach_M->up($id,$data);
    	empty($res) && error('重置失败',400);
		admin_log('重置二维码',$id);   
    	return $res;
    }

    /*解除微信绑定*/
    public function unlink_wechat(){
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$data['openid'] = '';
    	$res =  $this->userM->up($id,$data);
    	empty($res) && error('解除失败',400);
		admin_log('解除微信绑定',$id);   
    	return $res;
    }

    /*微信二维码*/
    public function reset_wx_ewm(){
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
    	$wechat_S = new \app\service\wechat();
    	$qrcode = $wechat_S -> gzh_ewm($id);
        if(!$qrcode){
                error('生成二维码错误',400);
        }
        $web = c('wx_mobile_web'); //http://www.szpg88.com  

        $user_attach_M = new \app\model\user_attach(); 
        $user_attach_M->up($id,['wx_ewm'=>$qrcode]);
    	return $web."/api".$qrcode."?id=".rand(0,1000);
    }

    //供应商二维码
    public function supplier_ewm()
    {
    	(new IDMustBeRequire())->goCheck();
    	$id = post('id');
        return c('wx_mobile_web').'/supplier/supplierdetails?id='.$id;
    }


    /*重置所有微信二维码*/
    public function reset_allwx_ewm(){
        $user_M = new \app\model\user();
        $ar = $user_M->lists_all();
        $wechat_S = new \app\service\wechat();
        foreach($ar as $one){
            $id = $one['id'];    
            $wechat_S -> gzh_ewm($id);
        }
    }


    public function empty_qr()
    {
        $user_attach_M = new \app\model\user_attach();
        $user_attach_M->up_all([],['wy_ewm'=>'','wx_ewm'=>'']);
        return '清空完成';
    }



    /*网页二维码*/
    public function reset_wy_ewm(){
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $user_M = new \app\model\user();
        $user_attach_M = new \app\model\user_attach();
        $user = $user_M->find($id); 
        $mobile_web = c('wx_mobile_web'); //http://www.szpg88.com  
        $url=$mobile_web.'/login?rk=reg&share='.$user['username'];    //用户名就是推荐码     
        $errorCorrectionLevel = 'm';//容错级别   
        $matrixPointSize = 6;//生成图片大小 
        $wy_ewm='resource/image/wy_wem/'.$id.'.png';
        \core\lib\QRcode::png($url, $wy_ewm, $errorCorrectionLevel, $matrixPointSize, 2);    
        $user_attach_M->up($user['id'],['wy_ewm'=>$wy_ewm]);
        return $mobile_web."/api".$wy_ewm;
    }

    /*编辑供应商*/
    public function supplier_edit(){
        (new IDMustBeRequire())->goCheck();
        $id = post('id');
        $pa = post(['shop_title','is_supplier']);
        $data_1['is_supplier'] = $pa['is_supplier'];
        $data_2['shop_title'] = $pa['shop_title'];
        $res = $this->userM->up($id,$data_1);
        $user_attach_M = new \app\model\user_attach();
        $res2 = $user_attach_M->up($id,$data_2);
        empty($res) && error('操作失败',400);
		admin_log('编辑供应商资料',$id);   
        return $res;
    }

    /*编辑代理商*/
    public function agent_edit(){
        (new IDMustBeRequire())->goCheck('agent_edit');
        $id = post('id');
        $pa = post(['is_agent','agent_province','agent_city','agent_area','agent_town']);
        $data_1['is_agent'] = $pa['is_agent'];
        $data_1['agent_province'] = $pa['agent_province'];
        $data_1['agent_city'] = $pa['agent_city'];
        $data_1['agent_area'] = $pa['agent_area'];
        $data_1['agent_town'] = $pa['agent_town'];
        $res = $this->userM->up($id,$data_1);
        empty($res) && error('操作失败',400);
		admin_log('编辑代理商',$id);   
        return $res;
    }


    /*查供应商所有*/
    public function supplier_lists()
    {
        $where= [];
        $username = post("username");
        $nickname = post("nickname");
        if($username){     
            $where['uid'] =$this->userM->find_mf_uid($username);
        }
        if($nickname){
        	$where['uid'] = $this->userM->find_mf_uid_plus($nickname);
        }
        $page=post("page",1);
        $page_size = post("page_size",10);   
        $where['is_supplier'] = 1;  
        $data=$this->userM->lists($page,$page_size,$where);
        $reward_M = new \app\model\reward();
        $reward_ar = $reward_M->title_by_types(2); //types为2的奖励组
        foreach($data as &$rs){
            $rs['tid_cn'] = user_info($rs['tid'],'username');
            foreach($reward_ar as &$one){
                $one['value'] = $rs[$one['iden']] ? $rs[$one['iden']] : '0.000000';
            }
            $rs['reward_ar'] = $reward_ar;
            unset($one);
        }
        unset($rs);

        $count = $this->userM->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }

    /*查代理商所有*/
    public function agent_lists()
    {
        $where= [];
        $username = post("username");
        $nickname = post("nickname");
        if($username){     
            $where['uid'] =$this->userM->find_mf_uid($username);
        }
        if($nickname){
        	$where['uid'] = $this->userM->find_mf_uid_plus($nickname);
        }
        $page=post("page",1);
        $page_size = post("page_size",10);   
        $where['is_agent[>]'] = 0;  
        $data=$this->userM->lists($page,$page_size,$where);
        $reward_M = new \app\model\reward();
        $reward_ar = $reward_M->title_by_types(2); //types为2的奖励组
        $money_M=new \app\model\money();
        foreach($data as &$rs){
            $rs['tid_cn'] = user_info($rs['tid'],'username');
            foreach($reward_ar as &$one){
                $one['value'] = $rs[$one['iden']] ? $rs[$one['iden']] : '0.000000';
            }
            $rs['reward_ar'] = $reward_ar;
            
            $rs['agent_order']=$money_M->new_count(['uid'=>$rs['id'],'iden'=>'agentaward']);
            $rs['agent_reward']=$money_M->find_sum('money',['uid'=>$rs['id'],'iden'=>'agentaward']);
            unset($one);
        }
        unset($rs);

        $count = $this->userM->new_count($where);
        $res['all_num'] = $count;
        $res['all_page'] = ceil($count/$page_size);
        $res['page'] = $page;
        $res['data'] = $data; 
        return $res; 
    }



    public function im_user_info(){
        $im_uid = post('im_uid'); //im账号
        $data = $this->userM->find_where(['im'=>$im_uid]);
        return user_info($data);
    }


    /*聚力兽 -用户添加关联账号*/
    public function add_mytel(){
        (new IDMustBeRequire())->goCheck();
        (new \app\validate\DragCloudValidate())->goCheck('add_mytel');
        $up_id = post('id');
        $tel_str = post('tel_str');
        $tel_ar = explode('@',$tel_str);

        if(count($tel_ar)>0){
            $user_M = new \app\model\user();
            foreach($tel_ar as $one){
                $rule = '^1(3|4|5|6|7|8|9)[0-9]\d{8}$^';
                $result = preg_match($rule, $one);
                if(!$result){
                    error($one."手机格式不正确",400);
                }
                $where['tel'] = $one;
                $is_have = $user_M->have($where);
                if(!$is_have){
                    error($one."用户不存在",400);
                }
                if($is_have['id'] == $up_id){
                    error('不能关联自己',400);
                }         
            }
            $user_contact_M = new \app\model\user_contact();
            foreach($tel_ar as $one){
                $where['tel'] = $one;
                $down_id = $user_M->have($where,'id');
                $data['up_id'] = $up_id;
                $data['down_id'] = $down_id;
                $is_have = $user_contact_M->is_have($data);
                if(!$is_have){
                    $res = $user_contact_M->save($data);
                    empty($res) && error('关联失败',400);
                }                
            }
        }
        return true;
    }




}
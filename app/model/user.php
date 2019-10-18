<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/24
 * Desc: 用户模型
 */
namespace app\model;


class user extends BaseModel
{
    public $title  = 'user';

    public $title2 = 'user_attach';

    public $title3 = 'rating';

    public $title4 = 'coin_rating';
	public $title5 = 'vip_rating';
	public $title6 = 'jddj_rating';


	/**
     * 模型查找where
     * @param where 数组
     * @return data 返回布尔
     */
	public function is_find($where){
    	$data=$this->has($this->title,$where);
        return $data;      
    }


	/**
     * 模型查找where
     * @param where 数组
     * @return data 返回布尔
     */
	public function find_where($where,$field='id'){
    	$data=$this->get($this->title,$field,$where);
        return $data;      
    }


    public function find_me($id){
        $data=$this->get($this->title,'*',["AND"=>['id'=>$id]]);
        $data2=$this->get($this->title2,'*',["AND"=>['uid'=>$id]]);
        unset($data2['id']);
        if(!($data && $data2)){
            error('用户不存在',404); 
        }
        $data_all = array_merge($data,$data2);
        unset($data_all['password']);
        unset($data_all['pay_password']);
        unset($data_all['openid']);
        return $data_all;

    }

    public function is_tjr($id){
        return $this->has($this->title,['tid'=>$id]);
    }

    public function find_by_im($im){
        $data=$this->get($this->title,'*',["AND"=>['im'=>$im]]);
    }


	/**
     * 模型查找id数据
     * @param id 数字
     * @return data 返回一条数据
     */
	public function find_all($id){
    	$data=$this->get($this->title,'*',["AND"=>['id'=>$id]]);
        $data2=$this->get($this->title2,'*',["AND"=>['uid'=>$id]]);

        $rating_cn=$this->get($this->title3,'title',["AND"=>['id'=>$data['rating']]]); //商城等级
        $coin_rating_cn = $this->get($this->title4,'title',['id'=>$data['coin_rating']]); //矿机等级
		$vip_rating_cn = $this->get($this->title5,'title',['id'=>$data['vip_rating']]); //VIP等级
		$jddj_rating_cn = $this->get($this->title6,'title',['id'=>$data['jddj_rating']]); //节点等级

        $data_all = $data;
        if(is_array($data_all) && is_array($data2)){
            $data_all = array_merge($data_all,$data2);
        }
		$data_all['jddj_rating_cn'] = $jddj_rating_cn;
		$data_all['vip_rating_cn'] = $vip_rating_cn;
        $data_all['coin_rating_cn'] = $coin_rating_cn;    
        $data_all['rating_cn'] = $rating_cn;
      
        unset($data_all['id']);
        unset($data_all['password']);
        unset($data_all['pay_password']);
        unset($data_all['openid']);
        $data_all['id'] = $id;
        return $data_all;      
    }


    /**
     * 按用户账号找id
     */
    public function find_uid($username){
        $uid = $this->get($this->title,'id',["AND"=>['username'=>$username]]);
        return $uid;
    }

    /*模糊查找*/
    public function find_mf_uid($username){
        $where['OR']=[
            'username'=>$username,
            'tel'=>$username,
        ];
        $ar = $this->get($this->title,'id',$where); 
        return $ar;
    }
    
    

    public function find_mf_uid_plus($nickname){
        $ar = $this->select($this->title,'id',["AND"=>['nickname[~]'=>$nickname]]); 
        return $ar;
    }
    

    public function list_where($where,$field='id'){
        $ar = $this->select($this->title,$field,$where); 
        return $ar;
    }


    /**
     * 模型添加数据规则
     * @param data 数据
     * @return 当前操作ID
     */
    public function save($data){
        $data['created_time'] = time();
        $data['update_time'] = time();
        $do = $this->insert($this->title,$data);          
        //$do = $do->rowCount();  
        $do = $this->id();
        if($do>0){
            $data_a['uid']= $do;
            $this->insert($this->title2,$data_a);       
        }
        if($do || $do===0){return $do;}else{return 0;}  
    }


    /**
     * 模型修改数据规则
     * @param data 数据
     * @return BOOL
     */
    public function up($id,$data){
        $data['update_time'] = time();
        $this->update($this->title,$data,['id'=>$id]);

        $redis = new \core\lib\redis();
        $rd_name = 'user:'.$id; 
        foreach($data as $key=>$val){  //自增，自减 $key里含有[+],[-]
            if(strpos($key,'[+]')>0 || strpos($key,'[-]')>0){
                $key = str_replace('[+]','',$key);   
                $key = str_replace('[-]','',$key);               
                $val_now = $this->find($id,$key);
                $redis->hset($rd_name,$key,$val_now);
            }else{
                $redis->hset($rd_name,$key,$val);
                if($key=='rating'){
                    $rating_cn=$this->get($this->title3,'title',['id'=>$val]); //商城等级
                    $redis->hset($rd_name,'rating_cn',$rating_cn);
                }
                if($key=='coin_rating'){
                    $coin_rating_cn = $this->get($this->title4,'title',['id'=>$val]); //矿机等级
                    $redis->hset($rd_name,'coin_rating_cn',$coin_rating_cn);
                }
				
				if($key=='vip_rating'){
                    $vip_rating_cn = $this->get($this->title5,'title',['id'=>$val]); //VIP等级
                    $redis->hset($rd_name,'vip_rating_cn',$vip_rating_cn);
                }
				
				if($key=='jddj_rating'){
                    $jddj_rating_cn = $this->get($this->title6,'title',['id'=>$val]); //节点等级
                    $redis->hset($rd_name,'jddj_rating_cn',$jddj_rating_cn);
                }
            }
        }
        
		return $this->doo();  
    }


    /**
     * 模型删除数据规则
     * @param data 数据
     * @return BOOL
     */
    public function del($id){
        //统计会员数据
        $users = new \app\service\user();
        $tid_yl=$this->find($id,"tid");
        if($tid_yl){
            $rating=$this->find($id,["rating","coin_rating"]);
            if($rating['coin_rating']>1){
                $users -> coin_recommend_vip($id,'-');
            }
            if($rating['rating']>1){
                $users -> mall_recommend_vip($id,'-');
            }
            $users -> recommend_remove($id);
        }

        $this->delete($this->title2,['uid'=>$id]);
        $this->delete($this->title,['id'=>$id]);
        if(is_array($id)){
            foreach($id as $one){
            $redis = new \core\lib\redis();
            $rd_name = 'user:'.$one;
		    $redis->hdel($rd_name);
            }
        }else{
            $redis = new \core\lib\redis();
            $rd_name = 'user:'.$id;
		    $redis->hdel($rd_name);
        }
        return $this->doo();  
    }


    public function im_user_list(){
        $data=$this->select($this->title,['id','username','avatar'],['ORDER'=>["id"=>"DESC"]]);        
        return $data;    
    }



    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists($page=1,$number=10,$where_base=[]){ 
        $startRecord=($page-1)*$number;
        $where_other = ["LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);
        $data = $this->select($this->title,[
            "[>]user_attach" => ["id"=>"uid"],
            "[>]rating" => ["rating" => "id"]
        ],[
            "user.id",
            "user.avatar",
            "user.supply",
            "user.username",
            "user.tel",
            "user.nickname",
            "user.tid",
            "user.share",
            "user.im",
            "user.rating",
            "rating.title(rating_cn)",
            "user.created_time",
            "user.money",
            "user.integral",
            "user.integrity",
            "user.amount",
            "user.coin",
            "user.coin_storage",
            "user.coin_rating",
			"user.vip_rating",
			"user.jddj_rating",
            "user.BTC",
            "user.USDT",
            "user.ETH",
            "user.LTC",
            "user.BCH",
            "user.is_agent",
            "user.is_supplier",
            "user.weight",
            "user.gold",
            "user.agent_province",
            "user.agent_city",
            "user.agent_area",
            "user.agent_town",
            "user_attach.name",
            "user_attach.shop_title",
            "user_attach.ynumber",
            "user_attach.yvip",
            "user_attach.buy",
            "user_attach.sum_amount",
            "user.show",
            "user_attach.goods_money",
            "user_attach.s_order_num",
            "user_attach.s_sales",
            "user_attach.s_settled",
            "user_attach.s_unsettled",
            "user_attach.admin_remark",
            "user_attach.coin_yvip", //矿机一级分销人数
            "user_attach.coin_zvip", //矿机总分销人数
            "user_attach.coin_buy", //虚拟币累计消费
            "user_attach.sum_coin", //虚拟币累计佣金
            "user_attach.shop_logo", //虚拟币累计佣金
            "user_attach.shop_address", //虚拟币累计佣金
			"user.viprd_usdt", //USDT未释放
			"user.viprd_ptb", //星际链未释放
			"user.mxq_fcsl", //飞船
			
			"user.viprd_ljje", //累计入单
			"user.viprd_ljed", //奖励总额度
			"user.viprd_ysf", //奖励已释放
			"user.viprd_wsf", //奖励未释放
			
			"user.USDT_storage", //奖励未释放
			"user.USDT_KY", //奖励未释放
			"user.LMJJ", //奖励未释放
			"user.XJJJ", //奖励未释放
			"user.LMJJA", //奖励未释放
			"user.LMJJB", //奖励未释放
			"user.LMJJC", //奖励未释放
			"user.sactloop", //奖励未释放
        ],$where);
        return $data;
    }

    /**
     * 分页列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists_have($page=1,$number=10,$where_base=[],$field='*'){        
        $startRecord=($page-1)*$number; 
        $where_other = ['ORDER'=>["id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
        if(is_array($where_base)){
        $where = array_merge($where_base,$where_other);
        }else{
        $where = $where_other;
        }
        
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        $data=$this->select($this->title,$field,$where_ar);

        return $data;
    }

    /**
     *  用openid查找用户
     *  @param  $openid 
     *  @return 数据集
     */
    public function find_by_openid($openid){
        $data=$this->get($this->title,'id',["AND"=>['openid'=>$openid]]);
        $data = empty($data) ? [] : $data;
        return $data;
    }


    /**
     * 验证管理员账号密码
     * @param  param1:账号,param2:密码
     * @return 存在返回存入redis的信息，不存在返回NULL
     */
    public function check_user($ad,$pw){
        $where["OR"]=[
        	'username'=>$ad,
          	'tel'=>$ad,
        ];
        $where['password'] = $pw;
        $data=$this->get($this->title,['is_supplier','tid','id','username','nickname','show','im','quhao'],$where);  
        return $data;        
    }

    /**
     * 验证管理员账号密码
     * @param  param1:账号,param2:密码
     * @return 存在返回存入redis的信息，不存在返回NULL
     */
    public function check_user_pay($ad,$pw){
        $where["OR"] =  [
            'username'=>$ad,
            'tel'=>$ad
        ];
        $where['pay_password'] = $pw;
        $data=$this->get($this->title,['tid','id','username','nickname','show','im','quhao'],$where);        
        return $data;        
    }

    /**
     * [find_by_username 返回指定账号的ID]
     * @param  [type] $username [账号]
     * @return [type]           [ID]
     */
    public function find_by_username($username){
        $data=$this->get($this->title,'id',["OR"=>['username'=>$username,'tel'=>$username]]);
        return $data;
    }

    public function find_by_share($share){
        $data=$this->get($this->title,'id',["AND"=>['username'=>$share]]);
        return $data;
    }

    public function find_by_tel($tel){
        $data=$this->get($this->title,'id',["AND"=>['tel'=>$tel]]);
        return $data;
    }


    /**
     * [check_by_username 查指定手机号和账号的ID]
     * @param  [type] $username [账号]
     * @param  [type] $tel      [手机号]
     * @return [type]           [ID]
     */
    public function check_by_username($username,$tel){
        $data=$this->get($this->title,'id',["AND"=>['username'=>$username,'tel'=>$tel]]);
        return $data;
    }


    /**
     * 修改密码
     * @param  [type] $username [description]
     * @param  [type] $password [description]
     * @return [type] bool      [description]
     */
    public function change_password($uid,$password){
        $data = $this->update($this->title,['password'=>$password],['id'=>$uid]);                    
        return $this->doo();
    }

    /**
     * 修改支付密码
     * @param  [type] $username [description]
     * @param  [type] $password [description]
     * @return [type] bool      [description]
     */
    public function change_pay_password($uid,$password){
        $data = $this->update($this->title,['pay_password'=>$password],['id'=>$uid]);                    
        return $this->doo();
    }

    /**
     * 所有下级会员ID 直推
     * @return [array]下级会员ID[description]
     */
    public function find_son($uid){
        $data = $this->select($this->title,['id','username','nickname','avatar','tel','rating','im','sex','created_time'],['tid'=>$uid,'ORDER'=>["created_time"=>"DESC",'id'=>"DESC"]]);
        return $data;
    }


    /**
     * 资金变化 可以是money/amount/interal
     * @return boolrean
     */
    public function upmoney($uid,$cate,$money){
        if($money<0){
            return false;
        }
        $this->update($this->title,[$cate=>$money],['id'=>$uid]);      
        return $this->doo();
    }

    /**
     * 当前资金余额
     * @return [string]余额
     */
    public function find_money($uid,$cate){
        $data = $this->get($this->title,[$cate],['id'=>$uid]);
        $data = $data[$cate];
        return $data;
    }


    /**
     * 生成注册推广码唯一,直接升级成用户账号
     */
    public function get_sharecode($code=''){
        $code = $code ? $code : getRandChar2(6);
        $data = $this->get($this->title,['id'],['username'=>$code]);
        $res = $data ? true : false;
        if($res){
            $new_code = getRandChar2(6);
            return $this->get_sharecode($new_code);
        }else{
            return $code;
        }
    }


    /*EXCEL导入会员表的所有期数*/
    public function stage_all(){
        $sql = "select stage from user group by stage order by id desc";
        $rs = self::$medoo->query($sql)->fetchAll();
        $ar = [];
        foreach($rs as $one){
            if($one['stage']){
                $ar[] = $one['stage'];
            }
        }    
        sort($ar);
        return $ar;
    }


    public function save_by_excel($data){
        $data['username'] = $this->get_sharecode();
        if(isset($data['tel'])){
            $is_have = $this->is_find(['tel'=>$data['tel']]); //导入电话重复不写入
            if($is_have || $data['tel']==''){
              return 0;
            }
        }

        if(isset($data['created_time'])){
            $t = $data['created_time'];      
            $data['created_time']  = intval(($t - 25569) * 3600 * 24) - 8*3600; //转换成1970年以来的秒数 要减8小时
        }

        $do = $this->insert($this->title,$data);          
        //$do = $do->rowCount();  
        $do = $this->id();
        if($do>0){
            $data_a['uid']= $do;
            $this->insert($this->title2,$data_a);       
        }
        if($do || $do===0){return $do;}else{return 0;}  
    }


	
}

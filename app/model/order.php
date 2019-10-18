<?php
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: DEMO模型
 */
namespace app\model;


class order extends BaseModel
{
    public $title = 'iorder';

 
    /**
     * 模型分类数据
     * @param  cate 分类字符
     * @return data 返回数据集
     */
    public function list_cate($category_id){      
        $data=$this->select($this->title,"*",["category_id"=>$category_id,'ORDER'=>["sort"=>"DESC"]]);        
        return $data;     
    }

    /**
     * 模型列表数据
     * @param page 页码
     * @return data 返回数据集
     */
    public function lists($page=1,$number=10,$where_base=[]){        
        $startRecord=($page-1)*$number;
        $where_other = ['ORDER'=>["Created_time"=>"DESC","id"=>"DESC"],"LIMIT" => [$startRecord,$number]];
        $where = array_merge($where_base,$where_other);
        $data_ar=$this->select($this->title,'id', $where);
        $where_ar['id']=$data_ar;
        if(isset($where['ORDER'])){
            $where_ar['ORDER']=$where['ORDER'];
        }
        $data=$this->select($this->title,'*',$where_ar);
        
        $user_M = new \app\model\user();
        $order_pro_M = new \app\model\order_product();
        $groups_M = new \app\model\groups();
        $card_M=new \app\model\card();
        foreach($data as $key=>$rs){
            //查商家
           if($rs['sid'] == 0 ){
                $data[$key]['sid_cn'] = c('head').'自营';
            }else{
                $data[$key]['sid_cn'] = user_info($rs['sid'],'shop_title'); 
            }
            $users=user_info($rs['uid']);
            $data[$key]['uid_cn'] = $users['username'];
            $data[$key]['uid_nick'] = $users['nickname'];

            //拼团
            if($rs['types']==4){
                $data[$key]['groups']=$groups_M->have(['oid'=>$rs['id']]);
            }

            //查推荐人账号  
            $tid = $user_M->find($rs['uid'],'tid');
            $data[$key]['tid'] = $tid;
            $data[$key]['tid_cn'] = user_info($tid,'username'); 
            //转sku中文为数组
            $product =  $order_pro_M->find_by_oid($rs['oid']);   
            foreach($product as &$vo){
                switch ($vo['status'])
                {
                case 0:
                    $vo['order_return']='';
                    break;  
                case 1:
                    $vo['order_return']='申请退货中';
                    break;
                case 2:
                    $vo['order_return']='等待买家发货';
                    break;
                case 3:
                    $vo['order_return']='待退款';
                    break;
                case 4:
                    $vo['order_return']='退货成功';
                    break;
                default:
                }

                if($rs['types']==2){
                    $vo['order_return']='';
                }
                if($rs['types']==4 && $rs['status']=='已支付' && $rs['groups']['status']==0){
                    $vo['order_return']='';
                }
                $vo['card']=$card_M->have(['oid'=>$rs['oid'],'pid'=>$vo['pid']],'key');
            }
            $data[$key]['product'] =  $product;
        }
        return $data;
    }


    /**
     * 查本月定单数
     */
    public function order_month($uid){
        $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
        $res=self::$medoo->query("select * from ".$this->title." where created_time > ".$beginThismonth." and uid=".$uid)->fetchAll();
        $num = count($res);    
        // $num = self::$medoo->query("select * from news")->fetchAll();  
        return $num;
    }


    /*根据时间区间查下单的用户*/
    public function find_time_oid($begin_time,$end_time){
        $where['created_time[<>]'] = [$begin_time,$end_time];
        $ar = $this->select($this->title,'uid',$where);
        return $ar;
    }


}

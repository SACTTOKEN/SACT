<?php
namespace app\ctrl\common;
use app\model\ad as ad_M;

class ad{

    public function lists_one()
    {
        $pid=post('pid',0);
        $data=(new ad_M())->lists_all(['parent_id'=>$pid],['yid(id)','title']);
        return $data;
    }
    public function index(){
        return false;
        $level = post('level',0);
        $page = post('page',1);
        $size = post('size',50);
        $host = "https://api02.aliyun.venuscn.com";
        $path = "/area/all";
        $method = "GET";
        $appcode = "c0e9faac3c20416fb2a130e730f77ce9";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "level=".$level."&page=".$page."&size=".$size;
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER,false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $back = curl_exec($curl);
        $back = json_decode($back,true);
        if($back['msg']!='success'){error($back['msg'],400);}
        $data = $back['data'];

        $ad_M = new \app\model\ad();
        foreach($data as $one){
            $vo['title'] = $one['name'];
            $vo['short_name'] = $one['short_name'];
            $vo['merger_name'] = $one['merger_name'];
            $vo['level'] = $one['level'];
            $vo['parent_id'] = 0;
            $vo['lng'] = $one['lng'];
            $vo['lat'] = $one['lat'];
            $vo['zip_code'] = $one['zip_code'];
            $vo['pinyin'] = $one['pinyin'];
            $vo['yid'] = $one['id'];
            $res = $ad_M ->save($vo);
        }

        return $data;       
    }

    //采集到数据库，然后导入成json
    public function lists(){
        return false; //全部重新采集时注释该句
        $ad_M = new \app\model\ad();
        $id = get('id');
        $one = $ad_M->find($id);
       
        $res = $this->do($one['yid']);
        $my_id = $id + 1;
        if($my_id>=5285){
            return 'OK';
        }else{
             echo "<script>location.href='http://www.api.com/common/ad/lists?id=".$my_id."'</script>";
             exit();
        }      
        return true;
    }

    public function do($parent_id){
        return false;
        $host = "https://api02.aliyun.venuscn.com";
        $path = "/area/query";
        $method = "GET";
        $appcode = "c0e9faac3c20416fb2a130e730f77ce9";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "parent_id=".$parent_id;
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $back = curl_exec($curl);
        $back = json_decode($back,true);
        if($back['msg']!='success'){error($back['msg'],400);}
        $data = $back['data'];


        $ad_M = new \app\model\ad();
        foreach($data as $one){
            $vo['title'] = $one['name'];
            $vo['short_name'] = $one['short_name'];
            $vo['merger_name'] = $one['merger_name'];
            $vo['level'] = $one['level'];
            $vo['parent_id'] = $parent_id;
            $vo['lng'] = $one['lng'];
            $vo['lat'] = $one['lat'];
            $vo['zip_code'] = $one['zip_code'];
            $vo['pinyin'] = $one['pinyin'];
            $vo['yid'] = $one['id'];
            $res = $ad_M ->save($vo);
        }
        return $res;
    }


//php.ini  max_execution_time  最大执行时间
//php.ini  memory_limit 内存限制
 
    public function tree($parent_id,$ar){
        $new_ar = [];
        foreach($ar as $key=>$one){
           
            if($one['parent_id']==$parent_id){
                //$one['yid'] = $one['yid'].$one['title'];
                //unset($one['title']);
                unset($one['id']);
                $new_ar[] = $one;      
            }
           
       }
       return $new_ar;
    }



    public function read(){
        //return false; 
        $filename = "ad.json";
        $filepath = IMOOC."/public/";
        $myfile = $filepath.$filename;
        $json = file_get_contents($myfile);
        $ar = json_decode($json,true);
        $old_ar = $ar['RECORDS'];


        //ob_end_clean();  
        //ob_implicit_flush(1);  //上句本意是ob_implicit_flush(true);
        //ob_flush();

        // foreach($old_ar as $key=>$one){
        //     var_dump($one);
        //     sleep(1);
        //     ob_flush();  
        //     flush();
        // }


        $filename2 = "address_new.json";
        $myfile2 = $filepath.$filename2;

        //step.1
        //$ar2 = $this->tree(0,$old_ar);
        

        //step.2
        // $json2 = file_get_contents($myfile2);
        // $ar2 = json_decode($json2,true);

        // foreach($ar2 as $key1=>$one){
        //     $find_ar = $this->tree($one['yid'],$old_ar);
        //     if(empty($find_ar)){$find_ar=[];}
        //     $ar2[$key1]['z'] = $find_ar;
        // }

        // var_dump($ar2[33]['z'][0]);  //ok 彰化县
        // exit();
        

        //step.3
        // $json2 = file_get_contents($myfile2);
        // $ar2 = json_decode($json2,true);

        // foreach($ar2 as &$one){
        //     foreach($one['z'] as &$vo){
        //         $find_ar = $this->tree($vo['yid'],$old_ar);
        //         if(empty($find_ar)){$find_ar=[];}
        //         $vo['z'] = $find_ar;
        //     }    
        // } 

        // var_dump($ar2[33]['z'][0]);  //OK 芳苑乡，芬园乡，福兴乡。。。。
        // exit();
        


        //step.4
        $json2 = file_get_contents($myfile2);
        $ar2 = json_decode($json2,true);

        foreach($ar2 as &$one){
            foreach($one['z'] as &$vo){
                foreach($vo['z'] as &$vv){
                    $find_ar = $this->tree($vv['yid'],$old_ar);
                    if(empty($find_ar)){$find_ar=[];}
                    $vv['z'] = $find_ar;
                }
            }    
        }

        // var_dump($ar2[33]['z'][1]['z'][0]);  //OK 芳苑乡 (雅乐巷,荣顺路)
        // exit();

      
        $new_ar = json_encode($ar2,JSON_UNESCAPED_UNICODE);
        $my = fopen($myfile2, 'w'); 
        fwrite($my, $new_ar);     
        fclose($my); 

        return true;
    }


    public function ad_vue(){
        $filename = "address_vue.json";
        $filepath = IMOOC."/public/resource/json/";
        $myfile = $filepath.$filename;
        $json = file_get_contents($myfile);
        return $json;
    }



    //传区ID 返回街道
    public function ad(){   
        $id = post('id');   
        $ad_M = new \app\model\ad();
        $where['parent_id'] = $id;
        $ar = $ad_M->lists_all($where);
        return $ar;
    }




}
<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-21 14:43:50
 * Desc: 导入导出excel类
 */
namespace core\lib;
use OSS\OssClient;
use OSS\Core\OssException;
class oss{
    
    public function upload_pic($tmp_name,$url)
    {
        require_once(IMOOC."/extend/aliyun_oss/autoload.php");
        $account=cc('account','oss');
        $accessKeyId=$account['OSS_ACCESS_ID'];
        $accessKeySecret=$account['OSS_ACCESS_KEY'];
        $endpoint='https://'.$account['OSS_ENDPOINT'];
        $bucket=$account['OSS_TEST_BUCKET'];
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret,$endpoint);
            $data=$ossClient->uploadFile($bucket,$url,$tmp_name);
            $ar['result']=1;
            $ar['url']=$data['info']['url'];
        } catch (OssException $e) {
            $ar['result']=0;
            $ar['info']=$e->getMessage();
        }
        return $ar;

    }
    
}
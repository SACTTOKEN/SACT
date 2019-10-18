<?php
/**
 * Created by yaaaaa__god 
 * User: GOD
 * Date: 2018-12-20 11:05:39
 * Desc: 内容生成静态页
 */
namespace app\service;

class create_html{

    private $info_htmlpath = 'resource/content';

    public function save_content($content)
    {
        $filename = "/".date('Ym',TIMESTAMP)."/".TIMESTAMP.".html" ;
        $filepath = IMOOC.$this->info_htmlpath.dirname($filename)."/";
        
        if(!file_exists($filepath))
        {
            if(!mkdir($filepath,0755,true))
            {
                return msg("创建目录失败");
            }
        }
        
        $myfile = fopen(IMOOC.$this->info_htmlpath.$filename, 'w'); 

        $content = str_replace('@link=@','src=',$content); //参见：core/lib/request.php post()方法
        //$content = str_replace('http://'.$web_url, '@web_url@', $content); //便于更换域名时，前端修改替换@web_url@

        fwrite($myfile, $content);     
        fclose($myfile);    
        return $this->info_htmlpath.$filename;       
    }


    public function get_content($filepath)
    { 
        $filepath = IMOOC.$filepath;
        if(!file_exists($filepath))
        {
            return '';
        }
        $content = file_get_contents($filepath);
        return $content;
    }



    public function edit_content($filepath,$content='')
    {
        $filepath = IMOOC.$filepath;
        if(!file_exists($filepath))
        {
            return false;
        }

        $content = str_replace('@link=@','src=',$content);
        $myfile = fopen($filepath, 'w');  
        $res = fwrite($myfile, $content); //返回写入的字符，失败false
        fclose($myfile);
        if($content==''){return true;}
        $res = $res ? true : false;
        return $res;      
    }


    public function del_content($filepath)
    {
        $filepath = IMOOC.$filepath;
        if(file_exists($filepath)){
            return unlink($filepath);
        }
        return true;
    }
    
}
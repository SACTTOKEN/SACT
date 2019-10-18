<?PHP
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 文件上传
 */
namespace core\lib;
use OSS\OssClient;
use OSS\Core\OssException;
class files{

    private $file_name   = '';    //文件原名称
    private $file_size   = 0;     //文件大小
    private $error     = 0;     //错误代号
    private $file_tmp_name = '';    //文件临时名称
    private $allow_type  = array('image/pjpeg', 'image/jpg', 'image/jpeg', 'image/png', 'image/gif','application/vnd.ms-excel','application/x-excel','video/mpeg','video/x-msvideo','application/octet-stream','video/mp4','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');  //允许上传的文件扩展名
    private $msg      = '';    //信息
    private $max_size   = '20000000'; //设置上传文件的大小，此为2M
    private $file_ext   = '';    //上传文件的扩展名
    private $ext   = '';    //上传文件后缀名
    private $save_path   = 'resource/image';    //文件保存路径
    private $uploaded   = '';    //路径.文件名
    private $folder   = '';    //自定义文件夹名
    private $ename = array();

  
    /**
     * 上传文件
     */
    public function upload_file($file,$folder='default'){

      $this->file_name   = $file['name'];
      $this->file_size   = $file['size'];
      $this->error     = $file['error'];
      $this->file_tmp_name = $file['tmp_name'];
      $this->ext       = $this->get_file_type($this->file_name);

      $this->ename=getimagesize($this->file_tmp_name); 
      $this->file_ext       = $this->ename['mime'];


      if($folder==''){
        $this->folder       = 'default';
      }else{
        $this->folder       = $folder;
      }
     



      
      if($this->ext=='xls' || $this->ext =='xlsx'){
        $this->save_path="resource/excel";
      }


      switch($this->error){
        case 0: $this->msg = ''; break;
        case 1: $this->msg = '超出了php.ini中文件大小'; break;
        case 2: $this->msg = '超出了MAX_FILE_SIZE的文件大小'; break;
        case 3: $this->msg = '文件被部分上传'; break;
        case 4: $this->msg = '没有文件上传'; break;
        case 5: $this->msg = '文件大小为0'; break;
        default: $this->msg = '上传失败'; break;
      }
      if($this->msg){
        return false;
      }

      if($this->error==0 && is_uploaded_file($this->file_tmp_name)){
        //检测文件类型
        if(!empty($this->file_ext)){
        if(in_array($this->file_ext, $this->allow_type)==false){
          $this->msg = '文件类型不正确';
          return false;
        }
        }
        //检测文件大小
        if($this->file_size > $this->max_size){
          $this->msg = '文件过大';
          return false;
        }
      }

      $this->set_file_name(); 
      if($this->ext =='mp4' || $this->ext=='avi' || $this->ext=='flv' || $this->ext=='xlsx' || $this->ext=='xls'){
        if($this->save_file()){
          $this->msg = $this->uploaded;
          return true;
        }else{
          $this->msg = '文件上传失败!';
          return false;
        }
      }else{
        if($this->create_simg(750,750)){
          if($this->save_file()){
            $this->msg = $this->uploaded;
            return true;
          }else{
            $this->msg = '文件上传失败';
            return false;
          }
        }else{
          $this->msg = '图片打不开';
          return false;
        }
      }
    }

    public function save_file()
    {
      $oss=plugin_is_open('oss');
      if($oss && c("kqoss")==1 && $this->ext!='xlsx' && $this->ext!='xls'){
        $this->uploaded=c("OSS_wzwjj").'/'.$this->uploaded;
        $oss_ar=(new oss())->upload_pic($this->file_tmp_name, $this->uploaded);

        if($oss_ar['result']==1){
            $this->uploaded=$oss_ar['url'];
            return true;
        }else{
            $this->msg = $oss_ar['info'];
            return false;
        }
      }else{
          move_uploaded_file($this->file_tmp_name, $this->uploaded);
          $this->uploaded = "/api/".$this->uploaded;
          return true;
      }
    }

    
    
    /**
    * 获取上传文件类型
    * @param string $filename 目标文件
    * @return string $ext 文件类型
    */
    public function get_file_type($filename){
      $ext = pathinfo($filename, PATHINFO_EXTENSION);
      return $ext;
    }
    /**
    * 设置上传后的文件名
    * 当前的毫秒数和原扩展名为新文件名
    */
    public function set_file_name(){
        if($this->folder=="imteam" && c("kqoss")==1){
        $t =$this->folder.'/';  
        $this->uploaded = $t.getRandChar2(16).'.'.($this->ext);
        }else{
        $t =$this->save_path.'/'.$this->folder.'/'.date('Ym',time()).'/';      
        $this->mkFolder($t);
        $this->uploaded = $t.md5($this->file_tmp_name).'.'.($this->ext);
        }
        //$this->uploaded = $t.date('dhis',time()).($a[0]*1000000).'.'.($this->ext);
    }
     
    public function mkFolder($path)
    {
      if(!file_exists($path)){ //file_exists检测目录或文件是否存在
        mkdir($path,0777,true);
        }
    }
     
    //获取错误信息
    public function get_msg(){
      return $this->msg;
    }
    public function get_size(){
      return $this->file_size;
    }
    public function get_oldname(){
      return $this->file_name;
    }


    //生成缩略图
    function create_simg($img_w,$img_h){
      return true;
      if($this->file_ext == "image/pjpeg" || $this->file_ext == "image/jpeg" || $this->file_ext == "image/jpg"){
            $im = imagecreatefromjpeg($this->file_tmp_name);   
      }else if($this->file_ext == "image/png"){  
            $im = imagecreatefrompng($this->file_tmp_name);  
      }else if($this->file_ext == "image/gif"){  
            $im = imagecreatefromgif($this->file_tmp_name);  
      }else if($this->file_ext == 'application/vnd.ms-excel'){
            return true;
      }else{
            return false;
      }
  
      $src_w=imagesx($im);//获得图像宽度
      $src_h=imagesy($im);//获得图像高度
      if($src_w<=0 || $src_h<=0){
        return false;
      }


      $new_wh=($img_w/$img_h);//新图像宽与高的比值
      $src_wh=($src_w/$src_h);//原图像宽与高的比值
      if($new_wh<=$src_wh){
        $f_w=$img_w;
        $f_h=$f_w*($src_h/$src_w);
      }else{
        $f_h=$img_h;
        $f_w=$f_h*($src_w/$src_h);
      }

      if($src_w>$img_w||$src_h>$img_h){      
        if(function_exists("imagecreatetruecolor")){//检查函数是否已定义
          $new_img=imagecreatetruecolor($f_w,$f_h); //imagecreatetruecolor有个默认的黑色背景
          if($new_img){
          imagecopyresampled($new_img,$im,0,0,0,0,$f_w,$f_h,$src_w,$src_h);//重采样拷贝部分图像并调整大小
          }else{
          $new_img=imagecreate($f_w,$f_h);
          imagecopyresized($new_img,$im,0,0,0,0,$f_w,$f_h,$src_w,$src_h);
          }
        }else{
          $new_img=imagecreate($f_w,$f_h);
          imagecopyresized($new_img,$im,0,0,0,0,$f_w,$f_h,$src_w,$src_h);
        }
        
        if(function_exists('imagejpeg')){
          imagejpeg($new_img,$this->file_tmp_name);
        }else{
          imagepng($new_img,$this->file_tmp_name);
        }
        imagedestroy($new_img);
      }
      return true;
    }    
    
}
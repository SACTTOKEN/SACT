<?php 
/**
 * Created by yaaaaa_god
 * User: yaaaaa
 * Date: 2018/12/13
 * Desc: 获取类
 */
namespace core\lib;
use core\lib\Exception;

class Request
{
	
    public $post = array();
    public $get = array();
    public $cookie = array();
    public $server = array();
    protected static $instance;
    
	
	/**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return \think\Request
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }
	
	protected function daddslashes($string) {

        if(is_array($string)) {
            foreach($string as $key => $val) {
                $string[$key] = $this->daddslashes($val);
            }
        } else {
            !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
		    if(!MAGIC_QUOTES_GPC) {
                $string = addslashes($string);
            }
        }

        $strings="";
        if(is_array($string)){
            foreach($string as $key => $val) {
                $strings.=$val.',';
            }
            $string=trim($strings,',');
        }
		$string=htmlspecialchars(strip_tags(trim($string)));
		$string = str_replace("_","\_",$string);
		$string = str_replace("%","\%",$string);
		$string = str_replace ( array ('"', "\\", "'", "/", "..", "../", "./", "//" ), '', $string );
        $no = '/%0[0-8bcef]/'; 
        $string = preg_replace ( $no, '', $string );
        $no = '/%1[0-9a-f]/';
        $string = preg_replace ( $no, '', $string );
        $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
        $string = preg_replace ( $no, '', $string );

        
        $string2=strtolower($string);
        $qian=array(" ","　","\t","\n","\r");
        $string2=str_replace($qian,'',$string2); 
 
        $ar = preg_match('/replace|iframe|src=|select|insert|update|delete|into|load_file|outfile|script|xp_cmdshell|database|DECLARE|CAST|VARCHAR|EXEC|&#/i',$string2,$matches);
        $mat = implode('@',$matches);

		if($ar){
			throw new Exception([
				'msg' => '包含非法参数'.$mat,
				'code' => 400
			]);
        }
		return $string;
	}
	
	
	/**
     * 设置获取获取GET参数
     */
    public function get($name='')
    {
        if (empty($this->get)) {
            $this->get = $_GET;
            $this->daddslashes($this->get);
        }
		if($name==""){
			return $this->get;
		}else{
			return isset($this->get[$name]) ? $this->get[$name] : '';
		}
    }

    /**
     * 设置获取获取POST参数
     */
    public function post($name='')
    {
        if (empty($this->post)) {
            $content = file_get_contents('php://input');
            if (empty($_POST) && false !== strpos($this->contentType(), 'application/json')) {
                $this->post = (array) json_decode($content, true);
            } else {
                $this->post = $_POST;
            }
            if(isset($this->post['content'])){
                $this->post['content']  =  str_replace('src=','@link=@',$this->post['content']);
            }
			$this->daddslashes($this->post);
        }
		
        $data = [];
		if($name==""){
			return $this->post;
		}elseif (is_array($name)) {
			foreach($name as $key=>$vo){
                if(isset($this->post[$vo])){
                    if(is_array($this->post[$vo])){
                        $data[$vo]=$this->post[$vo];
                    }else{
                        $data[$vo]=trim($this->post[$vo]);
                    }
                }				
			}
			return $data;
        }else{
			return isset($this->post[$name]) ? trim($this->post[$name]) : '';
		}
    }
	

    public function cookie($name='')
    {
        if (empty($this->cookie)) {
            $this->cookie = $_COOKIE;
            $this->daddslashes($this->cookie);
        }
		if($name==""){
			return $this->cookie;
		}else{
            if(!empty($this->cookie[$name])){
                return $this->cookie[$name];
            }
			
		}
    }
	
	public function server($name = '')
    {
        if (empty($this->server)) {
            $this->server = $_SERVER;
			$path=$this->server['REQUEST_URI'];
			if(isset($path) && $path!='/'){
				$patharr=explode('/',trim($path,'/'));
				if(count($patharr)>0){
					if(isset($patharr[0])){
						$this->server['app']=$patharr[0];
					}
					if(isset($patharr[1])){
						$this->server['ctrl']=$patharr[1];
					}else{
						$this->server['ctrl']='index';
					}
					if(isset($patharr[2])){
						$this->server['action']=$patharr[2];
					}else{
						$this->server['action']='index';
					}
				}else{
					$this->server['app']='mobile';
					$this->server['ctrl']='index';
					$this->server['action']='index';
				}
			}else{
				$this->server['app']='mobile';
				$this->server['ctrl']='index';
				$this->server['action']='index';
			}
			
        }
		if($name==""){
			return $this->server;
		}else{
			return isset($this->server[$name]) ? $this->server[$name] : '';
		}
    }
	
	
	


    /**
     * 设置或者获取当前的Header
     * @access public
     * @param string|array  $name header名称
     * @param string        $default 默认值
     * @return string
     */
    public function header($name = '', $default = null)
    {
        if (empty($this->header)) {
            $header = [];
            if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
                $header = $result;
            } else {
                $server = $this->server ?: $_SERVER;
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                        $header[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $header['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $header['content-length'] = $server['CONTENT_LENGTH'];
                }
            }
            $this->header = array_change_key_case($header);
            $this->daddslashes($this->header);
        }
        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }
        if ('' === $name) {
            return $this->header;
        }
        $name = str_replace('_', '-', strtolower($name));
        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }


    
	 /**
     * 当前请求 HTTP_CONTENT_TYPE
     * @access public
     * @return string
     */
    public function contentType()
    {
        $contentType = $this->server('CONTENT_TYPE');

        if ($contentType) {
            if (strpos($contentType, ';')) {
                list($type) = explode(';', $contentType);
            } else {
                $type = $contentType;
            }
            return trim($type);
        }
        return '';
    }
    
}

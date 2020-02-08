<?php
/**
 * 文件上传类 基于laravel上传类库
 * 使用方法  $upload = Upload::getInstance();  注意命名空间
 * 设置文件大小： $upload->size = 5; 限制文件大小 5M
 * 设置文件后缀： $upload->ext = 'png,bmp,jpeg' || ['png', 'bmp', 'jpeg']; 支持数组或字符串
 * 设置文件上传目录： $upload->dir = ‘custom’  /uploads/custom/20151223.a.png
 * 快速上传图片： $upload->image();  return true || false
 * 快速上传excel表格： $upload->excel();  return true || false
 *
 * 获取上传文件地址： $upload->getFile();
 *
 * 获取错误信息： $upload->getError();
 *
 * @todo 多图上传
 * */
namespace Master\Component;

class Uploads{   
    /*文件上传路径*/
    public $upload_path = '';

    /*文件返回地址*/
    public $upload_url = '';
    
    /*支持的文件后缀*/
    public $ext  = '';
    
    /*文件限制大小*/
    public $size = 0;
    
    /*文件名称加密key*/
    public $key = 'NuoCoo';
    
    /*自定义上传目录*/
    public $dir = '';

    public $file_rule = '';

    /*文件对象*/
    protected $_files;
    
    /*文件后缀*/
    protected $_files_ext = '';
    
    /*文件大小*/
    protected $_files_size = 0;
    
    /*文件名称*/
    protected $_files_name = '';
    
    /*文件原始名称*/
    protected $_files_base_name = '';
    
    /*是否压缩图片*/
    protected $_compress = false;

    /*错误信息*/
    protected $_error = ['code'=>0, 'msg'=>''];

    /*静态资源*/
    private static $instance = null;

    private function __construct(){

    }

    public static function getInstance(){
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Uploads';
    }

    /**
     * 图片快速上传
     * @param string  $key 文件对象key $_FILES[$key]
     * @return bool
     */
    public function image($key = 'file'){
        $this->ext = ['png', 'bmp', 'jpeg', 'jpg', 'gif'];
        /*文件对象*/
        $this->_files = isset($_FILES[$key]) ? $_FILES[$key] : [];
        if(empty($this->_files)){
            $this->setError(101, '获取文件错误');
            return false;
        }

        /*执行文件上传*/
        $res = $this->init();

        /*执行压缩图片*/
        if($this->_compress){
            $this->compressedImage();
        }
        return $res;
    }

    /**
     * Excel快速上传
     * @param string  $key 文件对象key $_FILES[$key]
     * @return bool
     */
    public function excel($key = 'file'){
        $this->ext = ['xls','xlsx','xlsm','xlt','xltx','xltm','csv'];
        /*文件对象*/
        $this->_files = isset($_FILES[$key]) ? $_FILES[$key] : [];
        if(empty($this->_files)){
            $this->setError(101, '获取文件错误');
            return false;
        }
        /*执行文件上传*/
        return $this->init();
    }

    /**
     * 简单上传
     * @param string  $key 文件对象key $_FILES[$key]
     * @return bool
     */
    public function simple($key = 'file'){
        /*文件对象*/
        $this->_files = isset($_FILES[$key]) ? $_FILES[$key] : [];
        if(empty($this->_files)){
            $this->setError(101, '获取文件错误');
            return false;
        }
        /*执行文件上传*/
        return $this->init();
    }

    /**
     * 获取上传后的文件地址
     *
     * @return string
     */
    public function getFile(){
        if(empty($this->_files_name)){
            return '';
        }
        return str_replace('\\', '/', $this->upload_url.$this->_files_name);
    }

    /**
     * 获取上传后的文件参数
     *
     * @return array
     */
    public function getFileParams(){
        if(empty($this->_files_name)){
            return [];
        }
        $response = [
            'filename'      => $this->_files_base_name,
            'url'           => '',
            'size'          => $this->size,
            'size_format'   => round(($this->_files_size / 1024), 2) .'KB',
            'ext'           => $this->_files_ext
        ];
        $response['url'] = str_replace('\\', '/', $this->upload_url.$this->_files_name);
        return $response;
    }

    /**
     * 文件上传
     *
     * @return bool
     */
    protected function init(){
        
        /*获取文件对象后缀名*/
        $this->_files_ext = $this->_files['type'];
        /*获取文件大小*/
        $this->_files_size = $this->_files['size'];
        /*获取文件原始名称*/
        $this->_files_base_name = $this->_files['name'];

        $this->_files_name = $this->_files['name'];
        /*移动文件*/
        @move_uploaded_file($_FILES["file"]["tmp_name"], UPLOAD_PATH."/".time().'.jpg');
        return true;
        /*检查文件后缀是否合法*/
        // if(!$this->checkExt()){
        //     return false;
        // }

        /*检查文件大小是否合法*/
        // if(!$this->checkSize()){
        //     return false;
        // }

        /*获取上传文件名称*/
      //  $this->_files_name = $this->createFileName();

        /*创建文件上传路径*/
      //  $this->createFilePath();
    }
       

    /**
     * 检查文件后缀是否合法
     *
     * @return bool
     */
    private function checkExt(){
        /*没有文件后缀*/
        if(empty($this->_files_ext)){
            $this->setError(101, '文件格式错误');
            return false;
        }
        /*文件后缀如果是字符串， 切割成数组*/
        if($this->ext && !is_array($this->ext)){
            $this->ext = explode(',', $this->ext);
        }

        /*没有设置文件后缀 不验证*/
        if(empty($this->ext)){
            return true;
        }

        /*验证文件后缀是否存在与后缀集合*/
        if(!in_array(strtolower($this->_files_ext), $this->ext)){
            $this->setError(102, '不支持的文件格式');
            return false;
        }
        return true;
    }

    /**
     * 检查文件大小是否合法
     *
     * @return bool
     */
    private function checkSize(){
        if($this->_files_size === null){
            $this->setError(101, '文件大小错误');
            return false;
        }
        if(empty($this->size)){
            return true;
        }
        $size = 1024 * 1024 * $this->size;
        if($this->_files_size > $size){
            $this->setError(102, '文件大小超过限制');
            return false;
        }
        return true;
    }

    /**
     * 创建文件名称
     *
     * @return string
     */
    private function createFileName(){
        if($this->file_rule == 'uuid'){
            $this->_files_name = Utils::createUuid().'.'.$this->_files_ext;
            return $this->_files_name;
        } else {
            $this->_files_name = Utils::createUnique($this->key.SYS_TIME).'.'.$this->_files_ext;
            return $this->_files_name;
        }

    }

    /**
     * 创建文件上传路径
     *
     * @return string
     */
    private function createFilePath(){
        $dir = '';
        if($this->dir){
            $dir = $this->dir .'/';
        }
        if($this->upload_path){
            if(!is_dir($this->upload_path)){
                mkdir($this->upload_path, 0777, true);
            }
            return $this->upload_path;
        } else {
            $this->upload_path = ROOT_PATH.'uploads/'.$dir.date('Ymd').'/';
            if(!is_dir($this->upload_path)){
                mkdir($this->upload_path, 0777, true);
            }
            $this->upload_url = '/uploads/'.$dir.date('Ymd').'/';
            return $this->upload_path;
        }

    }

    /**
     * 设置错误信息
     *
     * @param int  $code 错误标识code
     * @param string $msg 错误信息
     * @return string
     */
    private function setError($code = 0, $msg = ''){
        $this->_error = ['code'=>$code, 'msg'=>$msg];
    }

    /**
     * 获取错误数据  默认返回 Errors 数组
     *
     * @param string  $key 错误标识key
     * @return array | string
     */
    public function getError($key = ''){
        if(empty($key)){
            return $this->_error;
        }
        return isset($this->_error[$key])?$this->_error[$key]:'';
    }

    /**
     * 压缩图片
     *
     * @return array | string
     */
    public function compressedImage() {
        //ROOT_PATH
        /*创建压缩图片名称*/
        $file_name = $this->getFile();
        if(empty($file_name)){
            return false;
        }
        $imgsrc = ROOT_PATH.$file_name;
        list($width, $height, $type) = getimagesize($imgsrc);
        if($type == 1){
            return true;
        }
        $compressed_file_name = $this->upload_path.$this->createFileName();
        $imgdst = $compressed_file_name;
        $new_width = $width;//压缩后的图片宽
        $new_height = $height;//压缩后的图片高
        if($width >= 1000){
            $per = 1000 / $width;//计算比例
            $new_width = $width * $per;
            $new_height = $height * $per;
        }
        switch ($type) {
            case 1:
                return true;
                break;
            case 2:
                header('Content-Type:image/jpeg');
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromjpeg($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //90代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 90);
                @imagedestroy($image_wp);
                $res = @imagedestroy($image);
                if($res === true){
                    @unlink($imgsrc);
                } else {
                    $this->_files_name = $file_name;
                }
                break;
            case 3:
                header('Content-Type:image/png');
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefrompng($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //90代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 90);
                @imagedestroy($image_wp);
                $res = @imagedestroy($image);
                if($res === true){
                    @unlink($imgsrc);
                }else {
                    $this->_files_name = $file_name;
                }
                break;
        }
    }


    /**
     * 关闭句柄
     *
     * @return void
     */
    private function __clone(){

    }
}

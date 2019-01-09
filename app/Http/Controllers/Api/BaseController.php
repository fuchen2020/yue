<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/21
 * Time: 22:42
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Api\Config;
use App\Models\Api\UserMsg;
use App\Models\Api\UserVip;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Storage;
use Overtrue\EasySms\EasySms;

class BaseController extends Controller
{

    public static $re_msg = [
        '201' =>'图片中不包含人脸',
        '202' =>'图片中包含人脸',
        '203' =>'图片中包含多个人脸',
        '204' =>'图片中包含明星脸',
        '205' =>'片中包含政治人物人脸',
        '206' =>'片中包含公众人物人脸',
        '207' =>'自定义人脸库识别未通过',
        '301'=>'图片中包含色情内容',
        '302'=>	'图片中包含性感内容，如穿着比较暴露',
        '401'=>	'图片中包含血腥暴力场景内容',
        '501'=>	'图像美观度低于阀值',
        '502'=>	'图像美观度高于阀值',
        '503'=>	'图像美观度不等于阀值',
    ];

    /**
     * 生成随机验证码
     * @param int $len
     * @return string
     */
    public static function _get_rand_str($len = 4)
    {
        $chars = array(
            1, 2, 3, 4, 5, 6, 7, 8, 9, 0,
        );
        $charsLen = count($chars) - 1;
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */
    public static function set_curl($url, $params = false, $ispost = 0, $https = 0,$headers = '')
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($headers){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }


    /**
     * 取中间文本
     * @param $str 预取全文本
     * @param $leftStr 左边文本
     * @param $rightStr 右边文本
     * @return bool|string
     */
    function getSubStr($str, $leftStr, $rightStr)
    {
        $left = strpos($str, $leftStr);
        $right = strpos($str, $rightStr,$left);
        if($left < 0 or $right < $left) return '';
        return substr($str, $left + strlen($leftStr), $right-$left-strlen($leftStr));
    }

    /**
     * 百度接口请求
     * @param string $url
     * @param string $param
     * @return bool|mixed
     */
   public static function  request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $postUrl);
        // 要求结果为字符串
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // post方式
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($curl);
        var_dump(curl_error($curl));
        curl_close($curl);

        return $data;
    }

    /**
     * 获取百度api token
     * @return array|bool
     */
    public static function _getToken(){
        $key = 'BaiDu_Api_Token';
        $data=\Cache::get($key);
        if ($data) {
            return $data;
        }

        $url = 'https://aip.baidubce.com/oauth/2.0/token';
        $post_data['grant_type']       = 'client_credentials';
        $post_data['client_id']      = 'DqzU848GWcuYkcwTnyGrcr4q';
        $post_data['client_secret'] = 'SMYqCqfOlhZkFVp1gecZrZOwwDGBW7wP';
        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);

        $res =self::request_post($url, $post_data);

        $res = json_decode($res,true);

        if ($res) {

            $expiredAt = now()->addDays(29);
            // 缓存验证码 10分钟过期。
            \Cache::put($key, $res, $expiredAt);
            return $res;
        }else{
            return false;
        }

    }

    /**
     * 验证用户头像合法性
     */
    public static function _checkHead($img){

        $token=self::_getToken();

        if($token){
            $url = 'https://aip.baidubce.com/rest/2.0/solution/v1/face_audit?access_token=' . $token['access_token'];
            $body = array(
                "images" => $img
            );
            $res = self::request_post($url, $body);

            $res = json_decode($res,true);

            foreach ($res['result'] as $re ){

                if($re['res_code']==0 || $re['res_msg'][0]==206){
                    return [
                        'status' => true,
                        'msg' => '图像审核通过！'
                    ];
                }else{

                    return [
                        'status' => false,
                        'msg' => self::$re_msg[$re['res_msg'][0]],
                    ];
                }


            }

        }else{

            return [
                'status' =>false,
                'msg' => '图像审核失败！'
            ];
        }



    }


    /**
     * 图片转base64
     * @param ImageFile String 图片路径
     * @return 转为base64的图片
     */
    public static function _Base64EncodeImage($ImageFile) {
        if(file_exists($ImageFile) || is_file($ImageFile)){


            $path = \Storage::putFile('avatars', $ImageFile);

            $ImageFile=storage_path().'/app/'.$path;
            $base64_image = '';
            $image_info = getimagesize($ImageFile);

            $image_data = fread(fopen($ImageFile, 'r'), filesize($ImageFile));

            $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));

            \Storage::delete($path);

            return $base64_image;
        }
        else{
            return false;
        }
    }

    /**
     * base64 转图片 并保存
     * @param $base64_image_content
     * @param $path
     * @return bool|string
     */
    public static function _base64_image_content($base64_image_content,$path){
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];
            $new_file = '/upload/images/'.$path.'/'.date('Ymd',time())."/";
            if(!file_exists($new_file)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0777);
            }
            $new_file = $new_file.md5(time()+rand(1111,6666)).".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                return '/'.$new_file;
            }else{
                return false;
            }
        }else{
            return false;
        }



    }

    /**

     * base64转码图片

     * @param $base64

     * @param string $path

     * @return bool|string

     */

    public static function _get_base64_img($base64,$path = 'qt'){

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){

            $type = $result[2];

            $new_file = '/upload/images/'.$path.'/'.date('Ymd',time())."/".md5(time()+rand(1111,6666)).".{$type}";

            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64)))){

                return $new_file;

            }else{

                return  false;

            }

        }else{
            return false;
        }

    }


    /**
     * 微信小程序数据解密
     * @param $encryptedData
     * @param $sessionKey
     * @param $appid
     * @param $iv
     * @return int
     */

    public function encrypted($encryptedData,$sessionKey,$appid,$iv){

        require app_path('/Lib/Jmwx/wxBizDataCrypt.php');

        $pc = new \WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if ($errCode == 0) {
            return json_decode($data,true);
        } else {
            return $errCode;
        }

    }

    /**
     * 获取配置参数
     * @param $name
     * @return mixed
     */
    public static function _getConfig($name){

         $value = Config::where('name',$name)->value('value');

         return $value;
    }

    /**
     * 身份验证
     * @param $img
     * @param $num
     * @param $name
     * @return array
     */
    public static function _checkIdCard($img,$num,$name){

        $token=self::_getToken();

        if($token){
            $url = 'https://aip.baidubce.com/rest/2.0/face/v3/person/verify?access_token=' . $token['access_token'];
            $body = array(
                "image" => $img,
                'image_type' => 'URL',
                'id_card_number' => $num,
                'name' => $name,
                'quality_control' => 'LOW',
            );
            $res = self::request_post($url, $body);

            $res = json_decode($res,true);

            if($res['error_msg'] === 'SUCCESS'){

                if($res['result']['score'] >= 80){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }

        }else{

            return false;
        }
    }


    /**
     * 图片上传
     * @param $file 文件对象
     * @param string $path  专用文件夹
     * @return array
     */
    public function uploadImg($file,$path='qt'){
        $filePath =[];  // 定义空数组用来存放图片路径
        foreach ($file as $key => $value) {
            // 判断图片上传中是否出错
            if (!$value->isValid()) {
                exit("上传图片出错，请重试！");
            }
            if(!empty($value)){//此处防止没有多文件上传的情况
                $allowed_extensions = ["png", "jpg", "gif"];
                if ($value->getClientOriginalExtension() && !in_array($value->getClientOriginalExtension(), $allowed_extensions)) {
                    exit('您只能上传PNG、JPG或GIF格式的图片！');
                }
                $paths='/images/'.$path.'/'.date('Y-m-d');

                //检测文件是否存在,不存在则创建
                if (!\Storage::exists($paths)) {
                    \Storage::makeDirectory($paths);
                }

                $destinationPath = '/uploads'.$paths; // 文件保存路径
                $extension = $value->getClientOriginalExtension();   // 上传文件后缀
                $fileName = date('YmdHis').mt_rand(100,999).'.'.$extension; // 重命名
                $value->move(public_path().$destinationPath, $fileName); // 保存图片
                $filePath[] = '/uploads'.$paths.'/'.$fileName;
            }
        }
        return $filePath;
    }

    /**
     * 图片上传OSS
     * @param $file
     * @param string $path
     * @return bool|string
     */
    public function uploadOss($file,$path='mini'){
        try{

            $filePath =[];  // 定义空数组用来存放图片路径
            foreach ($file as $key => $value) {
                // 判断图片上传中是否出错
                if (!$value->isValid()) {
                    exit("上传图片出错，请重试！");
                }
                if(!empty($value)){//此处防止没有多文件上传的情况
                    $allowed_extensions = ["png", "jpg", "gif","jpeg"];
                    if ($value->getClientOriginalExtension() && !in_array($value->getClientOriginalExtension(), $allowed_extensions)) {
                        exit('您只能上传PNG、JPG或GIF格式的图片！');
                    }
                    $paths=$path.'/'.date('Y-m-d');// 文件保存路径

                    $disk = \Storage::disk('oss');//引入storage类和oss文件驱动

                     $re = $disk->put($paths,$value);

                    if ($re) {

                        $fileUrl = $disk->getUrl($re);
                    }else{

                        $fileUrl = false;
                    }
                }else{
                    $fileUrl = false;
                }
            }

            return $fileUrl;

        }catch (\Exception $exception){

            return false;
        }

    }


    /**
     * 发送推送消息
     * @param $user_id
     * @param $to_user_id
     * @param $content
     * @param $type
     * @return bool
     */
    public function sendMsg($user_id,$to_user_id,$content,$type)
    {

        if($user_id &&$to_user_id &&$content && $type){
            $msg=new UserMsg();
            $msg->type=$type;
            $msg->user_id=$user_id;
            $msg->to_user_id=$to_user_id;
            $msg->content=$content;
            $msg->is_red=0;

            if($msg->save()){
                return true;
            }else{
                return false;
            }

        }else{
            return false;
        }


    }

    /**
     * 发送互相心动短信
     * @param $phone
     * @param $user_no
     * @return string
     */
    public  function send_Sms($phone,$user_no){
        $easySms = new EasySms(config('easysms.config'));
        try{
            $easySms->send($phone, [
                'template' => 'SMS_143861585',
                'data' => [
                    'userid' => $user_no
                ],
            ]);

            return '发送成功';

        }catch (\Exception $exception){
            return $exception->getMessage();
        }
    }

    /**
     * 验证VIP会员
     * @param $user_id
     * @return array
     */
    public function is_vip($user_id){
        $vip = UserVip::where('user_id',$user_id)
            ->with('vip')
            ->first();
        if ($vip) {
            if ($vip->end_time < date('Y-m-d H:i:s')){
                return [
                    'msg'=>'您的会员已过期！',
                    'status'=>false,
                ];
            }else{
                return [
                    'msg'=>'您是会员',
                    'status'=>true,
                ];
            }
        }else{
            return [
                'msg'=>'您暂未开通会员！',
                'status'=>false,
            ];
        }
    }

    /**
     * 获取用户小程序二维码
     * @param $user_id
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @return bool
     */
    public function getUserCode($user_id){

        $config=config('wechat.config');
        $app = Factory::miniProgram($config);
        $scene = 'user_id='.$user_id;
        $response = $app->app_code->getUnlimit((string) $scene, (array) $optional = []);

        // 保存小程序码到文件
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            //$img = $this->data_uri($response,'image/png');
            $m = '/upload/code/'.date('Y-m-d');
            $path = public_path($m);
            $filename = $response->saveAs($path,$user_id.'_'.md5(date('Y-m-d H:i:s').$user_id).'jpg');
            $url = $m.'/'.$filename;

        }else{
            $url = false;
        }

        return $url;
    }

    //二进制转图片image/png
    public function data_uri($contents, $mime)
    {
        $base64   = base64_encode($contents);
        return ('data:' . $mime . ';base64,' . $base64);
    }

    /**
     * 时间格式化
     * @param $time
     * @return false|string
     */
    public function setTime($time){
        $rtime = date("m-d H:i",strtotime($time));
        $htime = date("H:i",strtotime($time));
        $time = time() - strtotime($time);
        if ($time < 60){
            $str = '刚刚';
        }elseif ($time < 60 * 60){
            $min = floor($time/60);
            $str = $min.'分钟前';
        }elseif ($time < 60 * 60 * 24){
            $h = floor($time/(60*60));
            $str = $h.'小时前 '.$htime;
        }elseif ($time < 60 * 60 * 24 * 3){
            $d = floor($time/(60*60*24));
            if($d==1)
                $str = '昨天 '.$rtime;
            else
                $str = '前天 '.$rtime;
        }else{
            $str = $rtime;
        }

        return $str;
    }


}
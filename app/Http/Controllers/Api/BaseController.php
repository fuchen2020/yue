<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/21
 * Time: 22:42
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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

//            dd($res);


            foreach ($res['result'] as $re ){

                if(!$re['res_msg'] && !$re['res_code']){
                    return $re['data']['face'];
                }
            }

            return false;

        }else{

            return false;
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


}
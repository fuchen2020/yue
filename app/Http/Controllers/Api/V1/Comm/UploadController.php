<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/28
 * Time: 20:54
 */

namespace App\Http\Controllers\Api\V1\Comm;


use App\Http\Controllers\Api\BaseController;

class UploadController extends BaseController
{

    /**
     * 图片上传
     * @param $file 文件对象
     * @param string $path  专用文件夹
     * @return array
     */
    public function upload($file,$path='qt'){
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
                $destinationPath = '/uploads'.$paths; // 文件保存路径
                $extension = $value->getClientOriginalExtension();   // 上传文件后缀
                $fileName = date('YmdHis').mt_rand(100,999).'.'.$extension; // 重命名
                $value->move(public_path().$destinationPath, $fileName); // 保存图片
                $filePath[] = $paths.'/'.$fileName;
            }
        }
        return $filePath;
    }


}
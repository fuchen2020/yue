<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2018/11/28
 * Time: 20:54
 */

namespace App\Http\Controllers\Api\V1\Comm;


use App\Http\Components\Code;
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



    public function uploadOss($file,$path='mini/'){
       try{

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
                   $paths=$path.'/'.date('Y-m-d').'/';// 文件保存路径
                   $extension = $value->getClientOriginalExtension();   // 上传文件后缀
                   $fileName = md5(date('YmdHis').mt_rand(1000,9999)).'.'.$extension; // 重命名

                   $fileNamePath = $paths.$fileName; //文件保存路径+文件名

                   $disk = \Storage::disk('oss');//引入storage类和oss文件驱动

                   if ($disk->put($fileNamePath,$value)) {

                       $fileUrl = $disk->get($fileNamePath);
                   }else{

                       $fileUrl = false;
                   }
               }else{
                   $fileUrl = false;
               }
           }

           return $fileUrl;

       }catch (\Exception $exception){

           dump($exception);
          return false;
       }

    }



    public function oss($fileContents){
        $disk = \Storage::disk('oss');

        // create a file
            $disk->put('avatars/filename.jpg', $fileContents);

        // check if a file exists
            $exists = $disk->has('file.jpg');

        // get timestamp
            $time = $disk->lastModified('file1.jpg');
            $time = $disk->getTimestamp('file1.jpg');

        // copy a file
            $disk->copy('old/file1.jpg', 'new/file1.jpg');

        // move a file
            $disk->move('old/file1.jpg', 'new/file1.jpg');

        // get file contents
            $contents = $disk->read('folder/my_file.txt');

        // get file url
            $url = $disk->getUrl('folder/my_file.txt');
    }

}
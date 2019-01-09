<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 2019/1/7
 * Time: 20:29
 */

namespace App\Http\Controllers\Api\V1\User;


use App\Http\Components\Code;
use App\Http\Controllers\Api\BaseController;
use App\Models\Api\Circle;
use App\Models\Api\Comment;
use App\Models\Api\Fabulous;
use App\Models\Api\Share;
use App\Models\Api\UserVip;
use Illuminate\Http\Request;

class CircleController extends BaseController
{

    /**
     * 获取圈子动态列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCircleList(Request $request){

        try{

            $user_id=auth()->id();//todo 获取当前登录用户id
            $page_num=$request->get('page')?:1;

            //圈子列表
            $release=new Circle();
            //帖子是状态正常
            $release=$release->where('status',1);

            $serve=$release->with(['user'=>function($user){
                $user->select('id','nickname','head','birthday','province','city');
            }])
            ->select([
               'id','user_id','content','images','is_top','status',
                'read_num','share_num','fabulou_num','shai_num','comment_num','created_at',
                'report_num',
            ])
            ->orderByDesc('is_top')
            ->orderByDesc('created_at')
            ->offset(10*$page_num-10)
            ->limit(10)
            ->get()
            ->toArray();

            if ($serve){

                foreach ($serve as $item=>&$value){
                    //点赞状态
                    if (Fabulous::where('user_id',$user_id)
                        ->where('article_id',$value['id'])
                        ->exists()){
                        $value['fabulous_type']=true;
                    }else{
                        $value['fabulous_type']=false;
                    }
                    //vip状态

                    if (UserVip::where('user_id',$user_id)
                        ->where('end_time','>',date('Y-m-d H:i:s'))
                        ->exists()){
                        $value['is_vip']=true;
                    }else{
                        $value['is_vip']=false;
                    }

                    $value['images'] = explode(',',$value['images']);

                    //发布时间信息
                    $value['time_info']=$this->setTime($value['created_at']);

                }

                return response()->json([
                    'code'=>200,
                    'msg'=>'获取圈子列表成功!',
                    'status'=>true,
                    'result'=>$serve
                ]);
            }else{
                return response()->json([
                    'code'=>200,
                    'msg'=>'暂无更多数据!',
                    'status'=>true,
                    'result'=>''
                ]);
            }

        }catch (\Exception $exception){

            return response()->json(['msg' => '方法执行异常','code'=>500,'err'=>$exception->getMessage()]);
        }

    }

    /**
     * 发布动态
     * @param Request $request
     * @param  content 内容
     * @param  images 图片
     * @return \Illuminate\Http\JsonResponse
     */
    public function release(Request $request){

        try{
            $validator = \Validator::make($request->all(), [
                'content' =>'required|between:1,1000',
            ], [
                'content.required' => '内容不能为空',
                'content.between' => '内容字数不能超过1000个',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => $validator->errors()->first()
                ]);
            }
            $user_id=auth()->id();
            $data = $request->all();
            $release = new Circle();
            $release->user_id = $user_id;
            $release->content = $data['content'];
            $release->images = $data['images'];
            $release->release_time = time();

            if ($release->save()){
                return response()->json([
                    'code'=>200,
                    'status'=>true,
                    'msg'=>'发布动态成功',
                    'result'=>$release->id,
                ]);
            }else {
                return response()->json([
                    'code'=>200,
                    'status'=>false,
                    'msg'=>'发布动态成功失败',
                    'result'=>''
                ]);
            }


        }catch (\Exception $exception){

            return response()->json(['msg' => '方法执行异常','code'=>500,'err'=>$exception->getMessage()]);
        }

    }

    /**
     * 获取圈子动态详情
     * @param Request $request
     * @param  id 动态id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(Request $request){

        try{
            $user_id=auth()->id();//todo 获取当前登录用户id
            $id=$request->get('id');
            $result=Circle::where('id',$id)->with(['user'=>function($user){
                $user->select('id','nickname','head','birthday','province','city');
            }])
                ->select([
                    'id','user_id','content','images','is_top','status',
                    'read_num','share_num','fabulou_num','shai_num','comment_num',
                    'report_num','created_at',
                ])
                ->first();

            //获取当前点赞状态
            if (Fabulous::where(['user_id'=>$user_id, 'article_id'=>$id])->exists()){
                $result['fabulous_type']=true;
            }else{
                $result['fabulous_type']=false;
            }

            //vip状态

            if (UserVip::where('user_id',$user_id)
                ->where('end_time','>',date('Y-m-d H:i:s'))
                ->exists()){
                $result['is_vip']=true;
            }else{
                $result['is_vip']=false;
            }

            $result['images'] = explode(',',$result['images']);

            //发布时间信息
            $result['time_info']=$this->setTime($result['created_at']);


            //获取格式化时间
            $result['time_info']=$this->setTime($result['created_at']);
            //获取评论列表
            $commentAll=Comment::where('article_id',$id)->get();
            //获取评论数
            $result['comment_num']=$commentAll->count();

            if ($result){

                //添加阅读量
                Circle::where('id',$id)->increment('read_num');

                return response()->json([
                    'code'=>200,
                    'msg'=>'获取圈子动态详情成功!',
                    'status'=>true,
                    'result'=>$result
                ]);
            }else{
                return response()->json([
                    'code'=>200,
                    'msg'=>'获取圈子动态详情失败!',
                    'status'=>false,
                    'result'=>''
                ]);
            }


        }catch (\Exception $exception){

            return response()->json(['msg' => '方法执行异常','code'=>500,'err' => $exception->getMessage()]);
        }

    }

    /**
     * 获取动态评论
     * @param Request $request
     * @param page
     * @param id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComment(Request $request){

        try{
            $id=$request->input('id');
            $page = $request->get('page') ?: 1;
            $pageSize = $request->get('size') ?: 10;
            $num=$request->input('num')?:10;
            //获取评论列表
            $commentAll=Comment::where('article_id',$id)
                ->with(['user'=>function($user){
                    $user->select('id','nickname');
                }])
                ->with(['toUser'=>function($user){
                    $user->select('id','nickname');
                }])
                ->get()
                ->toArray();
            $comments=Comment::where('article_id',$id)
                ->where('path','like','%-0-%')
                ->with(['user'=>function($user){
                    $user->select('id','nickname','head','birthday','province','city');
                }])
                ->with(['toUser'=>function($user){
                    $user->select('id','nickname','head','birthday','province','city');
                }])
                ->select([
                    'id','content','user_id','to_user_id','article_id','path','created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->forPage($page, $pageSize)
                ->limit($num)
                ->get()
                ->toArray();
            //遍历拼接回复评论
            $coms=[];
            foreach ($comments as $kc=>$vc){
                $coms[$kc]=$vc;
                foreach ($commentAll as $kcc=>$vcc){
                    $path='-'.$vc['id'].'-';
                    if( strstr($vcc['path'],$path)){
                        $coms[$kc]['reply'][$kcc]=$vcc;
                    }
                }
            }

            if ($coms){
                return response()->json([
                    'code'=>200,
                    'msg'=>'获取评论回复列表成功!',
                    'status'=>true,
                    'result'=>$coms
                ]);
            }else{
                return response()->json([
                    'code'=>200,
                    'msg'=>'暂无更多列表数据!',
                    'status'=>false,
                    'result'=>''
                ]);
            }


        }catch (\Exception $exception){

            response()->json(['msg' => '方法执行异常','code'=>500]);
        }

    }

    /**
     * 动态评论
     * @param Request $request
     * @param content
     * @param id
     * @return CircleController|\Illuminate\Http\JsonResponse
     */
    public function comment(Request $request){
        try{
            $validator = \Validator::make($request->all(), [
                'content' =>'required|between:1,200',
                'id' =>'required',
            ],[
                'content.required' => '内容不能为空',
                'content.between' => '内容字数不能超过200个',
                'id.required' => '动态ID不能为空',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => $validator->errors()->first()
                ]);
            }
            $user_id=auth()->id();
            $result=$request->all();
            $comment=new Comment();
            $comment->content=$result['content'];
            $comment->user_id=$user_id;
            $comment->article_id=$result['id'];
            $comment->path='-0-';
            $comment->release_user=Circle::where('id',$result['id'])->value('user_id');
            if ($comment->save()){
                Circle::where('id',$result['id'])->increment('comment_num',1);
                return response()->json([
                    'code'=>200,
                    'status'=>true,
                    'msg'=>'评论成功'
                ]);
            }else{
                return response()->json([
                    'code'=>400,
                    'status'=>false,
                    'msg'=>'评论失败'
                ]);
            }

        }catch (\Exception $exception){

            return $this->sendError(Code::FAIL3, $exception->getMessage());
        }

    }

    /**
     * 回复动态评论
     * @param Request $request
     * @param content  回复内容
     * @param id  动态ID
     * @param comment_id  评论ID
     * @param path
     * @return \Illuminate\Http\JsonResponse
     */
    public function reply(Request $request){
       try{
           $validator = \Validator::make($request->all(), [
               'id' =>'required',
               'comment_id' =>'required',
               'path' =>'required',
               'content' =>'required|between:1,200',
           ], [
               'id.required' => '动态ID不能为空',
               'comment_id.required' => '评论ID不能为空',
               'path.required' => '评论path参数不能为空',
               'content.required' => '内容不能为空',
               'content.between' => '内容字数不能超过200个',
           ]);
           if ($validator->fails()) {
               return response()->json([
                   'code' => 400,
                   'status' => false,
                   'msg' => $validator->errors()->first()
               ]);
           }
           $user_id=auth()->id();
           $result=$request->all();
           $comment=new Comment();
           $comment->content=$result['content'];
           $comment->user_id=$user_id;
           $comment->to_user_id=Comment::where('id',$result['comment_id'])->value('user_id');
           $comment->article_id=$result['id'];
           $comment->comment_pid=$result['comment_id'];
           if ($result['path']!='-0-'){
               $comment->path=$result['path'].$result['comment_id'].'-';
           }else{
               $comment->path='-'.$result['comment_id'].'-';
           }
           $comment->release_user=Circle::where('id',$result['id'])->value('user_id');

           if ($comment->save()){
               Circle::where('id',$result['id'])->increment('comment_num',1);
               return response()->json([
                   'code'=>200,
                   'status'=>true,
                   'msg'=>'回复成功'
               ]);
           }else{
               return response()->json([
                   'code'=>400,
                   'status'=>false,
                   'msg'=>'回复失败'
               ]);
           }

       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL3, $exception->getMessage());
       }

    }

    /**
     * 动态点赞
     * @param Request $request
     * @param id
     * @return \Illuminate\Http\JsonResponse
     */
    public function praise(Request $request){
        $user_id=auth()->id();
        $id=$request->get('id');//获取当前动态id
        if (Fabulous::where(['user_id'=>$user_id, 'article_id'=>$id])->exists()){
            Circle::where('id',$id)->decrement('fabulou_num',1);
            $result=Fabulous::where([
                'user_id'=>$user_id,
                'article_id'=>$id,
            ])->delete();
            if ($result){
                return response()->json([
                    'code'=>200,
                    'status'=>true,
                    'msg'=>'取消点赞成功'
                ]);
            }
            return response()->json([
                'code'=>400,
                'status'=>false,
                'msg'=>'取消点赞失败'
            ]);
        }else{
            $collection=new Fabulous();
            $collection->user_id=$user_id;
            $collection->article_id=$id;
            if ($collection->save()){
                Circle::where('id',$id)->increment('fabulou_num',1);
                return response()->json([
                    'code'=>200,
                    'status'=>true,
                    'msg'=>'点赞成功'
                ]);
            }
            return response()->json([
                'code'=>400,
                'status'=>false,
                'msg'=>'点赞失败'
            ]);
        }
    }


    /**
     * 圈子动态分享
     * @param Request $request
     * @param id
     * @return \Illuminate\Http\JsonResponse
     */
    public function share(Request $request){

        try{
            $user=auth()->user();
            $id=$request->get('id');//获取当前文章id
            $share=new Share();
            $share->user_id=$user->id;
            $share->article_id=$id;
            if ($share->save()){
                Circle::where('id',$id)->increment('share_num',1);
                return response()->json([
                    'code'=>200,
                    'status'=>true,
                    'msg'=>'分享成功'
                ]);
            }
            return response()->json([
                'code'=>200,
                'status'=>false,
                'msg'=>'分享失败'
            ]);


        }catch (\Exception $exception){

            response()->json(['msg' => '方法执行异常','code'=>500]);
        }

    }

    /**
     * 获取我的朋友圈列表
     *  @param Request $request
     * @return CircleController|\Illuminate\Http\JsonResponse
     */
    public function getMyCircleList(Request $request){
       try{



       }catch (\Exception $exception){

          return $this->sendError(Code::FAIL, $exception->getMessage());
       }

    }

}
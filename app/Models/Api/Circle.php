<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
    //
    protected $table = 'circle';

    /**
     * 关联用户
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    /**
     * 关联分享
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function share_num(){
        return $this->hasOne(Share::class,'article_id','id');
    }

    /**
     * 关联点赞
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function zanNum(){
        return $this->hasOne(Fabulous::class,'article_id','id');
    }

    /**
     * 关联评论
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function commentNum(){
        return $this->hasOne(Comment::class,'article_id','id');
    }

}

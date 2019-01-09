<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    protected $table = 'comment';

    /** 关联动态用户
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }

    /**
     * 关联对象用户
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function toUser(){
        return $this->hasOne(User::class,'id','to_user_id');
    }

}

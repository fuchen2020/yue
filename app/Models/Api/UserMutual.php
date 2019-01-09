<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class UserMutual extends Model
{
    //

    protected $table = 'user_mutual_xd';

    /**
     * 关联用户数据1
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function toUser(){

        return $this->hasOne(User::class,'id','to_user_id');
    }

    /**
     * 关联用户数据2
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user(){

        return $this->hasOne(User::class,'id','user_id');
    }

}

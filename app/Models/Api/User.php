<?php

namespace App\Models\Api;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{

    use Notifiable;

    protected $table='user';
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
        // TODO: Implement getJWTIdentifier() method.
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
        // TODO: Implement getJWTCustomClaims() method.
    }


    //关联相册

    public function photo(){

        return $this->hasMany(UserPhoto::class,'user_id',$this->id);
    }


    //关联vip

    public function vip(){

        return $this->hasOne(UserVip::class,'user_id',$this->id);
    }

    //关联择偶需求

    public function require(){

        return $this->hasOne(UserRequire::class,'user_id',$this->id);
    }





}

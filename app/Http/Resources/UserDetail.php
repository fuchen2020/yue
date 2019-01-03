<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserDetail extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            "nickname" => $this->nickname,
            "head" => $this->head,
            "sex" => $this->sex,
            "tall" => $this->tall,
            "birthday" => $this->birthday,
            "province" => $this->province,
            "education" => $this->education,
            "city" => $this->city,
            "phone" => $this->phone,
            "wx_no" => $this->wx_no,
            "qq" => $this->qq,
            "marriage" =>$this->marriage,
            "heart" => $this->heart,
            "occupation" => $this->occupation,
            "salary" => $this->salary,
            "school" => $this->school,
            "housing" => $this->housing,
            "native_place" => $this->native_place,
            "living_place" => $this->living_place,
            "is_card" => $this->is_card,
            "is_xue" => $this->is_xue,
            "is_fen" => $this->is_fen,
            "is_hu" => $this->is_hu,
            "is_lock" => $this->is_lock,
            "is_jia" => $this->is_jia,
            "photo" => $this->photo,
            "require" => $this->require,
            "extend" => $this->extend,
            'circle' => '',
        ];

        //最新一条圈子动态




        return $data;
    }
}

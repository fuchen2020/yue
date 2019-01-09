<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MyLikeList extends Resource
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
            'to_user_id' => $this->to_user_id,
            "nickname" => $this->toUser->nickname,
            "head" => $this->toUser->head,
            "sex" => $this->toUser->sex,
            "tall" => $this->toUser->tall,
            "birthday" => $this->toUser->birthday,
            "city" => $this->toUser->city,
            "marriage" => $this->toUser->marriage,
            "occupation" => $this->toUser->occupation,
            "heart" => $this->toUser->heart,
            "is_card" => $this->toUser->is_card,
            "is_xue" => $this->toUser->is_xue,
            "photo" => $this->toUser->photo,
//            "vip" => $this->toUser->vip?true:false
        ];

        return $data;
    }
}

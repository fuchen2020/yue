<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class LikeMeList extends Resource
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
            'to_user_id' => $this->user_id,
            "nickname" => $this->user->nickname,
            "head" => $this->user->head,
            "sex" => $this->user->sex,
            "tall" => $this->user->tall,
            "birthday" => $this->user->birthday,
            "city" => $this->user->city,
            "marriage" => $this->user->marriage,
            "occupation" => $this->user->occupation,
            "heart" => $this->user->heart,
            "is_card" => $this->user->is_card,
            "is_xue" => $this->user->is_xue,
            "photo" => $this->user->photo,
//            "vip" => $this->toUser->vip?true:false
        ];

        return $data;
    }
}

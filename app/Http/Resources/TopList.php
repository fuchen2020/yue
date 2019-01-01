<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TopList extends Resource
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
            "city" => $this->city,
            "marriage" => $this->marriage,
            "occupation" => $this->occupation,
            "heart" => $this->heart,
            "is_card" => $this->is_card,
            "is_xue" => $this->is_xue,
            "photo" => $this->photo,
            "vip" => $this->vip?true:false,
        ];

        return $data;
    }
}

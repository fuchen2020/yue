<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class VipList extends Resource
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
            'vip_name' => $this->vip_name,
            'vip_price' => $this->vip_price,
            'day' => $this->day,
        ];

        return $data;
    }
}

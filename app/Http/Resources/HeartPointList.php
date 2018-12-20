<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class HeartPointList extends Resource
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
          'point_num' => $this->point_num,
          'reason' => $this->reason,
           'created_at' => (string) $this->created_at,
        ];

        return $data;
    }
}

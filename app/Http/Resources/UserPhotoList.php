<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserPhotoList extends Resource
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
          'img' => $this->img,
        ];


        return $data;
    }
}

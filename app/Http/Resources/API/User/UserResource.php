<?php

namespace App\Http\Resources\API\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email ,
            'username' => $this->username,
            'telegram_id' => $this->telegram_id,
            'phone' => $this->phone,
            'photo' => $this->photo,
            'created_at' => $this->created_at,
            'department' => $this->departments,
            'roles' => $this->roles->pluck('name'),
            'stocks' => $this->stocks
        ];
    }
}

<?php

namespace App\Http\Resources\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @format uuid */
            'id' => $this->id,
            'name' => $this->name,
            /** @format email */
            'email' => $this->email,
            // @phpstan-ignore-next-line
            'email_verified_at' => $this->email_verified_at?->toRfc3339String(),
            /** @format uri */
            'avatar' => $this->avatar,
            'is_admin' => $this->is_admin,
        ];
    }
}

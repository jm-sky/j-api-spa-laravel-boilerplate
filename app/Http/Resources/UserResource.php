<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property Carbon $emailVerifiedAt
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 */
class UserResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "emailVerifiedAt" => $this->email_verified_at,
            "createdAt" => $this->created_at,
            "updatedAt" => $this->updated_at,
        ];
    }
}

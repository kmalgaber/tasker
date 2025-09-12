<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->user),
            'title' => $this->title,
            'status' => $this->status,
            'priority' => $this->priority,
            /** @format date */
            'due_date' => $this->due_date?->format('Y-m-d'),
            'assignee' => new UserResource($this->assignee),
            'tags' => TagResource::collection($this->tags),
        ];
    }

    public function with($request): array
    {
        return [
            'data' => [
                'description' => $this->description,
                'created_at' => $this->created_at->toRfc3339String(),
                'updated_at' => $this->updated_at->toRfc3339String(),
                'metadata' => $this->metadata,
            ],
        ];
    }
}

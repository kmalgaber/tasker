<?php

namespace App\Http\Resources\V1;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
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
            'due_date' => $this->whenNotNull($this->due_date?->format('Y-m-d'), null),
            'assignee' => $this->whenNotNull(new UserResource($this->assignee), null),
            'tags' => TagResource::collection($this->tags),
            /**
             * Visible to admins only
             *
             * @format date-time
             */
            'deleted_at' => $this->when(auth()->user()->is_admin ?? false, $this->deleted_at?->toRfc3339String()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function with($request): array
    {
        return [
            'data' => [
                'description' => $this->description,
                /** @format date-time */
                'created_at' => $this->created_at?->toRfc3339String(),
                /** @format date-time */
                'updated_at' => $this->whenNotNull($this->updated_at?->toRfc3339String(), null),
                'metadata' => $this->metadata,
            ],
        ];
    }
}

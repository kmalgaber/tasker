<?php

namespace App\Http\Controllers\V1;

use App\Actions\Tag\CreateTag;
use App\Actions\Tag\UpdateTag;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Tag\CreateRequest;
use App\Http\Requests\V1\Tag\UpdateRequest;
use App\Http\Resources\V1\TagResource;
use App\Models\Tag;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TagController extends Controller
{
    /**
     * List all tags
     */
    public function index(): ResourceCollection
    {
        Gate::authorize('viewAny', Tag::class);

        return TagResource::collection(Tag::all());
    }

    /**
     * Create a new tag
     *
     * Only for admins.
     */
    public function store(CreateRequest $request, CreateTag $action): TagResource
    {
        Gate::authorize('create', Tag::class);

        $tag = $action->execute($request->validated());

        return new TagResource($tag);
    }

    /**
     * Set a new color for the specified tag
     *
     * Only for admins.
     */
    public function update(UpdateRequest $request, Tag $tag, UpdateTag $action): TagResource
    {
        Gate::authorize('update', $tag);

        $tag = $action->execute($request->validated(), $tag);

        return new TagResource($tag);
    }

    /**
     * Permanently delete the tag
     *
     * Only for admins.
     */
    public function destroy(Tag $tag): Response
    {
        Gate::authorize('delete', $tag);

        $tag->tasks()->detach();

        $tag->delete();

        return response()->noContent();
    }
}

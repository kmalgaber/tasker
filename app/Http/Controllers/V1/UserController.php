<?php

namespace App\Http\Controllers\V1;

use App\Actions\User\SearchUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\IndexRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserController extends Controller
{
    /**
     * List users in a paginated format
     */
    public function index(IndexRequest $request, SearchUser $action): ResourceCollection
    {
        return UserResource::collection($action->execute($request->validated()));
    }
}

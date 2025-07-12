<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends ApiController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @tags Users
     */
    public function index(Request $request): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['user.view']);

        $filters = $request->only(['search', 'status', 'role']);
        $users = $this->userService->getUsers($filters);

        return $this->resourceResponse(
            UserResource::collection($users)->additional([
                'meta' => [
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                    ],
                ],
            ]),
            'Users retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @tags Users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        $this->logAction('User Created', $user);

        return $this->resourceResponse(
            new UserResource($user->load('roles')),
            'User created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @tags Users
     */
    public function show(int $id): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['user.view']);

        $user = User::with(['roles.permissions'])->findOrFail($id);

        return $this->resourceResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @tags Users
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $updatedUser = $this->userService->updateUser($user, $request->validated());

        $this->logAction('User Updated', $updatedUser);

        return $this->resourceResponse(
            new UserResource($updatedUser->load('roles')),
            'User updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @tags Users
     */
    public function destroy(int $id): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['user.delete']);

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return $this->errorResponse('You cannot delete yourself', 400);
        }

        $user->delete();

        $this->logAction('User Deleted', $user);

        return $this->successResponse(null, 'User deleted successfully');
    }

    /**
     * Bulk delete users.
     *
     * @tags Users
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $this->checkAuthorization(Auth::user(), ['user.delete']);

        $request->validate([
            /** @example [1, 2, 3] */
            'ids' => 'required|array|min:1',
            /** @example 1 */
            'ids.*' => 'integer|exists:users,id',
        ]);

        $userIds = $request->input('ids');

        // Prevent deletion of current user
        if (in_array(Auth::id(), $userIds)) {
            return $this->errorResponse('You cannot delete yourself', 400);
        }

        $deletedCount = User::whereIn('id', $userIds)->delete();

        $this->logAction('Bulk User Deletion', null, ['deleted_count' => $deletedCount]);

        return $this->successResponse(
            ['deleted_count' => $deletedCount],
            $deletedCount . " users deleted successfully"
        );
    }
}

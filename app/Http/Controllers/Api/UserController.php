<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\User\BulkDeleteUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends ApiController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    /**
     * Users list.
     *
     * @tags Users
     */
    #[QueryParameter('per_page', description: 'Number of users per page.', type: 'int', default: 10, example: 20)]
    #[QueryParameter('search', description: 'Search term for filtering users.', type: 'string', example: 'john')]
    #[QueryParameter('role', description: 'Filter users by role.', type: 'string', example: 'admin')]
    #[QueryParameter('date_from', description: 'Filter users created from this date.', type: 'string', example: '2025-01-01')]
    #[QueryParameter('date_to', description: 'Filter users created until this date.', type: 'string', example: '2025-12-31')]
    #[QueryParameter('sort', description: 'Sort users by field (prefix with - for descending).', type: 'string', example: '-created_at')]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $filters = $request->only(['search', 'status', 'role', 'per_page', 'date_from', 'date_to', 'sort']);
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
     * Create User.
     *
     * @tags Users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->createUserWithRelations($request->validated());

        $this->logAction('User Created', $user);

        return $this->resourceResponse(
            new UserResource($user),
            'User created successfully',
            201
        );
    }

    /**
     * Show User.
     *
     * @tags Users
     */
    public function show(int $id): JsonResponse
    {
        $user = User::with(['roles.permissions'])->findOrFail($id);
        $this->authorize('view', $user);

        return $this->resourceResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Update User.
     *
     * @tags Users
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $updatedUser = $this->userService->updateUserWithRelations($user, $request->validated());

        $this->logAction('User Updated', $updatedUser);

        return $this->resourceResponse(
            new UserResource($updatedUser),
            'User updated successfully'
        );
    }

    /**
     * Delete User.
     *
     * @tags Users
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        if ($user->id === Auth::id()) {
            return $this->errorResponse('You cannot delete yourself', 400);
        }

        $user->delete();

        $this->logAction('User Deleted', $user);

        return $this->successResponse(null, 'User deleted successfully');
    }

    /**
     * Bulk Delete Users.
     *
     * @tags Users
     */
    public function bulkDelete(BulkDeleteUserRequest $request): JsonResponse
    {
        $userIds = $request->input('ids');

        // Prevent deletion of current user
        if (in_array(Auth::id(), $userIds)) {
            return $this->errorResponse('You cannot delete yourself', 400);
        }

        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $user) {
            $this->authorize('delete', $user);
        }

        $deletedCount = User::whereIn('id', $userIds)->delete();

        $this->logAction('Bulk User Deletion', null, ['deleted_count' => $deletedCount]);

        return $this->successResponse(
            ['deleted_count' => $deletedCount],
            $deletedCount . " users deleted successfully"
        );
    }
}

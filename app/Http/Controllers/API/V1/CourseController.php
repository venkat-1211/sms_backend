<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseStoreRequest;
use App\Http\Requests\CourseUpdateRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\CourseCollection;
use App\DTO\CourseDTO;
use App\Actions\CreateCourseAction;
use App\Actions\UpdateCourseAction;
use App\Actions\DeleteCourseAction;
use App\Interfaces\CourseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected CourseRepositoryInterface $repository;

    public function __construct(CourseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request): CourseCollection
    {
        $filters = $request->only([
            'search', 'status', 'min_fee', 'max_fee', 
            'duration', 'sort_by', 'sort_direction'
        ]);

        $filters = array_filter($filters, function ($value) {
            return $value !== '' && $value !== null && $value !== [];
        });

        $perPage = $request->get('per_page');
        if (is_array($perPage)) {
            $perPage = isset($perPage[0]) ? (int) $perPage[0] : 15;
        } else {
            $perPage = (int) $perPage;
        }
        
        if ($perPage < 1) $perPage = 15;
        if ($perPage > 100) $perPage = 100;

        if (isset($filters['sort_direction']) && is_array($filters['sort_direction'])) {
            $filters['sort_direction'] = isset($filters['sort_direction'][0]) 
                ? $filters['sort_direction'][0] 
                : 'asc';
        }

        $courses = $this->repository->all($filters, $perPage);
        
        return new CourseCollection($courses);
    }

    public function store(CourseStoreRequest $request, CreateCourseAction $action): JsonResponse
    {
        $dto = CourseDTO::fromRequest($request->validated());
        $course = $action->execute($dto);

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully',
            'data' => new CourseResource($course),
            'errors' => null,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $course = $this->repository->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Course retrieved successfully',
            'data' => new CourseResource($course),
            'errors' => null,
        ]);
    }

    public function update(CourseUpdateRequest $request, int $id, UpdateCourseAction $action): JsonResponse
    {
        $dto = CourseDTO::fromRequest($request->validated());
        $course = $action->execute($id, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully',
            'data' => new CourseResource($course),
            'errors' => null,
        ]);
    }

    public function destroy(int $id, DeleteCourseAction $action): JsonResponse
    {
        $action->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully',
            'data' => null,
            'errors' => null,
        ]);
    }

    public function search(Request $request): CourseCollection
    {
        $search = $request->get('q', '');
        $filters = $request->only(['status', 'min_fee', 'max_fee', 'duration']);
        
        $filters = array_filter($filters, function ($value) {
            return $value !== '' && $value !== null && $value !== [];
        });

        $perPage = $request->get('per_page');
        if (is_array($perPage)) {
            $perPage = isset($perPage[0]) ? (int) $perPage[0] : 15;
        } else {
            $perPage = (int) $perPage;
        }
        
        if ($perPage < 1) $perPage = 15;
        if ($perPage > 100) $perPage = 100;

        $courses = $this->repository->search($search, $filters, $perPage);

        return new CourseCollection($courses);
    }

    public function stats(): JsonResponse
    {
        $stats = $this->repository->getStats();

        return response()->json([
            'success' => true,
            'message' => 'Course statistics retrieved',
            'data' => $stats,
            'errors' => null,
        ]);
    }

    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit');
        if (is_array($limit)) {
            $limit = isset($limit[0]) ? (int) $limit[0] : 5;
        } else {
            $limit = (int) $limit;
        }
        
        if ($limit < 1) $limit = 5;
        if ($limit > 20) $limit = 20;
        
        $courses = $this->repository->getPopularCourses($limit);

        return response()->json([
            'success' => true,
            'message' => 'Popular courses retrieved',
            'data' => CourseResource::collection($courses),
            'errors' => null,
        ]);
    }

    public function active(): JsonResponse
    {
        $courses = $this->repository->getActiveCourses();

        return response()->json([
            'success' => true,
            'message' => 'Active courses retrieved',
            'data' => CourseResource::collection($courses),
            'errors' => null,
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $course = $this->repository->find($id);
        $course->status = $course->status == 1 ? 0 : 1;
        $course->save();

        return response()->json([
            'success' => true,
            'message' => 'Course status updated successfully',
            'data' => new CourseResource($course),
            'errors' => null,
        ]);
    }
}
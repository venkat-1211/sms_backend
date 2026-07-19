<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\StudentUpdateRequest;
use App\Http\Resources\StudentResource;
use App\Http\Resources\StudentCollection;
use App\DTO\StudentDTO;
use App\Actions\CreateStudentAction;
use App\Actions\UpdateStudentAction;
use App\Actions\DeleteStudentAction;
use App\Actions\BulkDeleteStudentsAction;
use App\Actions\ExportStudentsAction;
use App\Interfaces\StudentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected StudentRepositoryInterface $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request): StudentCollection
    {
        $filters = $request->only([
            'search', 'status', 'gender', 'course_id', 
            'date_from', 'date_to', 'sort_by', 'sort_direction'
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

        $students = $this->repository->all($filters, $perPage);
        
        return new StudentCollection($students);
    }

    public function store(StudentStoreRequest $request, CreateStudentAction $action): JsonResponse
    {
        $dto = StudentDTO::fromRequest($request->validated());
        $student = $action->execute($dto);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => new StudentResource($student),
            'errors' => null,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $student = $this->repository->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Student retrieved successfully',
            'data' => new StudentResource($student),
            'errors' => null,
        ]);
    }

    public function update(StudentUpdateRequest $request, int $id, UpdateStudentAction $action): JsonResponse
    {
        $dto = StudentDTO::fromRequest($request->validated());
        $student = $action->execute($id, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => new StudentResource($student),
            'errors' => null,
        ]);
    }

    public function destroy(int $id, DeleteStudentAction $action): JsonResponse
    {
        $action->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
            'data' => null,
            'errors' => null,
        ]);
    }

    public function search(Request $request): StudentCollection
    {
        $search = $request->get('q', '');
        $filters = $request->only(['status', 'gender', 'course_id']);
        
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

        $students = $this->repository->search($search, $filters, $perPage);

        return new StudentCollection($students);
    }

    public function bulkDelete(Request $request, BulkDeleteStudentsAction $action): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:students,id'],
        ]);

        $result = $action->execute($request->ids);

        return response()->json([
            'success' => true,
            'message' => "Deleted {$result['deleted']} students, {$result['failed']} failed",
            'data' => $result,
            'errors' => null,
        ]);
    }

    public function export(Request $request, ExportStudentsAction $action): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'gender', 'course_id']);
        $filters = array_filter($filters, function ($value) {
            return $value !== '' && $value !== null && $value !== [];
        });
        
        $students = $action->execute($filters);

        return response()->json([
            'success' => true,
            'message' => 'Students exported successfully',
            'data' => $students,
            'errors' => null,
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = $this->repository->getStats();

        return response()->json([
            'success' => true,
            'message' => 'Student statistics retrieved',
            'data' => $stats,
            'errors' => null,
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $limit = $request->get('limit');
        if (is_array($limit)) {
            $limit = isset($limit[0]) ? (int) $limit[0] : 5;
        } else {
            $limit = (int) $limit;
        }
        
        if ($limit < 1) $limit = 5;
        if ($limit > 20) $limit = 20;
        
        $students = $this->repository->getRecentStudents($limit);

        return response()->json([
            'success' => true,
            'message' => 'Recent students retrieved',
            'data' => StudentResource::collection($students),
            'errors' => null,
        ]);
    }

    public function genderDistribution(): JsonResponse
    {
        $distribution = $this->repository->getGenderDistribution();

        return response()->json([
            'success' => true,
            'message' => 'Gender distribution retrieved',
            'data' => $distribution,
            'errors' => null,
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $student = $this->repository->find($id);
        $student->status = $student->status == 1 ? 0 : 1;
        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Student status updated successfully',
            'data' => new StudentResource($student),
            'errors' => null,
        ]);
    }

    public function byCourse(int $courseId): JsonResponse
    {
        $students = $this->repository->getStudentsByCourse($courseId);

        return response()->json([
            'success' => true,
            'message' => 'Students by course retrieved',
            'data' => StudentResource::collection($students),
            'errors' => null,
        ]);
    }
}
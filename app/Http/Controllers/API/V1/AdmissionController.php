<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdmissionStoreRequest;
use App\Http\Requests\AdmissionUpdateRequest;
use App\Http\Requests\AdmissionPaymentRequest;
use App\Http\Resources\AdmissionResource;
use App\Http\Resources\AdmissionCollection;
use App\DTO\AdmissionDTO;
use App\Actions\CreateAdmissionAction;
use App\Actions\UpdateAdmissionAction;
use App\Actions\DeleteAdmissionAction;
use App\Actions\PayAdmissionFeeAction;
use App\Interfaces\AdmissionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    protected AdmissionRepositoryInterface $repository;

    public function __construct(AdmissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request): AdmissionCollection
    {
        $filters = $request->only([
            'search', 'student_id', 'course_id', 'payment_status',
            'status', 'date_from', 'date_to', 'min_fee', 'max_fee',
            'sort_by', 'sort_direction'
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

        $admissions = $this->repository->all($filters, $perPage);
        
        return new AdmissionCollection($admissions);
    }

    public function store(AdmissionStoreRequest $request, CreateAdmissionAction $action): JsonResponse
    {
        $dto = AdmissionDTO::fromRequest($request->validated());
        $admission = $action->execute($dto);

        return response()->json([
            'success' => true,
            'message' => 'Admission created successfully',
            'data' => new AdmissionResource($admission),
            'errors' => null,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $admission = $this->repository->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Admission retrieved successfully',
            'data' => new AdmissionResource($admission),
            'errors' => null,
        ]);
    }

    public function update(AdmissionUpdateRequest $request, int $id, UpdateAdmissionAction $action): JsonResponse
    {
        $dto = AdmissionDTO::fromRequest($request->validated());
        $admission = $action->execute($id, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Admission updated successfully',
            'data' => new AdmissionResource($admission),
            'errors' => null,
        ]);
    }

    public function destroy(int $id, DeleteAdmissionAction $action): JsonResponse
    {
        $action->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Admission deleted successfully',
            'data' => null,
            'errors' => null,
        ]);
    }

    public function search(Request $request): AdmissionCollection
    {
        $search = $request->get('q', '');
        $filters = $request->only([
            'student_id', 'course_id', 'payment_status', 
            'status', 'date_from', 'date_to'
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

        $admissions = $this->repository->search($search, $filters, $perPage);

        return new AdmissionCollection($admissions);
    }

    public function payFee(AdmissionPaymentRequest $request, int $id, PayAdmissionFeeAction $action): JsonResponse
    {
        $admission = $action->execute($id, $request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Payment made successfully',
            'data' => new AdmissionResource($admission),
            'errors' => null,
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = $this->repository->getStats();

        return response()->json([
            'success' => true,
            'message' => 'Admission statistics retrieved',
            'data' => $stats,
            'errors' => null,
        ]);
    }

    public function revenue(): JsonResponse
    {
        $revenue = $this->repository->getRevenueStats();

        return response()->json([
            'success' => true,
            'message' => 'Revenue statistics retrieved',
            'data' => $revenue,
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
        
        $admissions = $this->repository->getRecentAdmissions($limit);

        return response()->json([
            'success' => true,
            'message' => 'Recent admissions retrieved',
            'data' => AdmissionResource::collection($admissions),
            'errors' => null,
        ]);
    }

    public function byStudent(int $studentId): JsonResponse
    {
        $admissions = $this->repository->getAdmissionsByStudent($studentId);

        return response()->json([
            'success' => true,
            'message' => 'Student admissions retrieved',
            'data' => AdmissionResource::collection($admissions),
            'errors' => null,
        ]);
    }

    public function byCourse(int $courseId): JsonResponse
    {
        $admissions = $this->repository->getAdmissionsByCourse($courseId);

        return response()->json([
            'success' => true,
            'message' => 'Course admissions retrieved',
            'data' => AdmissionResource::collection($admissions),
            'errors' => null,
        ]);
    }

    public function paymentDistribution(): JsonResponse
    {
        $distribution = $this->repository->getPaymentStatusDistribution();

        return response()->json([
            'success' => true,
            'message' => 'Payment distribution retrieved',
            'data' => $distribution,
            'errors' => null,
        ]);
    }

    public function monthlyRevenue(Request $request): JsonResponse
    {
        $months = $request->get('months');
        if (is_array($months)) {
            $months = isset($months[0]) ? (int) $months[0] : 12;
        } else {
            $months = (int) $months;
        }
        
        if ($months < 1) $months = 12;
        if ($months > 36) $months = 36;
        
        $revenue = $this->repository->getMonthlyRevenue($months);

        return response()->json([
            'success' => true,
            'message' => 'Monthly revenue retrieved',
            'data' => $revenue,
            'errors' => null,
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $admission = $this->repository->find($id);
        $admission->status = $admission->status == 1 ? 0 : 1;
        $admission->save();

        return response()->json([
            'success' => true,
            'message' => 'Admission status updated successfully',
            'data' => new AdmissionResource($admission),
            'errors' => null,
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'student_id', 'course_id', 'payment_status',
            'date_from', 'date_to'
        ]);
        
        $filters = array_filter($filters, function ($value) {
            return $value !== '' && $value !== null && $value !== [];
        });
        
        $admissions = $this->repository->all($filters, 1000);

        return response()->json([
            'success' => true,
            'message' => 'Admissions exported successfully',
            'data' => $admissions->items(),
            'errors' => null,
        ]);
    }
}
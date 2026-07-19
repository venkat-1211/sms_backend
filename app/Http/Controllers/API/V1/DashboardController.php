<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Interfaces\StudentRepositoryInterface;
use App\Interfaces\CourseRepositoryInterface;
use App\Interfaces\AdmissionRepositoryInterface;
use App\Http\Resources\DashboardResource;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected StudentRepositoryInterface $studentRepository;
    protected CourseRepositoryInterface $courseRepository;
    protected AdmissionRepositoryInterface $admissionRepository;

    public function __construct(
        StudentRepositoryInterface $studentRepository,
        CourseRepositoryInterface $courseRepository,
        AdmissionRepositoryInterface $admissionRepository
    ) {
        $this->studentRepository = $studentRepository;
        $this->courseRepository = $courseRepository;
        $this->admissionRepository = $admissionRepository;
    }

    /**
     * Get dashboard data
     */
    public function index(): JsonResponse
    {
        $data = [
            'stats' => $this->getStats(),
            'recent_students' => $this->studentRepository->getRecentStudents(5),
            'recent_admissions' => $this->admissionRepository->getRecentAdmissions(5),
            'popular_courses' => $this->courseRepository->getPopularCourses(5),
            'payment_distribution' => $this->admissionRepository->getPaymentStatusDistribution(),
            'monthly_revenue' => $this->admissionRepository->getMonthlyRevenue(12),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved',
            'data' => new DashboardResource($data),
            'errors' => null,
        ]);
    }

    /**
     * Get dashboard statistics only
     */
    public function stats(): JsonResponse
    {
        $stats = $this->getStats();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics retrieved',
            'data' => $stats,
            'errors' => null,
        ]);
    }

    /**
     * Get all statistics
     */
    private function getStats(): array
    {
        $studentStats = $this->studentRepository->getStats();
        $courseStats = $this->courseRepository->getStats();
        $admissionStats = $this->admissionRepository->getStats();
        $revenueStats = $this->admissionRepository->getRevenueStats();

        return [
            'students' => [
                'total' => $studentStats['total'],
                'active' => $studentStats['active'],
                'inactive' => $studentStats['inactive'],
                'male' => $studentStats['male'],
                'female' => $studentStats['female'],
            ],
            'courses' => [
                'total' => $courseStats['total'],
                'active' => $courseStats['active'],
                'inactive' => $courseStats['inactive'],
                'total_fee' => $courseStats['total_fee'],
                'avg_fee' => $courseStats['avg_fee'],
            ],
            'admissions' => [
                'total' => $admissionStats['total'],
                'pending' => $admissionStats['pending'],
                'partial' => $admissionStats['partial'],
                'paid' => $admissionStats['paid'],
            ],
            'revenue' => [
                'total_revenue' => $revenueStats['total_revenue'],
                'total_fee' => $revenueStats['total_fee'],
                'total_balance' => $revenueStats['total_balance'],
                'collection_rate' => $revenueStats['collection_rate'],
                'pending_amount' => $revenueStats['pending_amount'],
                'partial_amount' => $revenueStats['partial_amount'],
            ],
        ];
    }

    /**
     * Clear dashboard cache - kept for compatibility but does nothing
     */
    public function clearCache(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cache clearing is disabled',
            'data' => null,
            'errors' => null,
        ]);
    }
}
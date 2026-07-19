<?php

namespace App\Repositories;

use App\Interfaces\AdmissionRepositoryInterface;
use App\Models\Admission;
use Illuminate\Support\Facades\DB;

class AdmissionRepository implements AdmissionRepositoryInterface
{
    protected $model;

    public function __construct(Admission $model)
    {
        $this->model = $model;
    }

    /**
     * Clean filters recursively to handle array values
     */
    private function cleanFilters(array $filters): array
    {
        $cleaned = [];
        foreach ($filters as $key => $value) {
            if ($value === '' || $value === null || $value === []) {
                continue;
            }
            
            if (is_array($value)) {
                $arrayValues = array_filter($value, function ($v) {
                    return $v !== '' && $v !== null;
                });
                
                if (!empty($arrayValues)) {
                    $firstValue = reset($arrayValues);
                    if (is_array($firstValue)) {
                        $cleaned[$key] = $this->cleanFilters($firstValue);
                    } else {
                        $cleaned[$key] = $firstValue;
                    }
                }
            } else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }

    public function all(array $filters = [], int $perPage = 15)
    {
        // Clean filters first
        $filters = $this->cleanFilters($filters);

        // Ensure perPage is an integer
        if (is_array($perPage)) {
            $perPage = isset($perPage[0]) ? (int) $perPage[0] : 15;
        }
        $perPage = is_numeric($perPage) ? (int) $perPage : 15;
        
        if ($perPage < 1) $perPage = 15;
        if ($perPage > 100) $perPage = 100;

        $query = $this->model->query()
            ->with(['student', 'course'])
            ->select([
                'admissions.id',
                'admissions.uuid',
                'admissions.student_id',
                'admissions.course_id',
                'admissions.admission_date',
                'admissions.total_fee',
                'admissions.amount_paid',
                'admissions.balance_fee',
                'admissions.payment_status',
                'admissions.status',
                'admissions.created_at',
                'admissions.updated_at',
            ]);

        // Search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('full_name', 'LIKE', "%{$search}%")
                       ->orWhere('email', 'LIKE', "%{$search}%")
                       ->orWhere('mobile', 'LIKE', "%{$search}%");
                })->orWhereHas('course', function ($cq) use ($search) {
                    $cq->where('course_name', 'LIKE', "%{$search}%");
                });
            });
        }

        // Student filter
        if (isset($filters['student_id']) && is_numeric($filters['student_id'])) {
            $studentId = is_array($filters['student_id']) ? (int) reset($filters['student_id']) : (int) $filters['student_id'];
            $query->where('student_id', $studentId);
        }

        // Course filter
        if (isset($filters['course_id']) && is_numeric($filters['course_id'])) {
            $courseId = is_array($filters['course_id']) ? (int) reset($filters['course_id']) : (int) $filters['course_id'];
            $query->where('course_id', $courseId);
        }

        // Payment status filter
        if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
            $paymentStatus = is_array($filters['payment_status']) ? reset($filters['payment_status']) : $filters['payment_status'];
            $query->where('payment_status', $paymentStatus);
        }

        // Status filter
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $status = is_array($filters['status']) ? (int) reset($filters['status']) : (int) $filters['status'];
            $query->where('status', $status);
        }

        // Date filters
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $dateFrom = is_array($filters['date_from']) ? reset($filters['date_from']) : $filters['date_from'];
            $query->whereDate('admission_date', '>=', $dateFrom);
        }
        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $dateTo = is_array($filters['date_to']) ? reset($filters['date_to']) : $filters['date_to'];
            $query->whereDate('admission_date', '<=', $dateTo);
        }

        // Fee range filters
        if (isset($filters['min_fee']) && is_numeric($filters['min_fee'])) {
            $minFee = is_array($filters['min_fee']) ? (float) reset($filters['min_fee']) : (float) $filters['min_fee'];
            $query->where('total_fee', '>=', $minFee);
        }
        if (isset($filters['max_fee']) && is_numeric($filters['max_fee'])) {
            $maxFee = is_array($filters['max_fee']) ? (float) reset($filters['max_fee']) : (float) $filters['max_fee'];
            $query->where('total_fee', '<=', $maxFee);
        }

        // Sorting
        if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
            $sortBy = is_array($filters['sort_by']) ? reset($filters['sort_by']) : $filters['sort_by'];
            $sortDirection = 'asc';
            
            if (isset($filters['sort_direction'])) {
                $sortDirection = is_array($filters['sort_direction']) 
                    ? (isset($filters['sort_direction'][0]) ? strtolower($filters['sort_direction'][0]) : 'asc')
                    : strtolower($filters['sort_direction']);
            }
            
            $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderByDesc('admissions.created_at');
        }

        return $query->paginate($perPage);
    }

    public function find(int $id)
    {
        return $this->model->with(['student', 'course'])->findOrFail($id);
    }

    public function findByUuid(string $uuid)
    {
        return $this->model->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $admission = $this->model->create($data);
            $admission->updatePaymentStatus();
            return $admission;
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $admission = $this->find($id);
            $admission->update($data);
            $admission->updatePaymentStatus();
            return $admission;
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $admission = $this->find($id);
            $admission->delete();
            return true;
        });
    }

    public function search(string $search, array $filters = [], int $perPage = 15)
    {
        $filters['search'] = $search;
        return $this->all($filters, $perPage);
    }

    public function getStats(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->where('status', 1)->count(),
            'pending' => $this->model->where('payment_status', 'pending')->count(),
            'partial' => $this->model->where('payment_status', 'partial')->count(),
            'paid' => $this->model->where('payment_status', 'paid')->count(),
            'total_revenue' => $this->model->sum('amount_paid'),
            'total_fee' => $this->model->sum('total_fee'),
            'total_balance' => $this->model->sum('balance_fee'),
            'avg_fee' => $this->model->avg('total_fee'),
        ];
    }

    public function getRevenueStats(): array
    {
        $totalRevenue = $this->model->sum('amount_paid');
        $totalFee = $this->model->sum('total_fee');
        $totalBalance = $this->model->sum('balance_fee');
        $collectionRate = $totalFee > 0 ? ($totalRevenue / $totalFee) * 100 : 0;
        
        return [
            'total_revenue' => $totalRevenue,
            'total_fee' => $totalFee,
            'total_balance' => $totalBalance,
            'collection_rate' => round($collectionRate, 2),
            'pending_amount' => $this->model->where('payment_status', 'pending')->sum('balance_fee'),
            'partial_amount' => $this->model->where('payment_status', 'partial')->sum('balance_fee'),
        ];
    }

    public function getRecentAdmissions(int $limit = 5)
    {
        if (is_array($limit)) {
            $limit = isset($limit[0]) ? (int) $limit[0] : 5;
        }
        $limit = is_numeric($limit) ? (int) $limit : 5;
        
        if ($limit < 1) $limit = 5;
        if ($limit > 20) $limit = 20;
        
        return $this->model->with(['student', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getAdmissionsByStudent(int $studentId)
    {
        return $this->model->with('course')
            ->where('student_id', $studentId)
            ->get();
    }

    public function getAdmissionsByCourse(int $courseId)
    {
        return $this->model->with('student')
            ->where('course_id', $courseId)
            ->get();
    }

    public function getPaymentStatusDistribution(): array
    {
        return $this->model->select('payment_status', DB::raw('count(*) as total'))
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status')
            ->toArray();
    }

    public function getMonthlyRevenue(int $months = 12)
    {
        if (is_array($months)) {
            $months = isset($months[0]) ? (int) $months[0] : 12;
        }
        $months = is_numeric($months) ? (int) $months : 12;
        
        if ($months < 1) $months = 12;
        if ($months > 36) $months = 36;
        
        $startDate = now()->subMonths($months);
        
        return $this->model
            ->select(
                DB::raw('MONTH(admission_date) as month'),
                DB::raw('YEAR(admission_date) as year'),
                DB::raw('SUM(amount_paid) as revenue'),
                DB::raw('COUNT(*) as admissions_count')
            )
            ->where('admission_date', '>=', $startDate)
            ->where('payment_status', '!=', 'pending')
            ->groupBy(DB::raw('YEAR(admission_date)'), DB::raw('MONTH(admission_date)'))
            ->orderBy(DB::raw('YEAR(admission_date)'), 'asc')
            ->orderBy(DB::raw('MONTH(admission_date)'), 'asc')
            ->get();
    }
}
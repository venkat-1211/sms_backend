<?php

namespace App\Repositories;

use App\Interfaces\StudentRepositoryInterface;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class StudentRepository implements StudentRepositoryInterface
{
    protected $model;

    public function __construct(Student $model)
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
            ->select([
                'id',
                'uuid',
                'full_name',
                'email',
                'mobile',
                'date_of_birth',
                'gender',
                'address',
                'status',
                'created_at',
                'updated_at',
            ]);

        // Search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('mobile', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $status = is_array($filters['status']) ? (int) reset($filters['status']) : (int) $filters['status'];
            $query->where('status', $status);
        }

        // Gender filter
        if (isset($filters['gender']) && !empty($filters['gender'])) {
            $gender = is_array($filters['gender']) ? reset($filters['gender']) : $filters['gender'];
            $query->where('gender', $gender);
        }

        // Course filter
        if (isset($filters['course_id']) && !empty($filters['course_id'])) {
            $courseId = is_array($filters['course_id']) ? (int) reset($filters['course_id']) : (int) $filters['course_id'];
            $query->whereHas('admissions', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        // Date filters
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $dateFrom = is_array($filters['date_from']) ? reset($filters['date_from']) : $filters['date_from'];
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $dateTo = is_array($filters['date_to']) ? reset($filters['date_to']) : $filters['date_to'];
            $query->whereDate('created_at', '<=', $dateTo);
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
            $query->orderByDesc('created_at');
        }

        return $query->paginate($perPage);
    }

    public function find(int $id)
    {
        return $this->model->with(['admissions.course', 'courses'])->findOrFail($id);
    }

    public function findByUuid(string $uuid)
    {
        return $this->model->where('uuid', $uuid)->firstOrFail();
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByMobile(string $mobile)
    {
        return $this->model->where('mobile', $mobile)->first();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->model->create($data);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $student = $this->find($id);
            $student->update($data);
            return $student;
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $student = $this->find($id);
            $student->admissions()->delete();
            $student->delete();
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
            'inactive' => $this->model->where('status', 0)->count(),
            'male' => $this->model->where('gender', 'male')->count(),
            'female' => $this->model->where('gender', 'female')->count(),
            'other' => $this->model->where('gender', 'other')->count(),
        ];
    }

    public function getRecentStudents(int $limit = 5)
    {
        if (is_array($limit)) {
            $limit = isset($limit[0]) ? (int) $limit[0] : 5;
        }
        $limit = is_numeric($limit) ? (int) $limit : 5;
        
        if ($limit < 1) $limit = 5;
        if ($limit > 20) $limit = 20;
        
        return $this->model->with('admissions')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getStudentsByCourse(int $courseId)
    {
        return $this->model->whereHas('admissions', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->get();
    }

    public function getGenderDistribution(): array
    {
        return $this->model->select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();
    }
}
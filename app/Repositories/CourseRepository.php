<?php

namespace App\Repositories;

use App\Interfaces\CourseRepositoryInterface;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseRepository implements CourseRepositoryInterface
{
    protected $model;

    public function __construct(Course $model)
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
                'course_name',
                'duration',
                'total_fee',
                'status',
                'created_at',
                'updated_at',
            ]);

        // Search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = (string) $filters['search'];
            $query->where('course_name', 'LIKE', "%{$search}%");
        }

        // Status filter
        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== null) {
            $status = is_array($filters['status']) ? (int) reset($filters['status']) : (int) $filters['status'];
            $query->where('status', $status);
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

        // Duration filter
        if (isset($filters['duration']) && is_numeric($filters['duration'])) {
            $duration = is_array($filters['duration']) ? (int) reset($filters['duration']) : (int) $filters['duration'];
            $query->where('duration', $duration);
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
        return $this->model->with(['students', 'admissions.student'])->findOrFail($id);
    }

    public function findByUuid(string $uuid)
    {
        return $this->model->where('uuid', $uuid)->firstOrFail();
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
            $course = $this->find($id);
            $course->update($data);
            return $course;
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $course = $this->find($id);
            $course->admissions()->delete();
            $course->delete();
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
            'total_fee' => $this->model->sum('total_fee'),
            'avg_fee' => $this->model->avg('total_fee'),
            'min_fee' => $this->model->min('total_fee'),
            'max_fee' => $this->model->max('total_fee'),
        ];
    }

    public function getPopularCourses(int $limit = 5)
    {
        if (is_array($limit)) {
            $limit = isset($limit[0]) ? (int) $limit[0] : 5;
        }
        $limit = is_numeric($limit) ? (int) $limit : 5;
        
        if ($limit < 1) $limit = 5;
        if ($limit > 20) $limit = 20;
        
        return $this->model->withCount('admissions')
            ->orderBy('admissions_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getActiveCourses()
    {
        return $this->model->where('status', 1)->get();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;
use App\Traits\BinaryUuid;
use App\Enums\GenderEnum;
use App\Models\Concerns\HasBinaryUuid;

class Student extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid, HasAudit;

    protected $fillable = [
        'full_name',
        'email',
        'mobile',
        'date_of_birth',
        'gender',
        'address',
        'status',
        'updated_at' => 'datetime',
        'gender' => GenderEnum::class,
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'uuid' => BinaryUuid::class,
        
    ];

    protected $hidden = [
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'admissions')
                    ->withPivot('admission_date', 'total_fee', 'amount_paid', 'balance_fee', 'payment_status')
                    ->withTimestamps();
    }

    public function getFullNameAttribute($value)
    {
        return ucwords($value);
    }

    public function getGenderLabelAttribute()
    {
        return ucfirst($this->gender);
    }

    public function getStatusLabelAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status == 1 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query, $search) {
            return $query->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('mobile', 'LIKE', "%{$search}%");
        });
    }

    public function scopeFilterByGender($query, $gender)
    {
        return $query->when($gender, function ($query, $gender) {
            return $query->where('gender', $gender);
        });
    }

    public function scopeFilterByCourse($query, $courseId)
    {
        return $query->when($courseId, function ($query, $courseId) {
            return $query->whereHas('admissions', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        });
    }

    public function scopeFilterByDateRange($query, $from, $to)
    {
        return $query->when($from, function ($query) use ($from) {
            return $query->whereDate('created_at', '>=', $from);
        })->when($to, function ($query) use ($to) {
            return $query->whereDate('created_at', '<=', $to);
        });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BinaryUuid;
use App\Traits\HasAudit;
use App\Models\Concerns\HasBinaryUuid;

class Course extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid, HasAudit;

    protected $fillable = [
        'course_name',
        'duration',
        'total_fee',
        'status',
    ];

    protected $casts = [
        'total_fee' => 'decimal:2',
        'duration' => 'integer',
        'status' => 'integer',
        'uuid' => BinaryUuid::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'admissions')
                    ->withPivot('admission_date', 'total_fee', 'amount_paid', 'balance_fee', 'payment_status')
                    ->withTimestamps();
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    public function getStatusLabelAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }

    public function getTotalFeeFormattedAttribute()
    {
        return '$' . number_format($this->total_fee, 2);
    }

    public function getDurationLabelAttribute()
    {
        return $this->duration . ' ' . ($this->duration > 1 ? 'Months' : 'Month');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query, $search) {
            return $query->where('course_name', 'LIKE', "%{$search}%");
        });
    }

    public function scopeFilterByFeeRange($query, $min, $max)
    {
        return $query->when($min, function ($query) use ($min) {
            return $query->where('total_fee', '>=', $min);
        })->when($max, function ($query) use ($max) {
            return $query->where('total_fee', '<=', $max);
        });
    }

    public function scopeFilterByDuration($query, $duration)
    {
        return $query->when($duration, function ($query) use ($duration) {
            return $query->where('duration', $duration);
        });
    }
}
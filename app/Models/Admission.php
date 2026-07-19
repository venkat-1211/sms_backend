<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasAudit;
use App\Traits\BinaryUuid;
use App\Models\Concerns\HasBinaryUuid;

class Admission extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid, HasAudit;

    protected $fillable = [
        'student_id',
        'course_id',
        'admission_date',
        'total_fee',
        'amount_paid',
        'balance_fee',
        'payment_status',
        'status',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'status' => 'integer',
        'uuid' => BinaryUuid::class,
        'total_fee' => 'float',
        'amount_paid' => 'float',
        'balance_fee' => 'float',
    ];

    protected $hidden = [
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function getPaymentStatusLabelAttribute()
    {
        return ucfirst($this->payment_status);
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
        ];
        
        return '<span class="badge bg-' . ($badges[$this->payment_status] ?? 'secondary') . '">' . $this->payment_status_label . '</span>';
    }

    public function getAmountPaidFormattedAttribute()
    {
        return '$' . number_format($this->amount_paid, 2);
    }

    public function getTotalFeeFormattedAttribute()
    {
        return '$' . number_format($this->total_fee, 2);
    }

    public function getBalanceFeeFormattedAttribute()
    {
        return '$' . number_format($this->balance_fee, 2);
    }

    public function getPaymentPercentageAttribute()
    {
        if ($this->total_fee == 0) {
            return 100;
        }
        return min(100, round(($this->amount_paid / $this->total_fee) * 100, 2));
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query, $search) {
            return $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('mobile', 'LIKE', "%{$search}%");
            })->orWhereHas('course', function ($q) use ($search) {
                $q->where('course_name', 'LIKE', "%{$search}%");
            });
        });
    }

    public function scopeFilterByCourse($query, $courseId)
    {
        return $query->when($courseId, function ($query, $courseId) {
            return $query->where('course_id', $courseId);
        });
    }

    public function scopeFilterByStudent($query, $studentId)
    {
        return $query->when($studentId, function ($query, $studentId) {
            return $query->where('student_id', $studentId);
        });
    }

    public function scopeFilterByPaymentStatus($query, $status)
    {
        return $query->when($status, function ($query, $status) {
            return $query->where('payment_status', $status);
        });
    }

    public function scopeFilterByDateRange($query, $from, $to)
    {
        return $query->when($from, function ($query) use ($from) {
            return $query->whereDate('admission_date', '>=', $from);
        })->when($to, function ($query) use ($to) {
            return $query->whereDate('admission_date', '<=', $to);
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

    // Auto-update balance fee and payment status
    public function updatePaymentStatus()
    {
        $this->balance_fee = $this->total_fee - $this->amount_paid;
        
        if ($this->balance_fee <= 0) {
            $this->payment_status = 'paid';
            $this->balance_fee = 0;
        } elseif ($this->amount_paid > 0 && $this->balance_fee > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }
        
        $this->save();
        
        return $this;
    }
}
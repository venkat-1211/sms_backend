<?php

namespace App\Actions;

use App\Interfaces\AdmissionRepositoryInterface;
use App\Models\Admission;
use Illuminate\Support\Facades\Cache;

class PayAdmissionFeeAction
{
    protected AdmissionRepositoryInterface $repository;

    public function __construct(AdmissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, float $amount): Admission
    {
        $admission = $this->repository->find($id);
        
        $admission->amount_paid += $amount;
        $admission->balance_fee = $admission->total_fee - $admission->amount_paid;
        
        if ($admission->balance_fee <= 0) {
            $admission->payment_status = 'paid';
            $admission->balance_fee = 0;
        } elseif ($admission->amount_paid > 0) {
            $admission->payment_status = 'partial';
        }
        
        $admission->save();
        
        Cache::forget("admission:{$id}");
        Cache::forget('admissions:*');
        Cache::forget('dashboard:stats');
        
        return $admission;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'target_days',
        'implementation_period',
        'attendance_period',

        'contractor_name',
        'person_in_charge',

        'principal_name',
        'principal_nip',
        'facilities_head_name',
        'facilities_head_nip',
        'treasurer_name',
        'treasurer_nip',
        'admin_head_name',
        'admin_head_nip',
        'budget_date',
        'invitation_date',
        'invitation_number',
        'contractor_date',
        'contractor_number',
        'work_order_date',
        'work_order_number',
        'handover_report_date',
        'handover_report_number',
        'payment_report_date',
        'payment_report_number',
        'receipt_date',
        'receipt_number',
        'contractor_address',
        'receipt_receiver',
        'tax_vat',
        'tax_pph21',
        'tax_pph22',
        'tax_pph23',
        'tax_pph_final',
        'kop_school',
        'kop_contractor'
    ];

    public function items()
    {
        return $this->hasMany(ProjectItem::class);
    }

    public function images()
    {
        return $this->hasMany(ProjectImage::class);
    }

    public function schedules()
    {
        return $this->hasMany(ProjectSchedule::class);
    }


    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'project_workers', 'project_id', 'worker_id');
    }

    public function projectWorkers()
    {
        return $this->hasMany(ProjectWorker::class);
    }
}
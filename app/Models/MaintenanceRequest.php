<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $table = 'maintenance_requests';

    protected $fillable = [
        'ticket_no',
        // Step 1: Driver Request
        'doc_date',
        'doc_month',
        'doc_year',
        'driver_id',
        'driver_name',
        'car_id',
        'license_plate',
        'brand',
        'model',
        'mileage',
        'category',
        'description',
        'issues',
        'driver_signature',
        'signature_image',
        'attachment_url',
        'signed_document_url',
        'urgency',
        // Step 2: Supervisor Verification
        'supervisor_id',
        'supervisor_name',
        'supervisor_notes',
        'supervisor_verified_at',
        'supervisor_signature',
        'supervisor_signature_image',
        'approved_items',
        'rejected_items',
        // Step 3: Quotation Submission (Mechanic/Garage)
        'garage_to',
        'garage_project',
        'quotation_no',
        'quotation_date',
        'garage_name',
        'garage_manager',
        'mechanic_name',
        'quotation_items',
        'subtotal',
        'vat',
        'total_cost',
        'thai_baht_text',
        'estimated_days',
        'parts_cost',
        'labor_cost',
        'quotation_doc_url',
        // Step 4: Director Approval
        'director_opinion', // approved / rejected
        'budget_type', // government_budget / revenue_budget
        'revenue_budget_source',
        'director_name',
        'director_signed_at',
        'director_signature',
        'director_signature_image',
        'director_remarks',
        // Step 5: Completion & Billing
        'receiver_name',
        'completed_at',
        'archive_no',
        'archive_date',
        'receipt_doc_url',
        'completion_notes',
        'photos',
        'status' // pending_supervisor, pending_quotation, pending_director, in_progress, completed, rejected
    ];

    protected $casts = [
        'issues' => 'array',
        'approved_items' => 'array',
        'rejected_items' => 'array',
        'quotation_items' => 'array',
        'photos' => 'array',
        'subtotal' => 'decimal:2',
        'vat' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'supervisor_verified_at' => 'datetime',
        'director_signed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}

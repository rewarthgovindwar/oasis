<?php

namespace Modules\Fees\Entities;

use App\SmStudent;
use App\Scopes\AcademicSchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FmFeesInvoice extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'full_name' => 'string',
    ];

    protected $fillable = [];

    // Scope to eager load the sums
    public function scopeWithInvoiceDetailsSums($query): void
    {
        $query->with(['invoiceDetails' => function ($query): void {
            $query->selectRaw('fees_invoice_id, sum(amount) as total_amount, sum(weaver) as total_weaver, sum(fine) as total_fine, sum(paid_amount) as total_paid_amount, sum(sub_total) as total_sub_total')
                ->groupBy('fees_invoice_id');
        }]);
    }

    public function studentInfo()
    {
        return $this->belongsTo(SmStudent::class, 'student_id', 'id');
    }

    public function invoiceDetails()
    {
        return $this->hasMany(FmFeesInvoiceChield::class, 'fees_invoice_id');
    }

    // Using the pre-loaded sums for efficiency
    public function getTamountAttribute()
    {
        return $this->invoiceDetails()->sum('amount');
    }

    public function getTweaverAttribute()
    {
        return $this->invoiceDetails()->sum('weaver');
    }

    public function getTfineAttribute()
    {
        return $this->invoiceDetails()->sum('fine');
    }

    public function getTpaidamountAttribute()
    {
        return $this->invoiceDetails()->sum('paid_amount');
    }

    public function getTsubtotalAttribute()
    {
        return $this->invoiceDetails()->sum('sub_total');
    }

    public function recordDetail()
    {
        return $this->belongsTo(\App\Models\StudentRecord::class, 'record_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AcademicSchoolScope);
    }

    protected static function newFactory()
    {
        return \Modules\Fees\Database\factories\FmFeesInvoiceFactory::new();
    }
}

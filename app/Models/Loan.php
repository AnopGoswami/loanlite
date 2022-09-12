<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory,SoftDeletes;

    /**
    * Loan term constants
    */
    public const MIN_TERM=2;
    public const MAX_TERM=30;

    /**
    * Loan amount constants
    */
    public const MIN_AMOUNT=100;
    public const MAX_AMOUNT=1000000;

    /**
    * Loan status constants
    */
    public const STATUS_PENDING=0; 
    public const STATUS_APPROVED=1;
    public const STATUS_DECLINED=2;
    public const STATUS_PAID=3;

    /**
    * @var array of loan status
    */
    public const STATUS = [
        self::STATUS_PENDING  => 'Pending',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_DECLINED => 'Declined',
        self::STATUS_PAID     => 'Paid',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'amount',
        'term',
        'status',
        'approved_at',
        'paid_at',
    ];

    /**
    * Loan relation with installment table
    */
    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    /**
    * Loan relation with payments table
    */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

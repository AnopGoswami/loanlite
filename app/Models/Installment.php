<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installment extends Model
{
    use HasFactory,SoftDeletes;

    /**
    * Installment status constants
    */
    public const STATUS_PENDING=0; 
    public const STATUS_PAID=1;

    /**
    * @var array of installment status
    */
    public const STATUS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_PAID    => 'Paid',
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'loan_id',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'created_at',
    ];
}

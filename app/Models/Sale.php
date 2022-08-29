<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ["id", "product_id", "sales_person_id", "customer_id", "date"];

    public $timestamps = false;

    protected $casts = [
        'date' => 'date:d/m/Y',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'id', 'sales_person_id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
}

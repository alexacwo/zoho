<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'date',
		'cost_amount',
		'price_amount'
    ];
	
	/**
     * Get the device that has this sale
     */
    public function device()
    {
        return $this->belongsTo('App\Device', 'device_serial', 'serial');
    }
}

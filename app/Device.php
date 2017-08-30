<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sales_rep',
        'fb_client_id',
        'make',
        'model',
        'serial',
        'price_total',
        'city',
        'state',
		'base',
		'tax',
		'mono',
		'color',
		'mono_color',
		'color2',
		'color3',
		'mono_included',
		'color_included',
		'mono_color_included',
		'color2_included',
		'color3_included'
    ];	
	
	/**
     * Get the sale associated with the sale.
     */
    public function sale()
    {
        return $this->hasOne('App\Sale', 'device_serial', 'serial');
    }	
}

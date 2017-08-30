<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'fb_client_id',
		'routing_no',
		'bank_account',
		'add_ach'
    ];
}

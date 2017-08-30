<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AchLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'fb_client_id',
		'filename'
    ];
}

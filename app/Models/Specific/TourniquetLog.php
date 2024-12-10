<?php

namespace App\Models\Specific;

use App\Models\General\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TourniquetLog extends Model {

    protected $table = 'tourniquet_logs';

    public const UPDATED_AT = null;

    protected $fillable = [
        'url',
        'data',
        'ip',
    ];

	protected $casts = [
		'data' => 'json',
	];

    /// *** Relations *** ///



}

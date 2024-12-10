<?php

namespace App\Models\Specific;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model {

    protected $table = 'resumes';
    
    protected $fillable = [
        'name',
        'phone',
        'vacancy_id',
        'file',
        'ip'
    ];
    
    public function vacancy() {
        return $this->belongsTo(Vacancy::class, 'vacancy_id');
    }

}

<?php

namespace App\Models\Helpers;

use Illuminate\Support\Facades\Log;

class AfishaFilter
{
    public $q = '';
    public $city = null;
    public $cast = null;
    public $ids = null;
    public $time = null;
    public $venue = null;
    public $genre = null;

    public function __construct($string = null) {
        if($string) {
            $filter = json_decode($string);
            if($filter) {
                if(isset($filter->q)) {
                    $this->q = $filter->q;
                }
                if(isset($filter->time)) {
                    $this->time = $filter->time;
                }
                if(isset($filter->venue)) {
                    $this->venue = $filter->venue;
                }
                if(isset($filter->genre)) {
                    $this->genre = $filter->genre;
                }
                if(isset($filter->cast)) {
                    $this->cast = $filter->cast;
                }
                if(isset($filter->ids)) {
                    $this->ids = $filter->ids;
                }
                if(isset($filter->city)) {
                    $this->city = $filter->city;
                }
            }
        }

    }
}

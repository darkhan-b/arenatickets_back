<?php

namespace App\Models\General;

use File;
use Illuminate\Database\Eloquent\Model;

class Spacemedia extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'file_name',
        'collection_name',
        'mime_type',
        'folder'
    ];

    protected $table = 'spacemedia';

    private $storage = "storage";

    public function model() {
        return $this->morphTo();
    }

    public function getExtensionAttribute()  {
        return 'webp';
//        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getPath($conversionName = ''): string {
        return public_path($this->getUrl($conversionName));
    }

    public function getUrl($conversionName = ''): string {
        $path = '/storage/';
        if($this->storage == 'public') {
            $path = '/media/';
        }
        if($this->folder) {
            $path .= $this->folder.'/';
        }
        $path .= $this->id.'/';

        if($conversionName != '') {
            $tmp_path = $path.'conversions/'.$conversionName.'.'.$this->getExtensionAttribute();
            if(file_exists(public_path($tmp_path))) {
                return $tmp_path;
            }
        }
        return $path.$this->file_name;
    }


    public function pathForDeletion() {
        if($this->storage == 'public') {
            $path = public_path() . '/media/';
        } else {
            $path = storage_path() . '/app/public/';
        }
        if($this->folder) {
            $path .= $this->folder.'/';
        }
        $path .= $this->id;
        return $path;
    }


    public function isImageValid($type = '') {
        $url = $this->getPath($type);
        return (is_file($url) && (getimagesize($url) != false || $this->mime_type == 'image/svg+xml'));
    }


    public function delete()
    {
        $path = $this->pathForDeletion();
        File::deleteDirectory($path);
        return parent::delete();
    }


    public function deleteConversions() {
        $path = $this->pathForDeletion().'/conversions';
        File::deleteDirectory($path);
    }

}

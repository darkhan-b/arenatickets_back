<?php

namespace App\Traits;

use File;
use App\Models\General\Spacemedia;
use Intervention\Image\Facades\Image;

trait AnimatedMedia {


    private $storage = "storage";
//    private $storage = "public";


    public function image($type = 'main', $collection = 'default', $classes = null) {
        if($type == 'thumb') {
            $classes = ' class="teaser-preview"';
        }

        $default_photo = $this->default_image ? $this->default_image : "nophoto.png";
        $media = $this->getFirstMedia($collection);

        if(!$media) {
            return '<img '.$classes.' src=/images/'.$default_photo.' />';
        }
        $extension = $media->getExtensionAttribute();
        if($extension == 'gif') {
            return '<img '.$classes.' src="'.$media->getUrl().'" />';
        }
        if(in_array($extension,['mp4','webm'])) {
            if($type == 'main' || !$type) {
                $classes .= ' autoplay playsinline loop muted';
            }
            return '<video '.$classes.' src="'.$media->getUrl().'" />';
        }
        if($type) {
            if($media->isImageValid($type)) {
                return '<img '.$classes.' src="'.$media->getUrl($type).'" />';
            }
        }
        if(!$type) {
            if($media->isImageValid()) {
                return '<img '.$classes.' src="'.$media->getUrl().'" />';
            }
        }

        return '<img '.$classes.' src=/images/'.$default_photo.' />';
    }


    public function imageSrc($type = 'main', $collection = 'default') {
        $default_photo = $this->default_image ? $this->default_image : "nophoto.png";
        $media = $this->getFirstMedia($collection);
        if($media && $media->isImageValid($type)) {
            return $media->getUrl($type);
        }
        return null;
//        return "/images/".$default_photo;
    }


    public function imagePrimarySrc($type = 'main', $original = false) {
        if(!$this->primary_media_id || !$this->primary_media_ext) {
            return null;
        }
        $add = '/storage';
        if($this->storage == 'public') {
            $add = '/media';
        }
        if($original) {
            $media = Spacemedia::find($this->primary_media_id);
            return $add.'/'.$this->media_dir.'/'.$this->primary_media_id.'/'.$media->file_name;
        }
        if($this->primary_media_ext == 'svg' || $original) {
            return $add.'/'.$this->media_dir.'/'.$this->primary_media_id.'/'.$type.'.'.$this->primary_media_ext;
        }
        return $add.'/'.$this->media_dir.'/'.$this->primary_media_id.'/conversions/'.$type.'.'.$this->primary_media_ext;
    }


    public function getMedia($collection = null) {
        $r = $this->morphMany(Spacemedia::class,'model')->orderBy('sort_order','desc');
        if(!$collection) {
            return $r->get();
        }
        return $r->where('collection_name',$collection)->get();
    }


    public function saveImage($request, $delete = true) {
        if($request->deletefile) {
            $this->clearMediaCollection('default');
        }
        if($request->image) {
            if($delete) {
                $this->clearMediaCollection('default');
            }
            if(is_array($request->image)) {
                if(!$this->image_limit || $this->image_limit >= (count($request->image) + $this->media()->count())) {
                    foreach ($request->image as $im) {
                        $this->addMedia($im, 'default');
                    }
                }
            } else {
                return $this->addMedia($request->image, 'default');
            }
        }
//        $fields = ['wallpaper', 'banner', 'mobile'];
        $fields = ['wallpaper', 'banner'];
        foreach($fields as $f) {
            if($request->{$f}) {
                if($delete) $this->clearMediaCollection($f);
                foreach($request->{$f} as $im) {
                    $this->addMedia($im,$f);
                }
            }
        }
        return null;
    }


    public function addMedia($im, $collection_name = 'default', $extension = null, $mime_type = null) {
//        $imageName = $this->id.'.'.($extension ? $extension : $im->getClientOriginalExtension());
        $imageName = $this->id.'.'.($extension ? $extension : $im->extension());

        $mediaObject = $this->media()->create([
            'collection_name' => $collection_name,
            'file_name' => $imageName,
            'mime_type' => $mime_type ? $mime_type : $im->getMimeType(),
            'folder' => $this->media_dir
        ]);

        $unmodified_path = $this->getUnmodifiedPath($mediaObject);
        if(is_string($im)) {
            File::isDirectory($unmodified_path) or File::makeDirectory($unmodified_path, 0775, true);
            File::copy($im, $unmodified_path.$imageName);
        } else {
            $im->move($unmodified_path, $imageName);
        }
        $ext = $this->applyConversions($mediaObject);
        if($this->store_primary_image && $collection_name === 'wallpaper') {
            $this->update([
                'primary_media_id' => $mediaObject->id,
                'primary_media_ext' => $ext ?: $mediaObject->getExtensionAttribute()
            ]);
        }
        return $mediaObject;
    }


    public function  getUnmodifiedPath($mediaObject) {
        if($this->storage == 'public') {
            return public_path('media/'.$this->media_dir.'/'.$mediaObject->id.'/');
        }
        return storage_path('app/public/'.$this->media_dir.'/'.$mediaObject->id.'/');

    }


    public function applyConversions($mediaObject) {
        $unmodified_path = $this->getUnmodifiedPath($mediaObject);
        $collection_name = $mediaObject->collection_name;
        $imageName = $mediaObject->file_name;
        $info = new \SplFileInfo($imageName);
//        $extension = $info->getExtension();
        $extension = 'webp';

        if(!File::exists($unmodified_path . $imageName)) {
            File::isDirectory($unmodified_path) or File::makeDirectory($unmodified_path, 0775, true);
            File::copy(storage_path('app/public/'.$this->media_dir.'/'.$mediaObject->id.'/'.$mediaObject->file_name), $unmodified_path . $imageName);
        }

        if(in_array($mediaObject->mime_type,['image/png','image/jpeg','image/jpg'])) {
            foreach($this->conversions as $conversion_name => $params) {
                if(in_array($collection_name,$params['collections'])) {
                    File::isDirectory($unmodified_path.'conversions') or File::makeDirectory($unmodified_path.'conversions', 0775, true);
                    File::copy($unmodified_path . $imageName, $unmodified_path . 'conversions/'.$conversion_name.'.'.$extension);
                    $image = Image::make(sprintf($unmodified_path.'/conversions/%s', $conversion_name.'.'.$extension));
                    if($params['type'] == 'fit') {
                        $image->fit($params['width'], $params['height'])->save(null, null, 'webp');
                    }
                    if($params['type'] == 'resize') {
                        $image->resize($params['width'], $params['height'], function ($constraint) {
                            $constraint->aspectRatio();
                        })->save(null, null, 'webp');
                    }
                }
            }
            return 'webp';
        }
        return null;
    }


    public function clearMediaCollection($collection_name = 'default') {
        $m = $this->media()->where('collection_name',$collection_name)->get();
        if($this->store_primary_image) {
            $this->update([
                'primary_media_id' => null,
                'primary_media_ext' => null
            ]);
        }
        foreach($m as $unit) {
            $unit->delete();
        }
    }


    public function media() {
        return $this->morphMany(Spacemedia::class,'model')->orderBy('sort_order','desc');
    }


    public function getAllConversionsUrls($collection_name = 'default') {
        $media = $this->media()->where('collection_name',$collection_name)->get();
        $arr = [];
        foreach($media as $m) {
            $conversions = [];
            foreach($this->conversions as $conversion_name => $params) {
                $conversions[$conversion_name] = env('APP_URL').$m->getUrl($conversion_name);
            }
            $arr[] = $conversions;
        }
        return $arr;
    }


    private function getFirstMedia($collection = 'default') {
        return $this->media()->where('collection_name',$collection)->first();
    }

    public static function boot() {
        parent::boot();
        static::deleting(function ($model) {
            $media = $model->media;
            foreach($media as $m) {
                $m->delete();
            }
        });
    }

    public function regenerateConversion() {
        $media = $this->media;
        foreach($media as $m) {
            $m->deleteConversions();
            $m->folder = $this->media_dir;
            $m->save();
            $this->applyConversions($m);
        }
    }

    public static function regenerateAllConversions() {
        $class_name = get_called_class();
        $all = $class_name::all();
        foreach($all as $unit) {
            $unit->regenerateConversion();
        }
    }


    public static function recurseCopy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurseCopy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    public function replicateImages($new_obj) {
        $this->relations = [];
        $this->load('media');
        foreach($this->getRelations() as $relationName => $values) {
            foreach($values as $item) {
                $old_id = $item->id;
                unset($item->id);
                $new_item = $new_obj->{$relationName}()->create($item->toArray());
                $item->id = $old_id;
                self::recurseCopy($this->getUnmodifiedPath($item),$this->getUnmodifiedPath($new_item));
            }
        }
        return $new_obj;
    }

}

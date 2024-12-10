<?php

namespace App\Models\Specific;

use App\Traits\ActiveScopeTrait;
use App\Traits\ClientTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Crypto\Rsa\KeyPair;

class APIPartner extends Model {

    use LogsActivity, ActiveScopeTrait, ClientTrait;

    protected $table = 'api_partners';

    protected $fillable = [
        'title',
        'active',
    ];

    protected $hidden = ['token', 'private_key', 'public_key'];

    protected $casts = [
        'active' => 'boolean',
    ];

    /// *** Logging *** ///

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /// *** Relations *** ///

    public function shows() {
        return $this->belongsToMany(Show::class, 'partner_shows', 'partner_id', 'show_id');
    }

    /// *** Custom *** ///

    public function hasAccessToShow($id) {
        return $this->shows()->where('id', $id)->count() > 0;
    }

    public static function customDetails($id) {
        $obj = self::find($id);
        $obj->makeVisible('token', 'public_key');
        return $obj;
    }

    public static function customCreate($request) {
        [$privateKey, $publicKey] = (new KeyPair())->generate();
        $data = $request->all();
        $obj = self::create($data);
        $obj->token = Str::uuid();
        $obj->private_key = $privateKey;
        $obj->public_key = $publicKey;
        $obj->save();
        $obj->shows()->sync($request->shows);
        return $obj;
    }

    public function customUpdate($request) {
        $data = $request->all();
        $this->update($data);
        if(!$this->public_key) {
            [$privateKey, $publicKey] = (new KeyPair())->generate();
            $this->private_key = $privateKey;
            $this->public_key = $publicKey;
        }
        $this->save();
        $this->shows()->sync($request->shows);
        return $this;
    }
}

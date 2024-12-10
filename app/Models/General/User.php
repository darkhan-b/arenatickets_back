<?php

namespace App\Models\General;

use App\Models\Specific\Order;
use App\Models\Specific\Show;
use App\Notifications\PasswordReset;
use App\Traits\ClientTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens, ClientTrait;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'phone',
        'client_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = ['fullName'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /// *** Attributes *** ///

    public function getFullNameAttribute() {
        $str = $this->name;
        if($this->surname) {
            if($str) $str .= ' ';
            $str .= $this->surname;
        }
        return $str;
    }

    public function getPermissionsListAttribute() {
        $permissions = $this->getAllPermissions()->toArray();
        $permissions = array_column($permissions, 'name');
        return $permissions;
    }

    /// *** Relations *** ///

    public function orders() {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function shows() {
        return $this->hasMany(Show::class, 'organizer_id');
    }

    public static function customCreate($request) {
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $obj = self::create($data);
        $obj->syncRolesManually($request->roles);
        return $obj;
    }

    public function customUpdate($request) {
        $data = $request->all();
        if(isset($data['password']) && $data['password']) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $this->update($data);
        $this->syncRolesManually($request->roles);
        return $this;
    }

    public function syncRolesManually($roles = []) {
        if($roles === null) return;
        DB::table('model_has_roles')->where('model_id', $this->id)->delete();
        foreach($roles as $roleId) {
            DB::table('model_has_roles')->insert([
                'role_id'    => $roleId,
                'model_type' => User::class,
                'model_id'   => $this->id
            ]);
        }
    }

    public function isOrganizerForShow($showId = null, $orderId = null): bool {
        if(!$showId && !$orderId) return false;
        if($this->can('sell_all_invitations')) return true;
        $show = null;
        if($showId) {
            $show = Show::find($showId);
        } else if($orderId) {
            $order = Order::find($orderId);
            if($order) $show = $order->show;
        }
        return $show && $show->organizer_id == $this->id;
    }

    public function sendPasswordResetNotification($token) {
        $this->notify(new PasswordReset($token));
    }

    public static function additionalSearchQuery($query, $search) {
        if(clientId()) $query->where('client_id', clientId());
        if(isset($search['roles']) && $search['roles']) {
            $query->whereHas('roles', function($q) use($search){
                $q->where('id', $search['roles']);
            });
        }
        return $query;
    }

    public static function getSelectRoles() {
        $q = Role::query();
        if(clientId()) {
            $q->where('name', '<>', 'superadmin');
        }
        return $q->get();
    }

    public function createStandardToken() {
        return $this->createToken('admin', ['*'], (new \DateTime())->modify('+ 30 days'))->plainTextToken;
    }

    public function reassignOrders() {
        Order::where('email', $this->email)
            ->whereNull('user_id')
            ->update(['user_id' => $this->id]);
    }


}

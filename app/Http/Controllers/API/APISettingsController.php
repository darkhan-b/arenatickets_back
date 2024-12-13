<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\RefundApplicationSubmitted;
use App\Models\General\MenuItem;
use App\Models\General\Meta;
use App\Models\General\Setting;
use App\Models\General\Subscription;
use App\Models\General\Translation;
use App\Models\Specific\Category;
use App\Models\Specific\City;
use App\Models\Specific\Order;
use App\Models\Specific\RefundApplication;
use App\Models\Specific\Show;
use App\Models\Specific\StoryCategory;
use App\Traits\APIResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class APISettingsController extends Controller {

    use APIResponseTrait;

    public function getSettings(Request $request) {
        $user = auth('sanctum')->user();
        $user?->append('permissionsList');
        $meta = Meta::all()->keyBy('url')->toArray();
        return $this->apiSuccess([
            'locale'        => app()->getLocale(),
//            'cityGuess'     => City::guessCity($request),
            'settings'      => Setting::getSettings(),
            'categories'    => Category::active()->sorted()->get(),
            'cities'        => City::getCitiesList(),
            'translations'  => Translation::getTranslations(),
            'paySystems'    => Setting::getPaySystems(),
            'menu'          => MenuItem::getMenu(),
            'meta'          => $meta,
            'user'          => $user,
        ]);
    }

    public function getWidgetSettings(Request $request) {
        $user = auth('sanctum')->user();
        $showRights = [];
        if($user) {
            $user->append('permissionsList');
            if(($request->show_id || $request->order_id) && $user->isOrganizerForShow($request->show_id, $request->order_id)) {
                $showRights[] = 'invitation';
            }
            if(($request->show_id || $request->order_id) && $user->isOrganizerForShow($request->show_id, $request->order_id)) {
                $showRights[] = 'forum';
            }
            if(in_array('kassa', $user->permissionsList)) {
                $showRights[] = 'kassa';
            }
        }
        $config = [
            'order_time_limit' => ORDER_TIME_LIMIT
        ];
        return $this->apiSuccess([
            'translations'  => Translation::getTranslations(),
            'settings'      => Setting::getSettings(),
            'user'          => $user,
            'config'        => $config,
            'showRights'    => $showRights
        ]);
    }

    public function subscribe(Request $request) {
        $request->validate([
            'email' => 'required|email',
        ]);
        Subscription::updateOrCreate([
            'email'     => $request->email
        ], [
            'email'     => $request->email,
            'user_id'   => Auth::id(),
            'ip'        => $request->ip(),
        ]);
        return $this->apiSuccess([
            'message' => __('you_are_subscribed_successfully')
        ]);
    }

    public function search(Request $request) {
        $shows = Show::showable()
            ->whereRaw('LOWER(title->"$.'.app()->getLocale().'") like ?', ['%'.mb_strtolower($request->q).'%'])
            ->paginate(6);
        return $this->apiSuccess($shows);
    }

    public function refundApplication(Request $request) {
        $request->validate([
            'email'     => 'required|email',
            'order_id'  => 'required|numeric',
            'name'      => 'required',
            'phone'     => 'required',
        ]);
        $order = Order::find($request->order_id);
        if(!$order || $order->email != $request->email) {
            return $this->apiError(404, __('order_not_found'));
        }
        if(!$order->is_refundable) {
            return $this->apiError(400, __('order_not_refundable'));
        }
        $timetable = $order->timetable;
        if(!$timetable->availableForRefund) {
            return $this->apiError(400, __('less_then_x_hours_till_show', ['hours' => REFUND_APPLICATION_LIMIT]));
        }
        if(RefundApplication::where('order_id', $order->id)->count() > 0) {
            return $this->apiError(400, __('refund_application_for_this_order_already_submitted'));
        }
        $application = RefundApplication::create($request->only('order_id', 'name', 'phone', 'reason', 'email'));
        Mail::send(new RefundApplicationSubmitted($application));
        return $this->apiSuccess($application);
    }

    public function stories() {
        $stories = StoryCategory::active()->sorted()->with('slides')->get();
        return $this->apiSuccess($stories);
    }


}

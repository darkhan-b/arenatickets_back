<?php

namespace App\Http\Controllers\API;

use App\Collections\OrganizerTimetableCollection;
use App\Http\Controllers\Admin\EloquentController;
use App\Http\Controllers\Controller;
use App\Mail\OrganizerAddedEvent;
use App\Mail\RefundApplicationSubmitted;
use App\Models\Reports\ChartProcessor;
use App\Models\Reports\ExcelGenerator;
use App\Models\Reports\SalesListReport;
use App\Models\Specific\Show;
use App\Models\Specific\Timetable;
use App\Models\Specific\Venue;
use App\Models\Specific\VenueScheme;
use App\Resources\OrganizerOrdersResource;
use App\Traits\APIResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class APIOrganizerController extends Controller {

    use APIResponseTrait;

    public function getMyOrganizerEvents(Request $request) {
        $timetables = Timetable::userHasAccessToData($request->user())
            ->with('show')
            ->paginate(10);
        return $this->apiSuccess([
            'timetables' => new OrganizerTimetableCollection($timetables)
        ]);
    }

    public function getMyOrganizerShows(Request $request) {
        $q = Show::userHasAccessToData($request->user());
        if($request->q) {
            $q->whereRaw('LOWER(title->"$.'.app()->getLocale().'") like ?', ['%'.mb_strtolower($request->q).'%']);
        }
        if($request->type === 'future') $q->future();
        if($request->type === 'passed') $q->passed();
        return $this->apiSuccess([
            'shows' => $q->paginate(10)
        ]);
    }

    public function getOrganizerEventData($id, Request $request) {
        $user = $request->user();
        $timetable = Timetable::userHasAccessToData($user)->findOrFail($id);
        if(!$timetable->show) abort(404);
//        if($user->hasRole('manager')) {
//            if($timetable->show->managers()->where('id', $user->id)->count() < 1) abort(404);
//        } else {
//            if($timetable->show->organizer_id != $user->id) abort(404);
//        }
        $timetable->append('dateString',
            'totalTickets',
            'soldTickets',
            'invitationSales',
            'returnedTickets',
            'availableTickets',
            'revenue');
        if($request->getOrders) {
            $orders = $timetable->orders()
                ->with('orderItems')
                ->with('orderItems.pricegroup')
                ->with('orderItems.section')
                ->get();
            $orders = OrganizerOrdersResource::collection($orders);
        } else {
            $orders = [];
        }
        return $this->apiSuccess([
            'timetable' => $timetable,
            'orders'    => $orders
        ]);
    }

    public function getOrganizerShowData($id, Request $request) {
        $user = $request->user();
        $show = Show::userHasAccessToData($user)
            ->with('timetables')
            ->findOrFail($id);
        return $this->apiSuccess([
            'show' => $show,
        ]);
    }

    public function generateExcelReport(Request $request) {
        $user = auth('sanctum')->user();
        $data = $request->only(
            'show_ids',
            'timetable_ids',
            'venue_ids',
            'client_ids',
            'category_ids',
            'date_from',
            'date_to',
            'report_type'
        );
        $data['organizer_ids'] = [$user->id];
        if(isset($data['date_from']) && $data['date_from']) $data['date_from'] = mb_substr($data['date_from'], 0, 10);
        if(isset($data['date_to']) && $data['date_to']) $data['date_to'] = mb_substr($data['date_to'], 0, 10);
        $data['is_organizer'] = true;
        $report = new SalesListReport($data);
        $report->generate();
        if($report->hasData()) return $report->toExcel();
        return null;
    }

    public function organizerAutocomplete($model, $field, Request $request) {
        $user = auth('sanctum')->user();
        if(!in_array($model, ['timetable', 'show', 'venue', 'category'])) return json_encode([]);
        $controller = new EloquentController();
        return response()->json(json_decode($controller->eloquentAutocomplete($model, $request, $field, $user->id)));
    }

    public function addShow(Request $request) {
        $request->validate([
            'title.ru'          => ['required'],
            'category_id'       => ['required'],
            'venue_id'          => ['nullable'],
            'internal_comment'  => ['nullable'],
            'duration'          => ['nullable', 'numeric'],
            'age'               => ['nullable'],
            'language'          => ['required', 'in:russian,kazakh,english'],
            'video_url'         => ['nullable'],
            'afisha'            => ['array', 'min:1'],
            'afisha.*.date'     => ['required', 'date'],
            'banner.*'          => ['max:2000'],
            'wallpaper.*'       => ['max:2000'],
            'mobile.*'          => ['max:2000'],
            'image'             => ['array', 'nullable'],
            'image.*'           => ['max:2000'],
        ]);
        $data = $request->only('title', 'description', 'category_id', 'venue_id', 'duration', 'age', 'language', 'video_url', 'internal_comment');
        $organizer = $request->user();
        $data['organizer_id'] = $organizer->id;
        $data['organizer_add_status'] = 'new';
        $data['active'] = 0;
        $show = Show::create($data);
        $show->categories()->sync($data['category_id'] ?? []);
        $show->saveImage($request, false);
        $venue_scheme = isset($data['venue_id']) ? VenueScheme::where(['venue_id' => $data['venue_id']])->first() : null;
        foreach($request->afisha as $afisha) {
            Timetable::create([
                'show_id'           => $show->id,
                'date'              => $afisha['date'],
                'venue_id'          => $data['venue_id'] ?? null,
                'venue_scheme_id'   => $venue_scheme->id ?? null
            ]);
        }
        Mail::send(new OrganizerAddedEvent($show, $organizer));
        return $this->apiSuccess($show);
    }



    public function getMyCharts(Request $request) {
        $from = $request->from ?? date('Y-m-d', strtotime('-30 days'));
        $to = $request->to ?? date('Y-m-d');
        $res = [];
        $processor = new ChartProcessor($request->user(), $from, $to, $request->timetableIds, $request->paySystems);
        $res['byDays'] = $processor->getSalesByDaysData();
        $res['topEvents'] = $processor->getTopEventsData();
        $res['byCategories'] = $processor->getByCategoriesData();
        $res['byPayTypes'] = $processor->getByPayTypesData();
        $res['timetables'] = $processor->getTimetables();
        return $this->apiSuccess($res);
    }

}

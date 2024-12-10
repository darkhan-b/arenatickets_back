<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\GoogleAPI;
use App\Models\General\Page;
use App\Models\Helpers\AccessHelper;
use App\Models\Specific\Banner;
use App\Models\Specific\Carousel;
use App\Models\Specific\Category;
use App\Models\Specific\City;
use App\Models\Specific\Show;
use App\Models\Specific\Slide;
use App\Models\Specific\Timetable;
use App\Models\Specific\Venue;
use App\Resources\HomeSliderResource;
use App\Traits\APIResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class APIEventController extends Controller {

    use APIResponseTrait;

    public function home(Request $request) {
        $query = Show::showable()->sorted()->where('recommended', 1);
        $city = $request->city && $request->city != 'null' && $request->city != 'Kazakhstan' ? $request->city : null;
        if($city) {
            $query->whereHas('cities', function($query) use($city) {
                $query->where('id', $city);
            });
//            $query->whereHas('venue', function($query) use($city) {
//                $query->where('city_id', $city);
//            });
        }
        $shows = $query->paginate(6);
        $slider = Slide::active()
            ->sorted()
            ->with('show')
            ->with('show.media')
            ->get();
        return $this->apiSuccess([
            'recommended'       => $shows,
            'slider'            => $slider,
            'by_categories'     => Show::collectByCategories($city),
//            'custom_carousels'  => Carousel::collectForHome(),
//            'banners'           => [
//                'top'               => Banner::active()->where('position','top')->sorted()->get(),
//                'bottom'            => Banner::active()->where('position','bottom')->sorted()->get(),
//            ]
        ]);
    }

    public function popular(Request $request) {
        $query = Show::showable()->sorted()->where('popular', 1);
        if($request->city && $request->city != 'null' && $request->city != 'Kazakhstan') {
            $query->whereHas('cities', function($query) use($request) {
                $query->where('id', $request->city);
            });
//            $query->whereHas('venue', function($query) use($request) {
//                $query->where('city_id', $request->city);
//            });
        }
        if($request->category) {
            $query->where('category_id', $request->category);
        }
        $shows = $query->paginate(6);
        return $this->apiSuccess($shows);
    }

    public function recommended() {
        $shows = Show::showable()
            ->sorted()
            ->where('recommended', 1)
            ->paginate(6);
        return $this->apiSuccess($shows);
    }

    public function category($slug, Request $request) {
        $category = Category::findBySlug($slug);
        if(!$category) abort(404);
        $query = $category->shows()->sorted()->showable();
        if($request->city && $request->city != 'null' && $request->city != 'Kazakhstan') {
            $query->whereHas('cities', function($query) use($request) {
                $query->where('id', $request->city);
            });
//            $query->whereHas('venue', function($query) use($request) {
//                $query->where('city_id', $request->city);
//            });
        }
        $events = $query->orderBy('id','asc')->paginate(12);
        return $this->apiSuccess([
            'events'    => $events,
            'category'  => $category
        ]);
    }

    public function carousel($id) {
        $carousel = Carousel::find($id);
        if(!$carousel || !$carousel->active) abort(404);
        return $this->apiSuccess($carousel->collectData());
    }

    public function city($slug, Request $request) {
        $city = City::findBySlug($slug);
        if(!$city) abort(404);
        $query = Show::showable()->sorted()->whereHas('cities', function($query) use($city) {
            $query->where('id', $city->id);
        });
        $events = $query->paginate(12);
        return $this->apiSuccess([
            'events'    => $events,
            'city'      => $city
        ]);
    }

    public function getVenues(Request $request) {
        if($request->all) {
            return $this->apiSuccess(Venue::active()->select('id','title')->get());
        }
        $q = Venue::active()->with('category');
        if($request->city && $request->city != 'null' && $request->city != 'Kazakhstan') {
            $q->where('city_id', $request->city);
        }
        if($request->q) {
            $q->whereRaw('LOWER(title->"$.ru") like ?', ['%'.mb_strtolower($request->q).'%']);
        }
        $venues = $q->paginate($request->perPage ?? 12);
        return $this->apiSuccess([
            'venues'    => $venues,
        ]);
    }

    public function venue($slug) {
        $venue = Venue::findBySlug($slug);
        $venue->category;
        $venue->city;
        if(!$venue) abort(404);
        $events = $venue->timetables()
            ->future()
            ->visibleTill()
            ->whereHas('show', function ($query) {
                $query->active();
            })
            ->with('show')
            ->orderBy('date','asc')
            ->paginate(6);
        $gallery = $venue->getAllConversionsUrls();
        return $this->apiSuccess([
            'events'    => $events,
            'venue'     => $venue,
            'gallery'   => $gallery
        ]);
    }

    public function timetables(Request $request) {
        $q = Timetable::future()
            ->visibleTill()
            ->whereHas('show', function ($query) {
                $query->active();
            })
            ->with('show')
            ->with('show.venue')
            ->with('show.venue.city')
            ->orderBy('date','asc');
        if($request->city && $request->city != 'null' && $request->city != 'Kazakhstan') {
            $q->whereHas('show', function($query) use($request) {
                $query->whereHas('cities', function($query) use($request) {
                    $query->where('id', $request->city);
                });
            });
        }
        if(isset($request->from) && $request->from) {
            $q->where('date', '>=', $request->from.' 00:00:00');
        }
        if(isset($request->to) && $request->to) {
            $q->where('date', '<=', $request->to.' 23:59:59');
        }
        $timetables = $q->paginate(12);
        $timetables->append('minCost');
        return $this->apiSuccess([
            'timetables' => $timetables,
        ]);
    }

    public function getEvent($id) {
        $event = Show::findBySlugOrFail($id);
        if(!$event->active && !AccessHelper::hasAccessToTestEvents()) abort(404);
        $event->makeVisible('description');
        $event->append('mobileSlide');
        $timetables = $event->timetables()
            ->future()
            ->visibleTill()
            ->with('venue')
            ->with('venue.category')
            ->with('venue.city')
            ->get();
        $timetables->each->append(['minCost','placesLeft']);
        $event->timetables = $timetables;
        $event->categories;
        $gallery = $event->getAllConversionsUrls();
        return $this->apiSuccess([
            'event'    => $event,
            'gallery'  => $gallery
        ]);
    }

    public function getTimetable($id) {
        $timetable = Timetable::with([
            'show',
            'show.category',
            'pricegroups',
            'venue',
            'scheme',
            'scheme.sections'
        ])->where('uuid', $id)->first();
        if(!$timetable) abort(404);
        if(in_array($timetable->id, TIMETABLES_FOR_TESTING)) {
            $timetable->active = 1;
        }
        $timetable->append('salesFinished', 'hasPromocodes');
        $tickets = $timetable->groupedCountTickets();
        return response()->json([
            'timetable' => $timetable,
            'tickets'   => $tickets,
        ]);
    }

    public function getSection($timetable_id, $id) {
        $timetable = Timetable::findOrFail($timetable_id);
        return response()->json($timetable->getTicketsForGroup($id, false));
    }

    public function page($slug) {
        $page = Page::findBySlug($slug);
        if(!$page) abort(404);
        return $this->apiSuccess($page);
    }




}

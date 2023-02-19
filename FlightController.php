<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Station;
use App\Model\Flight;
use App\Model\Setting;
use App\Model\Type;
use Carbon\Carbon;
use DB;

class FlightController extends Controller
{

    protected $settings;

    public function __construct()
    {
        $this->settings = Setting::first();
    }

    public function search(Request $request)
    {
        if(!$request->has(['from', 'to', 'date', 'person'])){
            return redirect('/');
        }

        $data = $request->all();
        if(empty($data['date']) || $data['date'] < Carbon::now()->toDateString()){
            $data['date'] = Carbon::now()->toDateString();
        }
        
        if(empty($data['person'])){
            $data['person'] = 1;
        }

        //$type = Type::where(['eng_name' => date('D', strtotime($data['date']))])->first();
        $dayName = date('l',strtotime($data['date']));

        if(!empty($data['from']) && !empty($data['to'])) {
            $flight = Flight::with(['stations' => function ($query) use ($data) {
                $query->whereIn('station_id', array($data['from'], $data['to']));
                $query->orderByRaw(DB::raw("FIELD(station_id," . $data["from"] . "," . $data['to'] . ")"));
            }, 'price' => function ($query) use ($data) {
                $query->where('stationA_id', '=', $data['from']);
                $query->where('stationB_id', '=', $data['to']);
            }, 'allStationsFlight',
                'tickets' => function($query) use ($data){
                    $query->whereRaw("DATE(date) = '" .$data['date']. "'");
                    $query->where('status','=', 'ok');
            }, 'types' => function($query) use ($dayName){
                    $query->where('eng_name', '=', $dayName);
            }])->where('start_date', '<=', $data['date'])->where('end_date',  '>=', $data['date'])
            ->get();

            $searchStation = Station::whereIn('id', array($data['from'], $data['to']))
                ->orderByRaw(DB::raw("FIELD(id," . $data["from"] . "," . $data["to"] .")"))
                ->limit(2)
                ->get();
        }else{
            $flight = false;
            $searchStation = null;
        }

        return view('search',[
            'flights' => $flight,
            'data' => $data,
            'searchStation' => $searchStation,
            'settings' => $this->settings
        ]);
    }

    public function getStation(Request $request)
    {
        $stations = Station::where('title', 'LIKE', '%'.$request->get('query').'%')->get();
        $array = [];

        foreach ($stations as $st) {
            $array[] = ['value' => $st->id, 'title' => $st->title];
        }

        echo json_encode($array);
        //return \Response::json($array);
    }
    
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function buses()
    {
        return $this->belongsTo('App\Model\Bus');
    }

    public function route()
    {
        return $this->belongsTo('App\Model\Route');
    }

    /**
     * @return mixed
     */
    public function types()
    {
        return $this->belongsToMany(\App\Model\Type::class, 'flight_type', 'flight_id', 'type_id');
    }

    /**
     * @return mixed
     */
    public function stations()
    {
        return $this->belongsToMany('App\Model\Station', 'flight_station', 'flight_id', 'station_id')
            ->withPivot('arrival','departure');
    }

    public function allStationsFlight()
    {
        return $this->belongsToMany('App\Model\Station', 'flight_station', 'flight_id', 'station_id')
            ->withPivot('arrival','departure');
    }


    public function station()
    {
        return $this->hasMany('App\Model\Station','flight_id');
    }

    public function orderStations()
    {
        return $this->belongsToMany('App\Model\Station', 'flight_station', 'flight_id', 'station_id')
            ->withPivot('arrival','departure')
            ->orderBy('departure');
    }

    public function prices()
    {
        return $this->belongsToMany('App\Model\FlightPrice', 'flight_price', 'flight_id','stationA_id')
            ->withPivot('price');
    }
    

    public function price()
    {
        return $this->hasMany('App\Model\FlightPrice','flight_id');
    }

    public function tickets()
    {
        return $this->hasMany('App\Model\Ticket','flight_id');
    }

    public function countTicketDay($data)
    {
        return $this->hasMany('App\Model\Ticket','flight_id')
            ->where('status', '=', 'ok')
            ->whereRaw("DATE(date) = '" . $data . "'")
            ->count();
    }

    public function parent()
    {
        return $this->belongsTo('App\Model\Flight', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('App\Model\Flight', 'parent_id');
    }

    public static function ff()
    {
        return Flight::where(['id' => 1])->first();
    }

    public function driver()
    {
        return $this->belongsTo('App\Model\Driver');
    }


    public function drivers()
    {
        return $this->belongsToMany('App\Model\Driver', 'driver_flight', 'flight_id', 'driver_id')
            ->withPivot('date', 'id');
    }

}

<?php

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('calls', function () {
    $calls = file_get_contents(base_path('calls.json'));

    $collection = collect(json_decode($calls, true)["data"]);

    $logs = $collection->filter(function ($coll) {
        return Str::of($coll["phNumber"])->contains('8065055146') && Carbon::parse($coll["callDayTime"])->isCurrentMonth();
    })->sort(function ($a, $b) {
        return $a["callDate"] <=> $b["callDate"];
    })->groupBy(function ($coll) {
        return Carbon::parse($coll["callDayTime"])->format("d F Y");
    })->map(function ($calls, $date) {
        $first = collect($calls)->sortBy(function ($coll) {
            return Carbon::parse($coll["callDayTime"]);
        })->first();
        return [
            "date" => $date,
            "call_type" => $first["type_name"] == "OUTGOING" ? "Ben called first" : "Sarah called first",
            "time_of_day" => Carbon::parse($first["callDayTime"])->format("H:i A"),
            "is_morning" => Carbon::parse($first["callDayTime"])->format("H") < 12 ? "Yes" : "No",
            "you" =>  Carbon::parse($first["callDayTime"])->format("H") < 12 && $first["type_name"] == "INCOMING" ? "Yes" : "No",
            "me" => Carbon::parse($first["callDayTime"])->format("H") < 12 && $first["type_name"] == "OUTGOING" ? "Yes" : "No",
        ];
    })->values()->toArray();

    $this->table(
        ['Date', 'First Call Type', 'Time of Day', 'Is Morning Call', 'Sarah Called (Morning)', 'Ben Called (Morning)'],
        $logs
    );
})->purpose('Display an inspiring quote');

<?php

namespace App\Http\Controllers;

use App\Models\Recurring;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;

class RecurringController extends Controller
{
    public function index()
    {

        $recurrings =  Recurring::all();
        foreach ($recurrings as $each) {
            $each->transaction->first();
        }
        $respond = [
            'status' => 201,
            'message' =>  "reccurings",
            'data' => $recurrings
        ];
        return $respond;
    }

    public function edit(Request $request, $id)
    {
        $respond = [
            'status' => 201,
            'message' =>  null,
            'data' => null,
        ];
        $recurring = Recurring::find($id);

        if (!isset($recurring)) {
            $respond['message'] = "$id doesn't exist";
            return $respond;
        }
        $last_run = Carbon::createFromDate($recurring->next_run)->sub($recurring->duration,$recurring->schedule);
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'description' => 'string',
            'amount' => 'numeric|gt:0',
            'currency' => 'nullable|in:$,LBP',
            "schedule" => 'nullable|in:days,months,years',
            "duration" => 'numeric|gte:1',
            "end_date" => 'date_format:Y-m-d|after:' . $last_run->toDateString(),
        ]);

        if ($validator->fails()) {
            $respond['message'] = $validator->errors();;
            return $respond;
        }
        $next_run = $last_run->add($request->duration,$request->schedule);
        $recurring->title = $request->title ?? $recurring->title;
        $recurring->description = $request->description ?? $recurring->description;
        $recurring->currency = $request->currency ?? $recurring->currency;
        $recurring->amount = $request->amount ?? $recurring->amount;
        $recurring->schedule = $request->schedule ?? $recurring->schedule;
        $recurring->duration = $request->duration ?? $recurring->duration;
        $recurring->end_date = $request->end_date  ?? $recurring->end_date;
        if(isset($recurring->end_date))  $recurring->next_run =$next_run ;
        $recurring->save();

        $min_now_enddate = Carbon::createFromFormat('Y-m-d', $recurring->end_date)->minimum(Carbon::now());
        if($next_run->minimum($min_now_enddate)){
            $period = CarbonPeriod::create($recurring->next_run, $recurring->duration . ' ' . $recurring->schedule, $min_now_enddate);
            foreach ($period as $date) {
                $transaction = new Transaction;
                $transaction->title = $recurring->title;
                $transaction->description = $recurring->description;
                $transaction->amount = $recurring->amount;
                $transaction->currency = $recurring->currency;
                $transaction->date_time = $date->toDateString();
                $transaction->recurring_id = $recurring->id;
                $transaction->category_id = $recurring->category_id;
                $transaction->save();
                $recurring->next_run = $date->add($recurring->duration,$recurring->schedule);
                $recurring->save();
        }}


        $recurring->transaction;
        $respond['message'] = "recurring $id updated successfully";
        $respond['data'] = $recurring;

        return $respond;
    }

    public function destroy($id)
    {
        $respond = [
            'status' => 201,
            'message' => "recurring $id is not found",
            'data' => null
        ];
        $data = Recurring::find($id);
        if (isset($data)) {
            $data->delete();
            $respond['message'] = "recurring $id deleted successfully";
            $respond['data'] = Recurring::all();
        }
        return $respond;
    }
}

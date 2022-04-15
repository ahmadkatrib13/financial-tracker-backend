<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Recurring;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;
use Carbon\Carbon;



class TransactionController extends Controller
{
    /**
     * Display a listing of the transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $transactions =  Transaction::orderBy("date_time","DESC")->orderBy("updated_at","DESC")->paginate(7);
        foreach ($transactions as $each) {
            $each->category;
            $each->recurring;
        }
        $respond = [
            'status' => 201,
            'message' => 'get all transactions',
            'data' => $transactions
        ];
        return $respond;
    }
    /**
     * Display a transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTransaction($id)
    {
        $respond = [
            'status' => 201,
            'message' => null,
            'data' => null
        ];
        $transaction = Transaction::find($id);
        if (!isset($transaction)) {
            $respond['message'] = "transaction $id not found";
            return $respond;
        }
        $transaction->category;
        $transaction->recurring;
        $respond['data'] = $transaction;
        $respond['message'] = "category $id founded";


        return $respond;
    }

    public function getAllIncomes()
    {
        $transactions = Transaction::orderBy("date_time","DESC")->orderBy("updated_at","DESC")->whereRelation("category", "type", 'income')->paginate(7);
        foreach ($transactions as $each) {
            $each->category;
            $each->recurring;
        }
        $respond = [
            'status' => 201,
            'message' => "get all expense",
            'data' => $transactions
        ];

        return $respond;
    }

    public function getAllExpenses()
    {
        $transactions = Transaction::orderBy("date_time","DESC")->orderBy("updated_at","DESC")->whereRelation("category", "type", 'expense')->paginate(7);
        foreach ($transactions as $each) {
            $each->category;
            $each->recurring;
        }
        $respond = [
            'status' => 201,
            'message' => "get all Expense",
            'data' => $transactions
        ];

        return $respond;
    }


    public function getTotalAmount()
    {
        $income     =   Transaction::whereRelation("category", "type", 'income')->get()->sum('amount');
        $expense    =   Transaction::whereRelation("category", "type", 'expense')->get()->sum('amount');
        $total      =   $income - $expense;

        $respond = [
            'status' => 201,
            'message' =>  "total amount",
            'data' => ["total"=>$total,"income"=>$income,"expense"=>$expense]
        ];

        return $respond;
    }



    public function getTotalIncomes()
    {
        $now = Carbon::now();
        $start_month = Carbon::now()->startOfMonth();
        $end_month = Carbon::now()->endOfMonth();
        $start_year= Carbon::now()->startOfYear();
        $end_year = Carbon::now()->endOfYear();

        $this_day = Transaction::WhereDate('date_time',$now)->whereRelation("category", "type", 'income')->sum('amount');
        $this_month = Transaction::WhereDate('date_time','<=',$end_month)->WhereDate('date_time',">=",$start_month)->whereRelation("category", "type", 'income')->sum('amount');
        $this_year = Transaction::WhereDate('date_time','<=',$end_year)->WhereDate('date_time',">=",$start_year)->whereRelation("category", "type", 'income')->sum('amount');
        $current = Transaction::whereRelation("category", "type", 'income')->sum('amount');


        $respond = [
            'status' => 201,
            'message' =>  "total Income",
            'data' => ["this_day"=>$this_day,"this_month"=>$this_month,"this_year"=>$this_year,"current"=>$current]
        ];

        return $respond;
    }


    public function getTotalExpenses()
    {
        $now = Carbon::now();
        $start_month = Carbon::now()->startOfMonth();
        $end_month = Carbon::now()->endOfMonth();
        $start_year= Carbon::now()->startOfYear();
        $end_year = Carbon::now()->endOfYear();

        $this_day = Transaction::WhereDate('date_time',$now)->whereRelation("category", "type", 'expense')->sum('amount');
        $this_month = Transaction::WhereDate('date_time','<=',$end_month)->WhereDate('date_time',">=",$start_month)->whereRelation("category", "type", 'expense')->sum('amount');
        $this_year = Transaction::WhereDate('date_time','<=',$end_year)->WhereDate('date_time',">=",$start_year)->whereRelation("category", "type", 'expense')->sum('amount');
        $current = Transaction::whereRelation("category", "type", 'expense')->sum('amount');


        $respond = [
            'status' => 201,
            'message' =>  "total Expense",
            'data' => ["this_day"=>$this_day,"this_month"=>$this_month,"this_year"=>$this_year,"current"=>$current]
        ];

        return $respond;
    }



    /**
     * Show the form for creating a new transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $respond = [
            'status' => 201,
            'message' => null,
            'data' => null
        ];

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|gt:0',
            'currency' => 'required|in:$,LBP',
            'category_id' => 'required|integer|min:1',
            'type' => 'required|in:fixed,recurring',
            'status' => 'in:paid,unpaid',
            "schedule" => 'exclude_unless:type,recurring|required_if:type,recurring|in:days,months,years',
            "duration" => 'exclude_unless:type,recurring|required_if:type,recurring|integer|gte:1',
            "start_date" => 'required|date_format:Y-m-d|before_or_equal:' . date('Y-m-d'),
            "end_date" => 'exclude_unless:type,recurring|required_if:type,recurring|date_format:Y-m-d|after:start_date'
        ]);

        //validate request

        if ($validator->fails()) {
            $respond['message'] = $validator->errors();
            return $respond;
        }

        //validate existance of category

        $category = Category::firstWhere('id', $request->category_id);
        if (!isset($category)) {
            $respond['message'] = "category doesn't exist";
            return $respond;
        }
        if ($request->type == 'recurring') {

            $recurring = new Recurring;
            $recurring->title = $request->title;
            $recurring->description = $request->description;
            $recurring->amount = $request->amount;
            $recurring->category_id = $request->category_id;
            $recurring->currency = $request->currency;
            $recurring->schedule = $request->schedule;
            $recurring->duration = $request->duration;
            $recurring->next_run = Carbon::createFromDate($request->start)->add($request->duration,$request->schedule);
            $recurring->start_date = $request->start_date;
            $recurring->end_date = $request->end_date;
            $recurring->save();

            $min_now_enddate = Carbon::createFromFormat('Y-m-d', $recurring->end_date)->minimum(Carbon::now());
            $period = CarbonPeriod::create($recurring->start_date, $recurring->duration . ' ' . $recurring->schedule, $min_now_enddate);
            $transactions = [];
            foreach ($period as $date) {
                $transaction = new Transaction;
                $transaction->title = $recurring->title;
                $transaction->description = $recurring->description;
                $transaction->amount = $recurring->amount;
                $transaction->currency = $recurring->currency;
                $transaction->date_time = $date->toDateString();
                $transaction->recurring_id = $recurring->id;
                $transaction->category_id = $category->id;
                $transaction->save();
                $recurring->next_run = $date->add($request->duration,$request->schedule);
                $recurring->save();
                array_push($transactions, $transaction);
                // return $recurring;
            }

            foreach ($transactions as $transaction) {
                $transaction->recurring;
                $transaction->category;
            }
            $respond['message'] = "recurring transactions added successfully";
            $respond['data'] = $transactions;
            return $respond;
        } else {
            $transaction = new Transaction;
            $transaction->title = $request->title;
            $transaction->description = $request->description;
            $transaction->amount = $request->amount;
            $transaction->currency = $request->currency;
            $transaction->date_time = $request->start_date;
            $transaction->category_id = $category->id;
            $transaction->save();
            $respond['message'] = "fixed transaction added successfully";
            $transaction->category;
            $respond['data'] = $transaction;
            return $respond;
        }
    }

    public function edit(Request $request, $id)
    {

        $respond = [
            'status' => 201,
            'message' => null,
            'data' => null
        ];


        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'amount' => 'nullable|numeric|gt:0',
            'currency' => 'nullable|in:$,LBP',
            'status' => 'nullable|in:paid,unpaid',
            'date_time' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            $respond['message'] = $validator->errors();
            return $respond;
        }

        $transaction = Transaction::find($id);
        if (!isset($transaction)) {
            $respond['message'] = "transaction $id not found";
            return $respond;
        }
        $transaction->title = $request->title ?? $transaction->title;
        $transaction->description = $request->description ?? $transaction->description;
        $transaction->amount = $request->amount ?? $transaction->amount;
        $transaction->currency = $request->currency ?? $transaction->currency;
        $transaction->date_time = $request->date_time ?? $transaction->date_time;
        $transaction->save();
        $transaction->category;
        $respond['message'] = "updated successfuly";
        $respond['data'] = $transaction;

        return $respond;
    }

    public function destroy($id)
    {
        $data = Transaction::find($id);
        $data->delete();
        return Transaction::all();
    }
    public function updateReccuringTransactions()
    {
         {
            $now = Carbon::now();
            $recurrings = Recurring::WhereDate('next_run','<=',DB::raw('end_date'))->whereDate('next_run','<=',$now->toDateString())->get();
            $transactions=[];
            foreach($recurrings as $recurring){

                $min_now_enddate = Carbon::createFromFormat('Y-m-d', $recurring->end_date)->minimum(Carbon::now());
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
                    array_push($transactions, $transaction);

                }
            }
            return $transactions;


        }


    }

    public function getMonthly(Request $request)
    {



        for ($i = 11; $i >= 0; $i--) {
            $month = date("Y-m-d", strtotime(date('Y-m-01') . " -" . $i - $request->query('range') * 12 . " months"));
            $date = Carbon::createFromFormat('Y-m-d', $month);

            $income = Transaction::whereRelation("category", "type", 'income')->whereDate('date_time', '<=', now())->orderBy('date_time', 'desc')->whereMonth('date_time', $date->month)
                ->whereYear('date_time', $date->year)
                ->sum('amount');

            $expense = Transaction::whereRelation("category", "type", 'expense')->where('date_time', '<=', now())->orderBy('date_time', 'desc')->whereMonth('date_time', $date->month)
                ->whereYear('date_time', $date->year)
                ->sum('amount');

            $data[] = [
                "x" => $date->format('M Y'),
                "income" => $income,
                "expense" => $expense,
                "total"=>$income-$expense


            ];


        }

        $respond = [
            "status" => 201,
            "message" => "Successfully records of last 12 months",
            "data" => $data
        ];

        return $respond;
    }

    public function getWeekly(Request $request)
    {
        $range=$request->query('range');
        $now = Carbon::now()->add((7*$range),'days');
        $start_week = $now->startOfWeek()->format('Y-m-d H:i');
        $end_week = $now->endOfWeek()->format('Y-m-d H:i');

        $period = CarbonPeriod::create($start_week,$end_week);

        foreach($period as $day){

            $income = Transaction::whereRelation("category", "type", 'income')->whereDate('date_time',$day)
                ->sum('amount');

            $expense =  Transaction::whereRelation("category", "type", 'expense')->whereDate('date_time',$day)
            ->sum('amount');

            $data[] = [
                "x" => $day->format('D d M Y'),
                "income" => $income,
                "expense" => $expense,
                "total"=>$income-$expense
            ];


        }

        $respond = [
            "status" => 201,
            "message" => "Successfully records of last 12 months",
            "data" => $data
        ];

        return $respond;
    }

    public function getYearly(Request $request)
{
    $range=$request->query('range');
        $start_year = Carbon::now()->add($range*5 -2,'years');
        $end_year = Carbon::now()->add($range * 5 + 2,'years');
        $period = CarbonPeriod::create($start_year,'1 year',$end_year);


        foreach($period as $day){

            $income = Transaction::whereRelation("category", "type", 'income')->whereYear('date_time',$day)
                ->sum('amount');

            $expense =  Transaction::whereRelation("category", "type", 'expense')->whereYear('date_time',$day)
            ->sum('amount');

            $data[] = [
                "x" => $day->format('Y'),
                "income" => $income,
                "expense" => $expense,
                "total"=>$income-$expense
            ];

        }
        $respond = [
            "status" => 201,
            "message" => "Successfully records of last 12 months",
            "data" => $data
        ];
        return $respond;

}
}



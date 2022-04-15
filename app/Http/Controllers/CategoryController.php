<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    //
    public function index()
    {
        $categories = Category::all();
        $respond = [
            'status' => 201,
            'message' =>  "categories",
            'data' => $categories
        ];
        return $respond;
    }

    public function getIncomesCategories()
    {
        $categories = Category::where('type', 'income')->get();
        $respond = [
            'status' => 201,
            'message' =>  "categories",
            'data' => $categories
        ];
        return $respond;
    }
    public function getExpensesCategories()
    {
        $categories = Category::where('type', 'expense')->get();
        $respond = [
            'status' => 201,
            'message' =>  "categories",
            'data' => $categories
        ];
        return $respond;
    }


    public function get($id)
    {

        $category = Category::find($id);

        if (!isset($category)) {
            $respond = [
                'status' => 201,
                'message' =>  "category $id not found",
                'data' => null
            ];
            return $respond;
        }
        $respond = [
            'status' => 201,
            'message' =>  "category $id founded",
            'data' => $category
        ];
        return $respond;
    }
    public function create(Request $request)
    {
        $respond = [
            'status' => 201,
            'message' => null,
            'data' => null
        ];

        $validator = Validator::make($request->all(), [
            'type' => 'in:expense,income'
        ]);

        if ($validator->fails()) {
            $respond['message'] = $validator->errors();
            return $respond;
        }
        $category = new Category;
        $category->name = $request->name;
        $category->type = $request->type;
        $category->save();
        $respond["message"] = "category created successfully";
        $respond["data"] = $category;
        return $respond;
    }

    public function edit(Request $request, $id)
    {

        $respond = [
            'status' => 201,
            'message' => null,
            'data' => null
        ];
        $data = Category::find($id);
        if (!isset($data)) {
            $respond["status"]=400;
            $respond["message"] = "category $id doesn't exist";
            return $respond;
        }
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:expense,income'
        ]);

        if ($validator->fails()) {
            $respond["status"]=500;
            $respond['message'] = $validator->errors()->first();
            return $respond;
        }

        $data->name = $request->name ?? $data->name;
        $data->type = $request->type ?? $data->type;
        $data->save();
        $respond["message"] = "category edited successfully";
        $respond["data"] = $data;
        return $respond;
    }

    public function destroy($id)
    {
        $data = Category::find($id);

        if (isset($data)) {
            $data->delete();
            $respond = [
                'status' => 201,
                'message' => "category $id deleted successfully",
                'data' => $data
            ];
            return $respond;
        }
        $respond = [
            'status' => 201,
            'message' => "category $id is not found",
            'data' => null
        ];
        return $respond;
    }


    public function yearly(Request $request)
    {
        $range = $request->query('range');
        $now = Carbon::now()->add($range, 'years');

        $incomes = Category::where("type", 'income')->select('name')->withSum(
            ['transaction as sum'
            => function ($query) use ($now) {
                $query->whereYear('date_time', $now);
            }],
            'amount'
        )->get();

        $expenses = Category::where("type", 'expense')->select('name')->withSum(
            ['transaction as sum'
            => function ($query) use ($now) {
                $query->whereYear('date_time', $now);
            }],
            'amount'
        )->get();
        $incomes = $incomes->whereNotNull('sum')->values();
        $expenses =  $expenses->whereNotNull('sum')->values();
        // dd((float)$incomes[0]['sum']);
        foreach($incomes as $each){
            $each->sum = (float)$each->sum;
        }
        foreach($expenses as $each){
            $each->sum = (float)$each->sum;
        }

        return ["start" => $now->format("Y"),"end"=>$now->add(1,"years")->format("Y"), "incomes" => $incomes, "expenses" => $expenses];
    }
    public function monthly(Request $request)
    {
        $range = $request->query('range');
        $now = Carbon::now()->add($range, 'months');
        $month = $now->month;
        $year = $now->year;


        $incomes = Category::where("type", 'income')->select('name')->withSum(
            ['transaction as sum'
            => function ($query) use ($year, $month) {
                $query->whereYear('date_time', $year)->whereMonth('date_time', $month);
            }],
            'amount'
        )->get();
        $expenses = Category::where("type", 'expense')->select('name')->withSum(
            ['transaction as sum'
            => function ($query) use ($year, $month) {
                $query->whereYear('date_time', $year)->whereMonth('date_time', $month);
            }],
            'amount'
        )->get();
        $incomes =  $incomes->whereNotNull('sum')->values();
        $expenses =  $expenses->whereNotNull('sum')->values();
        foreach($incomes as $each){
            $each->sum = (float)$each->sum;
        }
        foreach($expenses as $each){
            $each->sum = (float)$each->sum;
        }
        return ["start" => $now->format("M Y"),"end"=> $now->add(1,"Month")->format("M Y"), "expenses" => $expenses,"incomes"=>$incomes];
    }

    public function weekly(Request $request)
    {
        $range = $request->query('range');
        $now = Carbon::now()->add((7 * $range), 'days');
        $start_week = $now->copy()->startOfWeek();
        $end_week = $now->copy()->endOfWeek();


        $incomes = Category::where("type", 'income')->select('name')->withSum(
            ['transaction as sum'
            => function ($query) use ($start_week, $end_week) {
                $query->whereDate('date_time', '>=', $start_week)->whereDate('date_time', '<=', $end_week);
            }],
            'amount'
        )->get();
        $expenses = Category::where("type", 'expense')->select('name')->withSum(
            ['transaction as sum'
            => function ($query) use ($start_week, $end_week) {
                $query->whereDate('date_time', '>=', $start_week)->whereDate('date_time', '<=', $end_week);
            }],
            'amount'
        )->get();
        $incomes = $incomes->whereNotNull('sum')->values();
        $expenses =  $expenses->whereNotNull('sum')->values();
        foreach($incomes as $each){
            $each->sum = (float)$each->sum;
        }
        foreach($expenses as $each){
            $each->sum = (float)$each->sum;
        }

        return ["start" => $start_week->format('D d M Y'),"end"=> $end_week->format('D d M Y'), "incomes" => $incomes, "expenses" => $expenses];
    }
}

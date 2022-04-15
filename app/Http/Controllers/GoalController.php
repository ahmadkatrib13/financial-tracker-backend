<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class goalController extends Controller
{
    public function getAll()
    {

        $income     =   Transaction::whereRelation("category", "type", 'income')->get()->sum('amount');
        $expense    =   Transaction::whereRelation("category", "type", 'expense')->get()->sum('amount');
        $total      =   $income - $expense;
        $profit_goal=   Goal::first();
        if(!isset($profit_goal)){
            $profit_goal = new Goal;
            $profit_goal->profit_goal = 0;
            $profit_goal->save();
        }

        $goal = (float)$profit_goal->profit_goal;

        $percentage = 100* $total/$goal;

      return [
            'status' => 201,
            'message' => null,
            'data' => [
                "id"=>$profit_goal->id,
                "total"=>$total,
                "percentage"=>$percentage>=100?100:($percentage<=0?0:$percentage),
                "profit_goal"=>$goal
            ]
        ];
    }
    public function update(Request $request, $id)
    {
        $goal = Goal::find($id);
        if(!isset($goal)){
            return [
                'status' => 400,
                'message' => "not found",
                'data' =>null
            ];
        }
        $goal->profit_goal = $request->profit_goal;
        $goal->save();

        $income     =   Transaction::whereRelation("category", "type", 'income')->get()->sum('amount');
        $expense    =   Transaction::whereRelation("category", "type", 'expense')->get()->sum('amount');
        $total      =   $income - $expense;

        $profitgoal = (float)$goal->profit_goal;

        $percentage = 100* $total/$profitgoal;

        return [
            'status' => 201,
            'message' => null,
            'data' => [
                "id"=>$goal->id,
                "total"=>$total,
                "percentage"=>$percentage>=100?100:($percentage<=0?0:$percentage),
                "profit_goal"=>$profitgoal
            ]
        ];
    }
    public function create(Request $request)
    {
        $respond = [
            'status' => 201,
            'message' => null,
            'data' => null
        ];

        $validator = Validator::make($request->all(), [
            'profit_goal' => 'required|numeric',
        ]);
        if($validator->fails()){
            if ($validator->fails()) {
                $respond['status'] = 400;
                $respond['message'] = $validator->errors()->first();
                return $respond;
            }
        }

        $goal = new Goal;
        $goal->profit_goal = $request->profit_goal;
        $goal->save();
    }
}

<?php

use App\Http\Controllers\RecurringController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoalController;
/*
|-----------------------------      ---------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });




Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'authenticate']);

// Route::group(['middleware' => ['jwt.verify']], function () {
    Route::group(['prefix' => 'transactions'], function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/monthly', [TransactionController::class, 'getMonthly']);
        Route::get('/weekly', [TransactionController::class, 'getWeekly']);
        Route::get('/yearly', [TransactionController::class, 'getYearly']);
        Route::get('/incomes', [TransactionController::class, 'getAllIncomes']);
        Route::get('/expenses', [TransactionController::class, 'getAllExpenses']);
        Route::get('/total', [TransactionController::class, 'getTotalAmount']);
        Route::get('/total/expenses', [TransactionController::class, 'getTotalExpenses']);
        Route::get('/total/incomes', [TransactionController::class, 'getTotalIncomes']);
        Route::get('/total/incomes', [TransactionController::class, 'getTotalIncomes']);
        Route::post('/add', [TransactionController::class, 'create']);
        Route::put('/edit/{id}', [TransactionController::class, 'edit']);
        Route::delete('/delete/{id}', [TransactionController::class, 'destroy']);
        Route::get('/update', [TransactionController::class, 'updateReccuringTransactions']);
        Route::get('/{id}', [TransactionController::class, 'getTransaction']);
    });
    Route::group(['prefix' => 'recurrings'], function () {
        Route::get('/', [RecurringController::class, 'index']);
        Route::put('/edit/{id}', [RecurringController::class, 'edit']);
        Route::delete('/delete/{id}', [RecurringController::class, 'destroy']);
    });



    Route::group(['prefix' => 'admin'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'get']);
        Route::put('/edit/{id}', [UserController::class, 'edit']);
        Route::delete('/delete/{id}', [UserController::class, 'destroy']);
    });

Route::group(['prefix' => 'recurrings'], function () {
    Route::get('/', [RecurringController::class, 'index']);
    Route::put('/edit/{id}', [RecurringController::class, 'edit']);
    Route::delete('/delete/{id}', [RecurringController::class, 'destroy']);
});

Route::group(['prefix' => 'categories'], function()
{
    Route::get('/',[CategoryController::class,'index']);
    Route::get('/yearly',[CategoryController::class,'yearly']);
    Route::get('/monthly',[CategoryController::class,'monthly']);
    Route::get('/weekly',[CategoryController::class,'weekly']);
    Route::get('/income',[CategoryController::class,'getIncomesCategories']);
    Route::get('/expense',[CategoryController::class,'getExpensesCategories']);
    Route::get('/{id}',[CategoryController::class,'get']);
    Route::post('/add',[CategoryController::class,'create']);
    Route::put('/edit/{id}',[CategoryController::class,'edit']);
    Route::delete('/delete/{id}',[CategoryController::class,'destroy']);
});

Route::get("/profit",[GoalController::class,'getAll']);
Route::put("/profit/edit/{id}",[GoalController::class,'update']);

// });

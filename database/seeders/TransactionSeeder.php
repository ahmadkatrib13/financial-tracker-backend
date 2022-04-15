<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        DB::table('transactions')->insert([
            'title' => "Company",
            'description' => "I Got paid",
            'amount'=>3000,
            'currency'=>'$',
            'date_time'=>  date("Y-m-d H:i:s") ,
            'status'=>'unpaid',
            'category_id'=>1
        ]);
        DB::table('transactions')->insert([
            'title' => "Company",
            'description' => "I Got paid",
            'amount'=>2000,
            'currency'=>'$',
            'date_time'=>  date("Y-m-d H:i:s") ,
            'status'=>'unpaid',
            'category_id'=>2
        ]);

    }
}

<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date);
        $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date ?? Carbon::now());
        $sales = Sale::with("product:id,name,price", "employee:id,name", "customer:id,first_name,last_name")->where('date', '>=', $startDate)->orWhere('date', '<=', $endDate)->orderBy('date');


        $sales->get();
        return response()->json($sales);
    }

    public function sales(Request $request)
    {
        $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date);
        $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date ?? Carbon::now());
        $totalSalesPerDay = DB::select("SELECT
                                            SUM(total_sale_by_product) AS total_sale, date
                                        FROM
                                            (SELECT
                                                date, (COUNT(product_id) * (products.price)) AS total_sale_by_product
                                            FROM
                                                laravel.sales
                                            JOIN laravel.products ON laravel.products.id = laravel.sales.product_id
                                            WHERE (date BETWEEN :start_day AND :end_day)
                                            GROUP BY product_id , date
                                            ORDER BY date) AS sub
                                        GROUP BY date
                                        ORDER BY date", ['start_day' => $startDate->subDay(), 'end_day' => $endDate]);




        return response()->json($this->prepareDailySalesData($startDate, $endDate, $totalSalesPerDay));
    }

    private function prepareDailySalesData(Carbon $startDate, Carbon $endDate, $totalSalesPerDay)
    {
        $formatted = array_combine(
            array_map(function ($v) {
                return $v->date;
            }, $totalSalesPerDay),
            array_map(function ($v) {
                return $v->total_sale;
            }, $totalSalesPerDay)
        );

        $result = [];
        while ($startDate->addDay() < $endDate) {
            if (array_key_exists($startDate->setTime(0, 0, 0)->format('Y-m-d H:i:s'), $formatted)) {
                $result[$startDate->setTime(0, 0, 0)->format('Y-m-d')] = $formatted[$startDate->setTime(0, 0, 0)->format('Y-m-d H:i:s')];
            } else {
                $result[$startDate->setTime(0, 0, 0)->format('Y-m-d')] = 0;
            }
            $startDate = $startDate->addDay();
        }
        return $result;
    }
}

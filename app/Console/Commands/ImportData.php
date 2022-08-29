<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Console\Command;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ImportData extends Command
{
    private array $columns;

    public function __construct()
    {
        parent::__construct();

        $this->columns = [
            "customers" => ["id", "first_name", "last_name", "email", "gender", "street", "city"],
            "sales" => ["id", "product_id", "sales_person_id", "customer_id", "date"],
            "products" => ["id", "name", "price"],
            "employees" => ["id", "name"]
        ];
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:xlsx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import TestData from xlsx file.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("---READING XLSX FILE---");
        $sheets = (new FastExcel)->withSheetsNames()->importSheets(Storage::disk('public')->path("TestData.xlsx"));
        $this->info("---XLSX FILE READ---");
        $this->storeCustomers($sheets["customers"]);
        $this->storeProducts($sheets["products"]);
        $this->storeEmployees($sheets["employee"]);
        $this->storeSales($sheets["sales"]);
        return 0;
    }

    private function storeCustomers($customers)
    {
        if ($this->checkDatabase("customers")) {
            $this->info("---IMPORT STARTED: CUSTOMERS---");
            foreach ($customers as $customer) {
                Customer::updateOrCreate(
                    [
                        "id" => $customer["id"]
                    ],
                    [
                        "first_name" => $customer["first_name"],
                        "last_name" => $customer["last_name"],
                        "email" => $customer["email"],
                        "gender" => $customer["gender"],
                        "street" => $customer["street"],
                        "city" => iconv('UTF-8', 'macintosh', $customer["city"])
                    ]
                );
            }
            $this->info("---IMPORT FINISHED: CUSTOMERS---");
            $this->info("---TOTAL CUSTOMERS: " . count($customers) . "---");
        } else {
            $this->error("SKIPPED: CUSTOMERS!");
        }
    }

    private function storeProducts($products)
    {
        if ($this->checkDatabase("products")) {
            $this->info("---IMPORT STARTED: PRODUCTS---");
            foreach ($products as $product) {
                Product::updateOrCreate(
                    [
                        "id" => $product["productId"]
                    ],
                    [
                        "name" => $product["name"],
                        "price" => $product["price"]
                    ]
                );
            }
            $this->info("---IMPORT FINISHED: PRODUCTS---");
            $this->info("---TOTAL PRODUCTS: " . count($products) . "---");
        } else {
            $this->error("SKIPPED: PRODUCTS!");
        }
    }

    private function storeEmployees($employees)
    {
        if ($this->checkDatabase("employees")) {
            $this->info("---IMPORT STARTED: EMPLOYEES---");
            foreach ($employees as $employee) {
                Employee::updateOrCreate(
                    [
                        "id" => $employee["id"]
                    ],
                    [
                        "name" => $employee["name"]
                    ]
                );
            }
            $this->info("---IMPORT FINISHED: EMPLOYEES---");
            $this->info("---TOTAL EMPLOYEES: " . count($employees) . "---");
        } else {
            $this->error("SKIPPED: EMPLOYEES!");
        }
    }

    private function storeSales($sales)
    {
        if ($this->checkDatabase("sales")) {
            $this->info("---IMPORT STARTED: SALES---");
            foreach ($sales as $sale) {
                Sale::updateOrCreate(
                    [
                        "id" => $sale["invoiceId"]
                    ],
                    [
                        "product_id" => (Product::where('name', $sale["product_name"])->first())->id,
                        "sales_person_id" => (Employee::where('name', $sale["sales_person"])->first())->id,
                        "customer_id" => (Customer::get()->where('full_name', $sale["customer name"])->first())->id,
                        "date" => new Carbon($sale["date"])
                    ]
                );
            }
            $this->info("---IMPORT FINISHED: SALES---");
            $this->info("---TOTAL SALES: " . count($sales) . "---");
        } else {
            $this->error("SKIPPED: SALES!");
        }
    }

    private function checkDatabase($tableName): bool
    {
        $this->info("---DATABASE REQUIREMENTS ARE CHECKING FOR '$tableName'---");
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumns($tableName, $this->columns[$tableName])) {
                return true;
            } else {
                $this->error($tableName . " can't imported. Columns doesn't exists");
            }
        } else {
            $this->error($tableName . " can't imported. Table doesn't exists");
        }
        return false;
    }
}

<?php

namespace Database\Seeders;

use App\Models\PaymentHistory;
use Illuminate\Database\Seeder;

class PaymentHistorySeeder extends Seeder
{
    public function run(): void
    {
        PaymentHistory::factory()->count(10)->create();
    }
}

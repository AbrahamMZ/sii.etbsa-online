<?php

use App\Components\Common\Models\AgencyBankAccount;
use Illuminate\Database\Seeder;

class AgencyBankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $agency_bank_accounts = factory(AgencyBankAccount::class,3)->create();
    }
}

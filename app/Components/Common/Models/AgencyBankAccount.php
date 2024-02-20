<?php

namespace App\Components\Common\Models;

use App\Components\FlujoEfectivo\Models\BankPolicy;
use Illuminate\Database\Eloquent\Model;

class AgencyBankAccount extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'agency_bank_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['agency_id', 'bank_name', 'account_number', 'balance'];

    protected $appends = ['income_balance'];

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function bankPolicy()
    {
        return $this->hasMany(BankPolicy::class, 'agency_bank_accounts_id', 'id');
    }

    public function getIncomeBalanceAttribute()
    {
        $total = $this->bankPolicy->reduce(function ($carry, $item) {
            return $carry + $item->amount;
        });

        return $total;
    }
}

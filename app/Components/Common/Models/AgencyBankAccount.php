<?php

namespace App\Components\Common\Models;


use App\Components\FlujoEfectivo\Models\BankPolicy;

use App\Components\FlujoEfectivo\Models\Poliza;

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
    protected $appends = ['calculo'];

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

    public function polizas()
    {
        return $this->hasMany(Poliza::class, 'apply_bank_accounts_id', 'id');
    }
    public function polizasTranferencia()
    {
        return $this->hasMany(Poliza::class, 'origen_bank_accounts_id', 'id');
    }

    public function ingresos()
    {
        return $this->polizas()->where('tipo_poliza_id', 1)->where('is_applied', true);
    }

    public function egresos()
    {
        return $this->polizas()->where('tipo_poliza_id', 2)->where('is_applied', true);
    }

    public function transferenciasIn()
    {
        return $this->polizas()->where('tipo_poliza_id', 3)->where('is_applied', true);
    }
    public function transferenciasOut()
    {
        return $this->polizasTranferencia()->where('tipo_poliza_id', 3)->where('is_applied', true);
    }

    public function getCalculoAttribute()
    {
        $ingresos = $this->ingresos()->sum('amount');
        $egresos = $this->egresos()->sum('amount');
        $transferencias_in = $this->transferenciasIn()->sum('amount');
        $transferencias_out = $this->transferenciasOut()->sum('amount');

        return $ingresos - $egresos + $transferencias_in - $transferencias_out;
    }

}

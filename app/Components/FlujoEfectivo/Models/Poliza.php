<?php

namespace App\Components\FlujoEfectivo\Models;

use App\Components\Common\Models\AgencyBankAccount;
use App\Components\Common\Models\Currency;
use App\Components\Product\Models\ProductCategory;
use App\Components\User\Models\User;
use Illuminate\Database\Eloquent\Model;

class Poliza extends Model
{
    protected $table = 'polizas';
    protected $fillable = [
        "external_id",
        "reference_number",
        "reference_name",
        "reference_concept",
        "description",
        "amount",
        "currency_id",
        "payment_source_id",
        "tipo_poliza_id",
        "origen_bank_accounts_id",
        "apply_bank_accounts_id",
        "category_id",
        "is_applied",
        "apply_date",
        "user_created",
        "user_updated",
    ];

    protected $appends = ['unidentified'];

    public function origenAgencyBankAccount()
    {
        return $this->belongsTo(AgencyBankAccount::class, 'origen_bank_accounts_id');
    }
    public function applyAgencyBankAccount()
    {
        return $this->belongsTo(AgencyBankAccount::class, 'apply_bank_accounts_id');
    }
    public function PaymentSource()
    {
        return $this->belongsTo(PaymentSource::class, 'payment_source_id');
    }
    public function tipoPoliza()
    {
        return $this->belongsTo(TipoPoliza::class, 'tipo_poliza_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    public function createdUser()
    {
        return $this->belongsTo(User::class, 'user_created');
    }
    public function updatedUser()
    {
        return $this->belongsTo(User::class, 'user_updated');
    }


    public function getUnidentifiedAttribute()
    {
        return empty($this->reference_number)
            || empty($this->reference_name)
            || empty($this->reference_concept)
            || empty($this->category_id);
    }

    public function scopeNoAplicados($query)
    {
        return $query->where(function ($query) {
            $query->where('is_applied', false);
        });
    }
    public function scopeUnidentified($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('reference_number')
                ->orWhere('reference_number', '');
        })
            ->orWhere(function ($query) {
                $query->whereNull('reference_name')
                    ->orWhere('reference_name', '');
            })
            ->orWhere(function ($query) {
                $query->whereNull('reference_concept')
                    ->orWhere('reference_concept', '');
            })
            ->orWhere(function ($query) {
                $query->whereNull('category_id')
                    ->orWhere('category_id', '');
            });
    }
    public function scopeIdentified($query)
    {
        return $query->where(function ($query) {
            $query->whereNotNull('reference_number')
                ->where('reference_number', '!=', '');
        })
            ->where(function ($query) {
                $query->whereNotNull('reference_name')
                    ->where('reference_name', '!=', '');
            })
            ->where(function ($query) {
                $query->whereNotNull('reference_concept')
                    ->where('reference_concept', '!=', '');
            })
            ->where(function ($query) {
                $query->whereNotNull('category_id')
                    ->where('category_id', '!=', '');
            });
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['tipo_poliza'] ?? null, function ($query, $tipo_poliza) {
            $query->whereHas('tipoPoliza', function ($query) use ($tipo_poliza) {
                return $query->where('key', $tipo_poliza);
            });
        })->when($filters['payment_sources'] ?? null, function ($query, $payment_sources) {
            $query->whereHas('PaymentSource', function ($query) use ($payment_sources) {
                return $query->whereIn('id', $payment_sources);
            });
        });
    }

    public function scopeIngresos($query)
    {
        return $query->where('tipo_poliza_id', 1);
    }
    public function scopeEgresos($query)
    {
        return $query->where('tipo_poliza_id', 2);
    }
    public function scopeTransferencias($query)
    {
        return $query->where('tipo_poliza_id', 3);
    }

    public function scopeFilterPermission($query, User $user)
    {
        $query->when($user->hasPermission('caja.admin'),
            function ($query) {
                return $query;
            },
            function ($query) use ($user) {
                $query->when($user->inGroup('Flujo Efectivo') ?? null, function ($query) use ($user) {
                    $query->where('user_created', $user->id);
                });
            }
        );
    }


}

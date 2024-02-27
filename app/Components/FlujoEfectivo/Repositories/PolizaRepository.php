<?php

namespace App\Components\FlujoEfectivo\Repositories;


use App\Components\Common\Models\AgencyBankAccount;
use App\Components\Common\Models\Currency;
use App\Components\Core\BaseRepository;
use App\Components\FlujoEfectivo\Models\PaymentSource;
use App\Components\FlujoEfectivo\Models\Poliza;
use App\Components\FlujoEfectivo\Models\TipoPoliza;
use App\Components\Product\Models\ProductCategory;
use Auth;

class PolizaRepository extends BaseRepository
{
    public function __construct(Poliza $model)
    {
        parent::__construct($model);
    }

    /**
     * list resource
     *
     * @param array $params
     * @return 
     */
    public function index($params)
    {
        return $this->get($params, [
            'applyAgencyBankAccount',
            'origenAgencyBankAccount',
            'PaymentSource',
            'tipoPoliza',
            'currency',
            'category',
            'createdUser',
            'updatedUser',
        ], function ($query) use ($params) {

            $query->filter($params);

            $query->when($params['unidentified'] ?? null, function ($query, $unidentified) {
                $unidentified == "unidentified" ? $query->unidentified() : $query->identified();
            });

            $query->filterPermission(Auth::user());

            return $query;

        });
    }

    public function getOptions()
    {
        $options['agency_bank_accounts'] = AgencyBankAccount::with('agency:id,title')->get(['id', 'bank_name', 'account_number', 'agency_id']);
        $options['currencies'] = Currency::all('id', 'name');
        $options['payment_sources'] = PaymentSource::all('id', 'key', 'title');
        $options['tipo_polizas'] = TipoPoliza::all('id', 'key', 'title');
        $options['category'] = ProductCategory::all('id', 'name');

        return compact('options');
    }
}
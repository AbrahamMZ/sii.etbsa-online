<?php

namespace App\Http\Controllers\Admin;

use App\Components\Common\Models\AgencyBankAccount;
use App\Components\Common\Models\Currency;
use App\Components\FlujoEfectivo\Models\BankPolicy;
use Illuminate\Http\Request;

class BankPolicyController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $bank_policies = BankPolicy::with('agencyBankAccount', 'currency')->get();
        $options['agency_bank_accounts'] = AgencyBankAccount::with('agency:id,title')->get(['id', 'bank_name', 'account_number', 'agency_id']);
        $options['currencies'] = Currency::all('id', 'name');
        $options['payment_source'] = ['Cheque', 'Tranferencia', 'Efectivo'];

        return $this->sendResponse(compact('bank_policies', 'options'), 'Polizas');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = validator($request->all(), []);
        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        $policy = BankPolicy::create($request->all());

        return $this->sendResponseCreated(compact('policy'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Components\FlujoEfectivo\Models\BankPolicy  $bankPolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BankPolicy $bankPolicy)
    {
        $validate = validator($request->all(), []);
        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        $updated = $bankPolicy->update($request->all());

        return $this->sendResponseUpdated(compact('updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Components\FlujoEfectivo\Models\BankPolicy  $bankPolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy(BankPolicy $bankPolicy)
    {
        $bankPolicy->delete();

        return $this->sendResponseDeleted();
    }
}

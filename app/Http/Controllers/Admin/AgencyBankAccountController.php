<?php


namespace App\Http\Controllers\Admin;

use App\Components\Common\Models\Agency;
use App\Components\Common\Models\AgencyBankAccount;
use Illuminate\Http\Request;

class AgencyBankAccountController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        // \DB::raw('SUM(polizas.amount) as total_amount')

        // $polizasPorTipo = \DB::table('agency_bank_accounts')
        //     ->join('polizas', 'agency_bank_accounts.id', '=', 'polizas.apply_bank_accounts_id')
        //     ->select('agency_bank_accounts.id', 'polizas.tipo_poliza_id','polizas.id as poliza_id')
        //     ->groupBy('agency_bank_accounts.id', 'polizas.tipo_poliza_id','polizas.id')
        //     ->get();

        $polizasPorTipo = \DB::table('agency_bank_accounts')
            ->join('polizas', 'agency_bank_accounts.id', '=', 'polizas.apply_bank_accounts_id')
            ->select('agency_bank_accounts.id', 'polizas.tipo_poliza_id', 'polizas.id as poliza_id')
            ->groupBy('agency_bank_accounts.id', 'polizas.tipo_poliza_id','polizas.id')
            ->get();

        // Organizar los resultados en un array bidimensional
        $tablaPolizas = [];
        foreach ($polizasPorTipo as $poliza) {
            $cuentaId = $poliza->id;
            $tipoPolizaId = $poliza->tipo_poliza_id;
            $polizaId = $poliza->poliza_id;

            // Agregar la póliza a la fila correspondiente en la tabla
            $tablaPolizas[$tipoPolizaId][$cuentaId] = $polizaId;
        }

        // return compact('polizasPorTipo','tablaPolizas');

        $accounts = AgencyBankAccount::with('agency:id,title')->get();
        $options['agencies'] = Agency::all('id', 'title', 'code');
        return $this->sendResponse(compact('accounts', 'options','tablaPolizas','polizasPorTipo'), 'cuentas de Sucursales');
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

        $account = AgencyBankAccount::create($request->all());

        return $this->sendResponseCreated(compact('account'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Components\Common\Models\AgencyBankAccount  $agencyBankAccount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AgencyBankAccount $agencyBankAccount)
    {

        $validate = validator($request->all(), []);
        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        $updated = $agencyBankAccount->update($request->all());

        return $this->sendResponseUpdated(compact('updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Components\Common\Models\AgencyBankAccount  $agencyBankAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(AgencyBankAccount $agencyBankAccount)
    {

        $agencyBankAccount->delete();

        return $this->sendResponseDeleted();
    }
}

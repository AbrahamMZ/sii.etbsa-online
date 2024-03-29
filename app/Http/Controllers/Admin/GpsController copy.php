<?php

namespace App\Http\Controllers\Admin;

use App\Components\Gps\Models\Gps;
use App\Components\Gps\Models\GpsChips;
use App\Components\Gps\Models\GpsGroup;
use App\Components\Gps\Repositories\GpsRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GpsController extends AdminController
{
    /**
     * @var GpsRepository
     */
    private $gpsRepository;

    /**
     * FileGroupController constructor.
     * @param GpsRepository $gpsRepository
     */
    public function __construct(GpsRepository $gpsRepository)
    {
        $this->gpsRepository = $gpsRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $gps = $this->gpsRepository->index($request->all());
        return $this->sendResponseOk(compact('gps'));
    }

    public function create()
    {
        $resources = [
            'groups_gps' => GpsGroup::all('id', 'name'),
            'chips_gps' => GpsChips::whereNull('gps_id')->get('sim', 'costo'),
            'types' => ['CONTADO', 'CREDITO', 'CARGO']
        ];

        return $this->sendResponseOk(compact('resources'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = validator($request->all(), [
            'name' => 'required|unique:gps,name',
            'gps_group_id' => 'required',
            'installation_date' => 'required',
            'renew_date' => 'required',
            'description' => 'required',
            'payment_type' => 'required',
            'gps_chip_id' => 'required|unique:gps,gps_chip_id',
        ], [
            'name.required' => 'El campo nombre es obligatorio',
            'name.unique' => 'Nombre GPS Duplicado',
            'gps_chip_id.unique' => 'Error Chip Duplicado, Ya se encuentra Asignado',
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        $request['uploaded_by'] = Auth::user()->id;
        $gps = $this->gpsRepository->create($request->all());

        if (!$gps) {
            return $this->sendResponseBadRequest("Failed to create.");
        }

        return $this->sendResponseCreated($gps);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $gps = $this->gpsRepository->find($id, ['chip', 'gpsGroup', 'historical', 'user']);

        if (!$gps) {
            return $this->sendResponseNotFound();
        }

        return $this->sendResponseOk($gps);
    }



    public function edit(Gps $gp)
    {
        $data = $gp->only(
            'id',
            'name',
            'invoice',
            'currency',
            'renew_date',
            'description',
            'gps_chip_id',
            'gps_group_id',
            'payment_type',
            'invoice_date',
            'exchange_rate',
            'installation_date',
        );

        if (!$gp) {
            return $this->sendResponseNotFound();
        }

        return $this->sendResponseOk($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = validator($request->all(), [
            'name' => 'required|string',
            'amount' => 'numeric',
        ], [
            'name.required' => 'El campo nombre es obligatorio',
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        $updated = $this->gpsRepository->update($id, $request->all());

        if (!$updated) {
            return $this->sendResponseBadRequest("Failed to update");
        }

        return $this->sendResponseOk([], "Updated.");
    }

    public function renewInvoice(Request $request, $id)
    {
        $validate = validator($request->all(), [
            'invoice' => 'required',
            'amount' => 'required|numeric',
            'currency' => 'required',
            'exchange_rate' => 'required|numeric',
        ], [
            'invoice.required' => 'La Factura es Requerida',
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        $updated = false;
        DB::transaction(function () use ($id, $request, $updated) {
            $this->gpsRepository->keepHistorical($id);
            $renew = new Carbon($request->installation_date);
            $renew->setYear(Carbon::now()->year);

            $request['renew_date'] = $renew->addYear();
            $request['estatus'] = 'RENOVADO';
            $request['uploaded_by'] = auth()->user()->id;
            $updated = $this->gpsRepository->update($id, $request->all());
        });

        // if (!$updated) {
        //     return $this->sendResponseBadRequest("Error en la Renovacion ");
        // }

        return $this->sendResponseOk([], "GPS RENOVADO.");
    }

    public function cancelled(Request $request, $id)
    {
        $validate = validator($request->all(), [
            'cancellation_date' => 'required',
            'description' => 'required',
        ], [
            'cancellation_date.required' => 'Ingrese Fecha de Cancelacion',
            'description.required' => 'Ingrese Motivo de la Cancelacion',
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        $updated = false;
        DB::transaction(function () use ($id, $request, $updated) {
            $this->gpsRepository->keepHistorical($id);
            $this->gpsRepository->cancelGps($id);
            $request['estatus'] = 'CANCELADO';
            $request['invoice'] = null;
            $request['amount'] = 0;
            $request['currency'] = 'MXN';
            $request['exchange_rate'] = 1;
            $request['renew_date'] = null;
            // $request['installation_date'] = null;
            $request['uploaded_by'] = auth()->user()->id;
            $updated = $this->gpsRepository->update($id, $request->all());
        });
        // if (!$updated) {
        //     return $this->sendResponseBadRequest("Error en la Cancelacion");
        // }

        return $this->sendResponseOk([], "GPS Cancelado.");
    }

    public function reasign(Request $request, $id)
    {
        $validate = validator($request->all(), [
            'name' => 'required',
            'gps_chip_id' => 'required',
            'gps_group_id' => 'required',
            'installation_date' => 'required',
            'description' => 'required',
            // 'gps_chip_id' => 'required|unique:gps,gps_chip_id',
        ], [
            'name.required' => 'Nombre GPS es Requrido',
            'description.required' => 'Es necesario un Comentario',
            // 'gps_chip_id.unique' => 'Error Chip Duplicado, Ya se encuentra Asignado',
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        DB::transaction(function () use ($id, $request) {
            $this->gpsRepository->keepHistorical($id);
            // $renew = new Carbon($request->installation_date);
            // $renew->setYear(Carbon::now()->year);

            $request['renew_date'] = $request->installation_date;
            $request['estatus'] = 'REASIGNADO';
            $request['cancellation_date'] = null;
            $request['uploaded_by'] = auth()->user()->id;
            $this->gpsRepository->update($id, $request->all());
        });

        // if (!$updated) {
        //     return $this->sendResponseBadRequest("Error en Reasignar");
        // }

        return $this->sendResponseOk([], "GPS Reasignado.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->gpsRepository->delete($id);

        return $this->sendResponseOk([], "Deleted.");
    }

    public function stats()
    {
        $year = Carbon::now()->year;
        $stats = [];

        for ($month = 1; $month <= 12; $month++) {
            $stats[] = $this->gpsRepository->getStatsGps($month, $year);
        }

        return $this->sendResponseOk($stats, "Get Estadisticas GPS.");
    }

    public function resources()
    {
        $chips = GpsChips::all('sim');
        $groups = GpsGroup::all('id', 'name');

        return $this->sendResponseOk(compact('chips', 'groups'));
    }
}

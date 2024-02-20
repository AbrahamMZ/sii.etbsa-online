<?php

namespace App\Http\Controllers\Admin;

use App\Components\Common\Models\Township;
use App\Components\Customers\Repositories\CustomerRepository;
use App\Components\Tracking\Repositories\ProspectRepository;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ProspectController extends AdminController
{

    /**
     * @var ProspectRepository
     */
    private $prospectRepository;
    private $customerRepository;

    /**
     * ProspectController constructor.
     * @param ProspectRepository $prospectRepository
     * @param CustomerRepository $customerRepository
     */
    public function __construct(ProspectRepository $prospectRepository, CustomerRepository $customerRepository)
    {
        $this->prospectRepository = $prospectRepository;
        $this->customerRepository = $customerRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->prospectRepository->listProspect(request()->all());

        return $this->sendResponseOk($data, "list prospect ok.");
    }

    public function options()
    {

        $options['customers'] = $this->customerRepository->list(['paginate' => 'no'])->map->only('id', 'full_name', 'rfc');


        return $this->sendResponseOk(compact('options'), "list prospect ok.");
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
            'full_name' => 'required',
            // 'township_id' => 'required',
            'phone' => 'size:10|required|unique:prospect,phone',
            // 'is_moral' => 'required',
        ], [
            'phone.unique' => 'El Telefono ya se encuentra registrado',
            'phone.size' => 'El Telefono debe tener 10 Digitos',
            'phone.required' => 'El Telefono es requerido',
            // 'township_id.required' => 'El Municipio es requerido',
            'full_name.required' => 'El Nombre es requerido',
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        $request['registered_by'] = Auth::user()->id;

        /** @var \App\Components\Tracking\Models\Prospect $prospect */
        $prospect = $this->prospectRepository->create($request->all());

        if (!$prospect) {
            return $this->sendResponseBadRequest('Pospecto no Credo');
        }

        $prospect->setMeta('cultivos', $request->cultivos);
        $prospect->save();

        return $this->sendResponseCreated(compact('prospect'), 'Prospecto Registrado');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $prospect = $this->prospectRepository->find($id);

        if (!$prospect) {
            return $this->sendResponseNotFound();
        }

        return $this->sendResponseOk($prospect);
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
            'full_name' => 'required',
            'township_id' => 'required',
            'phone' => ['required', 'size:10', Rule::unique('prospect')->ignore($id)],
            'is_moral' => 'required',
        ], [
            'phone.unique' => 'El Telefono ya se encuentra registrado',
            'phone.size' => 'El Telefono debe tener 10 Digitos',
            'phone.required' => 'El Telefono es requerido',
            'township_id.required' => 'El Municipio es requerido',
            'full_name.required' => 'El Nombre es requerido',
        ]);

        if ($validate->fails())
            return $this->sendResponseBadRequest($validate->errors()->first());

        $payload = $request->all();

        $updated = $this->prospectRepository->update($id, $payload);

        if (!$updated)
            return $this->sendResponseBadRequest();

        $prospect = $this->prospectRepository->find($id);
        $prospect->setMeta('cultivos', $request->cultivos);
        $prospect->save();

        return $this->sendResponseUpdated(compact('prospect'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $this->prospectRepository->delete($id);
        } catch (\Exception $e) {
            return $this->sendResponseBadRequest("Failed to delete");
        }
        return $this->sendResponseDeleted();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Components\Customers\Models\Customers;
use App\Components\Customers\Repositories\CustomerRepository;
use Auth;
use Illuminate\Http\Request;

class CustomersController extends AdminController
{
    private $customersRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customersRepository = $customerRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->customersRepository->list(request()->all());
        return $this->sendResponseOk($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['created_by'] = Auth::user()->id;
        $validate = validator($request->all(), [
            'full_name' => 'required|unique:customers,full_name',
        ], [
            'full_name.unique' => 'Nombre de Cliente ya se encuentra registrado',
            'full_name.required' => 'El Nombre es requerido',
        ]);
        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        $customer = $this->customersRepository->create($request->all());
        if (!$customer) {
            return $this->sendResponseBadRequest("Failed create.");
        }
        return $this->sendResponseCreated($customer);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Customers $customer)
    {

        $data = [
            'id' => $customer->id,
            'name' => $customer->full_name,
            'company' => $customer->company ?? '',
            'rfc' => $customer->rfc ?? '',
            'curp' => $customer->curp ?? '',
            'number_customer' => $customer->number_customer ?? '',
            'phone' => $customer->phone ?? '',
            'address' => $customer->address . ' ' . $customer->colonia . ',' . $customer->code_postal,
            'estado' => $customer->township->estate->name ?? '',
            'municipio' => $customer->township->name ?? '',
            'path' => $customer->path_documents,
            'metatable' => $customer->meta,
        ];

        return $this->sendResponseOk($data);
    }

    public function edit(Customers $customer)
    {
        return $this->sendResponseOk($customer);
    }
    public function create(Request $request)
    {
        return $this->sendResponseOk();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customers $customer)
    {
        $validate = validator($request->all(), []);
        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        $updated = $customer->update($request->all());
        if (!$updated) {
            return $this->sendResponseBadRequest("Failed create.");
        }
        return $this->sendResponseCreated($updated);
    }

    public function updateMeta(Request $request, Customers $customer)
    {

        $customer->setMeta(
            $request->all()
        );
        $customer->save();
        return $this->sendResponseCreated($customer->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        return $this->sendResponseDeleted();
    }
}

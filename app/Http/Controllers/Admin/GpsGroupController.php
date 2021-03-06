<?php

namespace App\Http\Controllers\Admin;

use App\Components\Gps\Repositories\GpsGroupRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GpsGroupController extends AdminController
{
    /**
     * @var GpsGroupRepository
     */
    private $gpsGroupRepository;

    /**
     * FileGroupController constructor.
     * @param GpsGroupRepository $gpsGroupRepository
     */
    public function __construct(GpsGroupRepository $gpsGroupRepository)
    {
        $this->gpsGroupRepository = $gpsGroupRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $this->gpsGroupRepository->index($request->all());

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
        $validate = validator($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|unique:gps_groups,phone',
            // 'description' => 'required|string',
        ], [
            'name.required' => 'El campo nombre es obligatorio',
            'phone.unique' => 'Error Telefono Duplicado, Ya se encuentra Registrado',
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        $file = $this->gpsGroupRepository->create($request->all());

        if (!$file)  return $this->sendResponseBadRequest("Failed to create.");

        return $this->sendResponseCreated($file);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $file = $this->gpsGroupRepository->find($id);

        if (!$file) return $this->sendResponseNotFound();

        return $this->sendResponseOk($file);
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
            'description' => 'required|string',
        ]);

        if ($validate->fails()) return $this->sendResponseBadRequest($validate->errors()->first());

        $updated = $this->gpsGroupRepository->update($id, $request->all());

        if (!$updated) return $this->sendResponseBadRequest("Failed to update");

        return $this->sendResponseOk([], "Updated.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->gpsGroupRepository->delete($id);

        return $this->sendResponseOk([], "Deleted.");
    }
}

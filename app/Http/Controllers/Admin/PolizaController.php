<?php


namespace App\Http\Controllers\Admin;

use App\Components\FlujoEfectivo\Models\Poliza;
use App\Components\FlujoEfectivo\Repositories\PolizaRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class PolizaController extends AdminController
{

    private $polizaRepository;

    public function __construct(PolizaRepository $polizaRepository)
    {
        $this->polizaRepository = $polizaRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return Poliza::unidentified()->get();

        $data = $this->polizaRepository->index(request()->all());
        return $this->sendResponseOk($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $options = $this->polizaRepository->getOptions();
        return $this->sendResponseOk($options, 'route get poliza create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['user_created'] = auth()->user()->id;
        $validate = validator($request->all(), []);
        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        $poliza = $this->polizaRepository->create($request->all());

        return $this->sendResponseCreated(compact('poliza'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Components\FlujoEfectivo\Models\Poliza  $poliza
     * @return \Illuminate\Http\Response
     */
    public function show(Poliza $poliza)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Components\FlujoEfectivo\Models\Poliza  $poliza
     * @return \Illuminate\Http\Response
     */
    public function edit(Poliza $poliza)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Components\FlujoEfectivo\Models\Poliza  $poliza
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Poliza $poliza)
    {
        $request['user_updated'] = auth()->user()->id;
        $validate = validator($request->all(), []);
        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        // TODO: Poner condicionantes

        $poliza = $this->polizaRepository->update($poliza->id, $request->all());

        return $this->sendResponseUpdated(compact('poliza'));
    }

    public function apply(Request $request, Poliza $poliza)
    {

        $payload = [
            'user_updated' => auth()->user()->id,
            'is_applied' => true,
            'apply_date' => Carbon::now(),
            'external_id' => Uuid::uuid4()->toString()
        ];
        $poliza = $this->polizaRepository->update($poliza->id, $payload);

        return $this->sendResponseUpdated(compact('poliza'));

    }
    public function unapply(Request $request, Poliza $poliza)
    {

        $payload = [
            'user_updated' => auth()->user()->id,
            'is_applied' => false,
            'apply_date' => null,
            'external_id' => null
        ];
        $poliza = $this->polizaRepository->update($poliza->id, $payload);

        return $this->sendResponseUpdated(compact('poliza'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Components\FlujoEfectivo\Models\Poliza  $poliza
     * @return \Illuminate\Http\Response
     */
    public function destroy(Poliza $poliza)
    {
        // TODO: Poner condicionantes
        $this->polizaRepository->delete($poliza->id);
        return $this->sendResponseDeleted();
    }
}

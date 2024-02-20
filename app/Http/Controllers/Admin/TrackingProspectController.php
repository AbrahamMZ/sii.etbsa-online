<?php

namespace App\Http\Controllers\Admin;

use App\Components\Common\Models\Currency;
use App\Components\Common\Models\Estatus;
use App\Components\Common\Models\ExchangeRates;
use App\Components\Common\Models\SellerCategory;
use App\Components\File\Services\FileService;
use App\Components\Product\Models\ProductCategory;
use App\Components\RRHH\Models\Employee;
use App\Components\Tracking\Models\Prospect;
use App\Components\Tracking\Models\TrackingProspect;
use App\Components\Tracking\Models\TrackingQuote;
use App\Components\Tracking\Repositories\TrackingRepository;
use App\Components\User\Models\User;
use App\Notifications\TrackingAssigned;
use App\Notifications\TrackingNewHistorical;
use Auth;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Components\File\Models\File as FileTracking;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;

class TrackingProspectController extends AdminController
{
    /**
     * @var TrackingRepository
     */
    private $trackingRepository;
    private $trackingQuote;
    private $fileService;

    /**
     * ProspectController constructor.
     * @param TrackingRepository $trackingRepository
     */
    public function __construct(TrackingRepository $trackingRepository, FileService $fileService)
    {
        $this->trackingRepository = $trackingRepository;
        $this->fileService = $fileService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->trackingRepository->listTracking(request()->all());
        return $this->sendResponseOk($data, "list tracking ok.");
    }


    public function create()
    {
        $prospects = Prospect::all('id', 'full_name', 'phone');
        $categories = DB::table('cat_sales_category')->get(['id', 'title']);
        // $categories = auth()->user()->seller_category;
        $currency = DB::table('currency')->get(['id', 'name']);
        $agencies = DB::table('agencies')->get(['id', 'code', 'title']);
        $departments = DB::table('departments')->whereNotNull('code')->get(['id', 'title']);

        $sellers = User::whereHasMorph(
            'profiable',
            [Employee::class],
        )->whereHas(
                'groups',
                function ($query) {
                    return $query->whereIn('groups.name', ['Vendedor']);
                }
            )->with('profiable:id,name,last_name,agency_id,department_id')->get();

        return $this->sendResponseOk(
            compact(
                'prospects',
                'categories',
                'currency',
                'agencies',
                'departments',
                'sellers'
            ),
            "Create Tracking Prospect"
        );
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
            'title' => 'required|string',
            'reference' => 'required|string',
            'description_topic' => 'required|string',
            'price' => 'required|numeric',
            'agency_id' => 'required',
            'department_id' => 'required',
            'attended_by' => 'required',
            'prospect_id' => 'required',
        ], [
            'description_topic.required' => 'Motivo del seguimiento es Requerido',
            'attended_by.required' => 'Seleccione a un Vendedor',
            'reference.required' => 'Especifique una referencia',
            'title.required' => 'Especifique una Categoria',
            'agency_id.required' => 'Agencia Asignada es Requerido',
            'department_id.required' => 'A quien Corresponde es Requerido',
            'prospect_id.required' => 'El Prospecto es Requerido',
            'price.required' => 'El Valor es Requerido',
            'price.numeric' => 'El Valor debe ser un Numero Valido',
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }


        return DB::transaction(function () use ($request) {
            $request['registered_by'] = Auth::user()->id;
            $request['assigned_by'] = Auth::user()->id;
            $request['currency_id'] = $request['currency.id'];
            $currency_name = Currency::where('id', $request['currency.id'])->first();

            $date_next_lead = $request->get('date_next_tracking', Carbon::now()->addDays(15));
            if (empty($date_next_lead) || is_null($date_next_lead)) {
                $date_next_lead = Carbon::now()->addDays(15);
                $request['date_next_tracking'] = Carbon::now()->addDays(15);
            }
            /** @var Prospect $tracking */
            $created = $this->trackingRepository->create($request->all());
            $tracking = $this->trackingRepository->find($created->id);

            if (!$created) {
                return $this->sendResponseBadRequest("Failed create.");
            }

            $estatus = Estatus::where('key', Estatus::ESTATUS_ACTIVO)->first();
            $tracking->estatus()->associate($estatus);
            $tracking->save();
            $tracking->refresh();

            $tracking->historical()->create([
                'message' => 'Llamar para dar Seguimiento',
                'last_price' => $request['price'],
                'last_currency' => $currency_name->name,
                'type_contacted' => 'Llamada',
                'user_id' => $request['attended_by'],
                'date_next_tracking' => $date_next_lead,
                'last_assertiveness' => $request['assertiveness'],
            ]);

            if ($request['withQuote']) {
                $request['date_due'] = Carbon::now()->addDays(30);
                $request['category_id'] = ProductCategory::where('name', $request['title'])->first()->id;
                $request['currency_id'] = $request['currency.id'];
                $quotation
                    = $tracking->quotation()->create($request->all());
                if ($products = $request->get('products', [])) {
                    foreach ($products as $product => $value) {
                        if ($value) {
                            $quotation->products()->attach(
                                $value['id'],
                                [
                                    'price_unit' => $value['price'],
                                    'quantity' => $value['qty'],
                                    'currency' => $value['currency']['name'],
                                    'subtotal' => $value['subtotal']
                                ]
                            );
                        }
                    }
                }
                // $data = $quotation->load('tracking', 'products', 'currency');
                // $pdf = PDF::loadView('pdf.tracking_quote', compact('data'));
                // return $this->sendResponse($data, 'SHOW PDF');
                $quotation->refresh();
                // return  redirect()->route('tracking-quote.print', ['quote' => $quotation->id]);
                return $this->sendResponseCreated(compact('quotation'), 'Se Registro Nuevo Seguimiento Con su Cotizacion');
            }

            // $tracking->attended->notify(new TrackingAssigned($tracking));
            return $this->sendResponseCreated(compact('tracking'), 'Se Registro Nuevo Seguimiento');
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(TrackingProspect $tracking)
    {
        $data = [
            'detail' => $tracking->only(
                'id',
                'title',
                'reference',
                'description_topic',
                'price',
                'exchange_value',
                'first_contact',
                'assertiveness',
                'tracking_condition',
                'date_next_tracking',
                'date_won_sale',
                'date_lost_sale',
                'date_invoice',
                'invoice',
                'created_at',
            ),
            'currency' => $tracking->moneda->name,
            'agency' => $tracking->agency->title,
            'department' => $tracking->department->title,
            'owner' => $tracking->attended->id,
            'attended_by' => $tracking->attended->name,
            'attended_email' => $tracking->attended->email,
            'assigned_by' => $tracking->assigned->name,
            'registered_by' => $tracking->registered->name,
            'estatus' => $tracking->estatus->only('id', 'title', 'key'),
            'prospect' => $tracking->prospect()->with('township','customer:id,full_name')->first()
                ->only('id','full_name', 'email', 'is_moral', 'customer','customer_id','company', 'rfc', 'town', 'phone', 'township'),
            'customer' => $tracking->customer,
            'historical' => $tracking->historical->map(function ($H) {
                return [
                    'id' => $H->id,
                    'message' => $H->message,
                    'last_price' => $H->last_price,
                    'last_currency' => $H->last_currency,
                    'last_assertiveness' => $H->last_assertiveness,
                    'type_contacted' => $H->type_contacted,
                    'user' => $H->user->name,
                    'date_next_tracking' => $H->date_next_tracking,
                    'created_at' => $H->created_at,
                ];
            }),
            'files' => $tracking->files->map->only('id', 'name', 'file_path', 'file_type', 'created_at')
        ];

        if (!$tracking) {
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
    public function addHistoricalTracking(Request $request, $id)
    {

        $validate = validator($request->all(), [
            'estatus' => 'required|string',
            'message' => 'required|string',
            'currency' => 'required',
            'assertiveness' => 'required|numeric',
            'last_assertiveness' => 'required|numeric',
            'date_next_tracking' => ['required_if:estatus,"activo"|date'],
            'invoice' => ['exclude_unless:estatus,"fomalizado"|required'],
        ], [
            'message.required' => 'El mensaje es requerido',
        ]);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        DB::transaction(function () use ($id, $request) {
            $estatus = Estatus::where('key', $request['estatus'])->first();
            $tracking = $this->trackingRepository->find($id);

            $request['user_id'] = Auth::user()->id;
            $tracking->historical()->create($request->all());
            $this->trackingRepository->update($id, $request->all());
            $tracking->estatus()->associate($estatus)->save();

            // $tracking->attended->notify(new TrackingNewHistorical($tracking));
        });

        return $this->sendResponseOk([], "Seguimiento Guardado.");
    }

    public function edit(TrackingProspect $tracking)
    {
        $data = $tracking->only(
            'id',
            'price',
            'title',
            'currency',
            'reference',
            'agency_id',
            'prospect_id',
            'attended_by',
            'department_id',
            'first_contact',
            'description_topic',
            'tracking_condition',
            'currency_id',
            'date_next_tracking'
        );

        if (!$tracking) {
            return $this->sendResponseNotFound();
        }

        return $this->sendResponseOk($tracking);
    }

    public function update(Request $request, $id)
    {
        $validate = validator($request->all(), [
            'title' => 'required|string',
            'reference' => 'required|string',
            'description_topic' => 'required|string',
            'price' => 'required|numeric',
            'agency_id' => 'required',
            'department_id' => 'required',
            'attended_by' => 'required',
            'prospect_id' => 'required',
        ], [
            'description_topic.required' => 'Motivo del seguimiento es Requerido',
            'attended_by.required' => 'Seleccione a un Vendedor',
            'reference.required' => 'Especifique una referencia',
            'title.required' => 'Especifique una Categoria',
            'agency_id.required' => 'Agencia Asignada es Requerido',
            'department_id.required' => 'A quien Corresponde es Requerido',
            'prospect_id.required' => 'El Prospecto es Requerido',
            'price.required' => 'El Precio es Requerido',
            'price.numeric' => 'El Precio debe ser un Numero Valido',
        ]);


        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        DB::transaction(function () use ($id, $request) {
            $this->trackingRepository->update($id, $request->all());
        });

        return $this->sendResponseOk([], "Se actualizo el Seguimiento.");
    }


    public function resetToActive(Request $request, $id)
    {
        $updated = $this->trackingRepository->update($id, ['estatus_id' => 1]);

        if (!$updated) {
            return $this->sendResponseBadRequest("Failed update");
        }

        return $this->sendResponseOk([], "Se Asigno el Estatus a Activo");
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function resources()
    {
        if (Auth::user()->isSuperUser()) {
            $agencies = DB::table('agencies')->get(['id', 'code', 'title']);
            $departments = DB::table('departments')->whereNotNull('code')->get(['id', 'title']);
            // $categories = DB::table('cat_product_category')->get(['id', 'name']);
        } else {
            $agencies = Auth::user()->seller_agency->map(function ($i, $k) {
                return ['id' => $i->id, 'code' => $i->code, 'title' => $i->title];
            });
            $departments = Auth::user()->seller_type->map(function ($i, $k) {
                return ['id' => $i->id, 'title' => $i->title];
            });
            // $categories = Auth::user()->seller_category->map(function ($i, $k) {
            //     return ['id' => $i->id, 'name' => $i->name];
            // });
        }
        $categories = DB::table('cat_product_category')->get(['id', 'name']);

        $currency = DB::table('currency')->get(['id', 'name']);
        $prospects = Prospect::with('township')->get()->map->only(['id', 'full_name', 'email', 'company', 'rfc', 'town', 'phone', 'township']);
        $exchange_value = ExchangeRates::latest()->first()->value;
        return $this->sendResponseOk(compact('agencies', 'departments', 'prospects', 'currency', 'categories', 'exchange_value'));
    }


    public function diaryTrackings()
    {
        $d = $this->trackingRepository->diaryTracking(request()->all());
        $colors = [
            'green darken-4',
            'grey darken-1',
            'deep-purple',
        ];
        $event = [];
        foreach ($d as $tracking) {
            $event[] = [
                'id' => $tracking->id,
                'name' => 'Folio: #' . $tracking->id . ' ' . ($tracking->historical->last()->type_contacted ?? 'Sin Seguimiento'),
                'start' => Carbon::createFromDate($tracking->date_next_tracking)->toDateString(),
                'end' => Carbon::createFromDate($tracking->date_next_tracking)->toDateString(),
                'color' => $colors[$tracking->estatus->id - 1],
                'details' => $tracking->historical->last()->message ?? '',
                'timed' => true,
                'attended' => $tracking->attended->name,
                'estatus' => $tracking->estatus->title,
                'prospect' => $tracking->prospect->full_name,
                'title' => $tracking->title,
                'reference' => $tracking->reference,
            ];
        }
        return $this->sendResponseOk(compact('event'), "list tracking ok.");
    }

    public function associateCustomer(Request $request, $id)
    {
        $updated = $this->trackingRepository->update($id, ['customer_id' => $request['customer_id']]);

        if (!$updated) {
            return $this->sendResponseBadRequest("Failed update");
        }

        return $this->sendResponseOk([], "Se Asigno el Cliente Correctamente");
    }

    public function print(TrackingProspect $trackingProspect)
    {
        $data = $trackingProspect->load('prospect','prospect.township', 'assigned');
        $pdf = \PDF::loadView('pdf.quote', compact('data'));
        return $pdf->stream();
    }


    public function destroyFile(FileTracking $file)
    {

        // dd($file,Storage::disk('s3')->exists($file->path));
        try {
            $file->delete();
            if (Storage::disk('s3')->exists($file->path)) {
                Storage::disk('s3')->delete($file->path);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendResponseDeleted("Error de Servidor");
        }
        return $this->sendResponseDeleted("Archivo Eliminado");
    }

    public function attachFiles(Request $request, TrackingProspect $tracking)
    {
        // dd($request->hasFile('file'), $request->file('file'));

        if ($request->hasFile('file')) {
            $files = $request->file('file');
            $error = false;
            $errorMessage = '';
            $fileRecord = null;
            foreach ($files as $file) {
                try {
                    $fileOriginalName = $file->getClientOriginalName();
                    $filePath = $file->store('trackings/id_' . $tracking->id . '/files', 's3');

                    if (!$filePath) {
                        $this->addError("Failed to upload.", 400);
                        return false;
                    }

                    // other data
                    $ext = pathinfo(config('filesystems.disks.local.root') . '/' . $filePath, PATHINFO_EXTENSION);
                    $size = Storage::disk('s3')->getSize($filePath);
                    $type = Storage::disk('s3')->getMimetype($filePath);

                    $fileData = [
                        'original_name' => $fileOriginalName,
                        'path' => $filePath,
                        'ext' => $ext,
                        'size' => $size,
                        'type' => $type,
                    ];
                } catch (FileNotFoundException $e) {
                    $error = true;
                    $errorMessage = $e->getMessage();
                    break;
                }
                if (!$fileData) {
                    $error = true;
                    $errorMessage = $this->fileService->getErrors()->first();
                    break;
                }
                $fileRecord = $tracking->files()->create([
                    'name' => $fileData['original_name'],
                    'uploaded_by' => Auth::user()->id,
                    'file_type' => $fileData['type'],
                    'extension' => $fileData['ext'],
                    'size' => $fileData['size'],
                    'path' => $fileData['path'],
                ]);

                if (!$fileRecord) {
                    if (Storage::disk('s3')->exists($fileData['path'])) {
                        Storage::disk('s3')->delete($fileData['path']);
                    }
                    $error = true;
                    $errorMessage = "Failed to create record.";
                    break;
                }

            }
            if ($error) {
                return $this->sendResponseBadRequest($errorMessage);
            }

            return $this->sendResponseCreated();
        }
    }

}

<?php

namespace App\Http\Controllers\Admin;

use App;
use App\Components\Common\Models\Currency;
use App\Components\Product\Models\Product;
use App\Components\Product\Models\ProductCategory;
use App\Components\Product\Models\ProductModel;
use App\Components\Tracking\Models\TrackingQuote;
use App\Components\Tracking\Repositories\TrackingQuoteRepository;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class TrackingQuoteController extends AdminController
{
    private $trackingQuoteRepository;

    public function __construct(TrackingQuoteRepository $trackingQuoteRepository)
    {
        $this->trackingQuoteRepository = $trackingQuoteRepository;
    }

    public function index()
    {
        $data = $this->trackingQuoteRepository->listTrackingQuote(request()->all());
        return $this->sendResponseOk($data, "Lista de Cotizaciones");
    }


    public function create()
    {
        //
    }


    public function getProductsByCategory(Request $request, ProductCategory $product_category)
    {
        $products = $product_category->products;

        return $this->sendResponseCreated($products, 'getProducts');
    }

    public function getOptions()
    {
        $options = ProductCategory::select(['id', 'name'])->get();

        return $this->sendResponseCreated($options, 'getOptions');
    }


    public function store(Request $request)
    {

        $validate = validator($request->all(), []);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }
        $quotation = null;
        return DB::transaction(function () use ($request, $quotation) {
            $request['date_due'] = Carbon::now()->addDays(30);
            $request['currency_id'] = $request['currency.id'];

            
            $quotation = $this->trackingQuoteRepository->create($request->all());
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
            return $this->sendResponseCreated($quotation, 'Cotizacion Creada');
        });
    }

    public function show(TrackingQuote $quote)
    {
        //
    }


    public function edit(TrackingQuote $quote)
    {
        //
    }

    public function update(Request $request, TrackingQuote $quote)
    {
        $validate = validator($request->all(), []);

        if ($validate->fails()) {
            return $this->sendResponseBadRequest($validate->errors()->first());
        }

        return DB::transaction(function () use ($quote, $request) {
            $request['date_due'] = Carbon::now()->addDays(30);
            $request['currency_id'] = $request['currency.id'];
            $this->trackingQuoteRepository->update($quote->id, $request->all());
            $quote->refresh();
            $syncConcept = [];
            if ($products = $request->get('products', [])) {
                foreach ($products as $product => $value) {
                    if ($value) {
                        $pivotConcept = [
                            'price_unit' => $value['price'],
                            'quantity' => $value['qty'],
                            // 'currency' => $value['currency']['name'],
                            // 'currency' => Currency::find($value['currency_id'])->name,
                            'currency' => Currency::find($value['currency']['id'])->name,
                            'subtotal' => $value['subtotal']
                        ];
                        $syncConcept[$value['id']] = $pivotConcept;
                    }
                }
                $quote->products()->sync($syncConcept);
            }
            return $this->sendResponseCreated($quote, 'Cotizacion Actualizada');
        });
    }


    public function destroy(TrackingQuote $quote)
    {
        $quote->delete();
        return $this->sendResponseDeleted();
    }

    public function printQuote(TrackingQuote $quote)
    {
        $data = $quote->load('tracking', 'products', 'currency');
        // $pdf = \PDF::loadView('pdf.tracking_quote', compact('data'));
        // return $this->sendResponse($data, 'SHOW PDF');
        $pdf = App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadView('pdf.tracking_quote', compact('data'));
        return $pdf->stream();
    }
}
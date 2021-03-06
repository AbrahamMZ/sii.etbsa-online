<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Components\Gps\Repositories\GpsRepository;
use App\Components\Marketing\Repositories\MarketingRepository;
use App\Components\Tracking\Repositories\TrackingRepository;
use App\Components\Gps\Repositories\GpsChipsRepository;
use App\Components\Gps\Repositories\GpsGroupRepository;
use App\Exports\GpsChipExport;
use App\Exports\GpsExport;
use App\Exports\GpsGroupExport;
use App\Exports\MarketingExport;
use App\Exports\TrackingExport;

class ExportController extends AdminController
{
    /**
     * @var GpsRepository
     */
    private $gpsRepository;
    private $gpsChipsRepository;
    private $gpsGroupRepository;
    private $marketingRepository;
    private $trackingRepository;

    /**
     * FileGroupController constructor.
     * @param GpsRepository $gpsRepository
     */
    public function __construct(
        GpsRepository $gpsRepository,
        MarketingRepository $marketingRepository,
        TrackingRepository $trackingRepository,
        GpsChipsRepository $gpsChipsRepository,
        GpsGroupRepository $gpsGroupRepository

    ) {
        $this->gpsRepository = $gpsRepository;
        $this->gpsChipsRepository = $gpsChipsRepository;
        $this->gpsGroupRepository = $gpsGroupRepository;
        $this->marketingRepository = $marketingRepository;
        $this->trackingRepository = $trackingRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportGps(Request $request)
    {
        $data = $this->gpsRepository->index($request->all());

        return Excel::download(new GpsExport($data), 'gps.xlsx');
    }
    public function exportGpsChips(Request $request)
    {
        $data = $this->gpsChipsRepository->index($request->all());

        return Excel::download(new GpsChipExport($data), 'gps-chips.xlsx');
    }
    public function exportGpsGroups(Request $request)
    {
        $data = $this->gpsGroupRepository->index($request->all());

        return Excel::download(new GpsGroupExport($data), 'gps-chips.xlsx');
    }

    public function exportMarketing(Request $request)
    {
        $data = $this->marketingRepository->index($request->all());

        return Excel::download(new MarketingExport($data), 'maketing.xlsx');
    }

    public function exportTracking(Request $request)
    {
        $data = $this->trackingRepository->listTracking($request->all());

        return Excel::download(new TrackingExport($data), 'tracking.xlsx');
    }
}

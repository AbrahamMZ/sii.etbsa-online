<?php

namespace App\Components\Tracking\Repositories;

use App\Components\Core\BaseRepository;
use App\Components\Tracking\Models\TrackingProspect;
use Auth;
use Carbon\Carbon;

class TrackingRepository extends BaseRepository
{
    public function __construct(TrackingProspect $model)
    {
        parent::__construct($model);
    }

    /**
     * list all users
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]|mixed[]
     */
    public function listTracking($params)
    {
        return $this->get(
            $params,
            [
                'estatus',
                'prospect:id,full_name,company,phone,email,town,township_id',
                'agency:id,title',
                'department:id,title',
                'attended.profiable:id,name,last_name,agency_id',
                'attended.profiable.agency:id,title',
                'estatus:id,title,key',
                'moneda:id,name',
                'quotation',
            ],
            function ($query) use ($params) {
                $query->where(function ($query) use ($params) {
                    $query->filter($params)
                        ->filterByDateRange($params['dates'] ?? null, $params['estatus'] ?? null);
                })->when(Auth::user()->isSuperUser(), function ($query) {
                    return $query;
                }, function ($query) {
                    $query->where(function ($query) {
                        $query->filterForManagers(Auth::user());
                    });
                });
                return $query;
            }
        );
    }

    public function dashboardData($params)
    {
        $params['paginate'] = 'no';
        return $this->get(
            $params,
            [
                'estatus',
                'prospect:id,full_name,company,phone,email',
                'agency:id,title',
                'department:id,title',
                'attended.profiable:id,name,last_name,agency_id',
                'attended.profiable.agency:id,title',
                'estatus:id,title,key',
                'moneda:id,name',
            ],
            function ($query) use ($params) {
                $query->where(function ($query) use ($params) {
                    $query->filter($params);
                    if ($params['dates'] ?? false) {
                        $query->filterByDateRange($params['dates'] ?? null, $params['estatus'] ?? null);
                    } else {
                        $query->filterByYear($params['year'] ?? Carbon::now()->year, $params['estatus'] ?? null)
                            ->filterByMonths($params['months'] ?? null, $params['estatus'] ?? null);
                    }
                })->when(Auth::user()->isSuperUser(), function ($query) {
                    return $query;
                }, function ($query) {
                    $query->where(function ($query) {
                        $query->filterForManagers(Auth::user());
                    });
                });
                // return $query->whereYear('updated_at', $params['year'] ?? Carbon::now()->year);
                return $query;
            }
        );

    }

    public function diaryTracking($params)
    {
        return $this->get(
            ['paginate' => 'no'],
            [],
            function ($query) use ($params) {
                $query->where(function ($query) use ($params) {
                    $query->filter($params);
                });
                $query->whereHas('historical')
                    ->where(function ($query) {
                        $query->whereIn('estatus_id', [1]);
                    })
                    ->when(Auth::user()->isSuperUser(), function ($query) {
                        return $query;
                    }, function ($query) {
                    $query->where(function ($query) {
                        $query->filterForManagers(Auth::user());
                    });
                });
                return $query;
            }
        );
    }
}
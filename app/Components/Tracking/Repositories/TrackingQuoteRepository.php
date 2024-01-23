<?php

namespace App\Components\Tracking\Repositories;

use App\Components\Core\BaseRepository;
use App\Components\Tracking\Models\TrackingQuote;
use Auth;

class TrackingQuoteRepository extends BaseRepository
{
    public function __construct(TrackingQuote $model)
    {
        parent::__construct($model);
    }

    /**
     * list all users
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]|mixed[]
     */
    public function listTrackingQuote($params)
    {
        return $this->get(
            $params,
            ['products','products.category:id,name', 'currency','estatus'],
            function ($query) use ($params) {
                $query->search($params['search'] ?? '')
                    ->filter($params);
                return $query;
            }
        );
    }
}

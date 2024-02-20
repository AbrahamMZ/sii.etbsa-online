<?php

namespace App\Components\Tracking\Repositories;

use App\Components\Core\BaseRepository;
use App\Components\Tracking\Models\Prospect;

class ProspectRepository extends BaseRepository
{
    public function __construct(Prospect $model)
    {
        parent::__construct($model);
    }

    /**
     * list all users
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]|mixed[]
     */
    public function listProspect($params)
    {

        return $this->get($params, ['user', 'customer:id,full_name'], function ($q) use ($params) {
            $q->meta();
            $q->search($params['search'] ?? '');
            $q->filter($params ?? []);


            return $q;
        });
    }

    public function resource($params)
    {
        $params['paginate'] = 'no';
        return $this->get($params, [], function ($q) use ($params) {
            return $q;
        });
    }

    public function delete(int $id)
    {
        $ids = explode(',', $id);

        foreach ($ids as $id) {
            /** @var Prospect $Prospect */
            $Prospect = $this->model->find($id);

            if (!$Prospect) {
                return false;
            }
            ;

            $Prospect->delete();
        }

        return true;
    }
}

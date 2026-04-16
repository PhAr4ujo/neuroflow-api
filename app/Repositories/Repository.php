<?php

namespace App\Repositories;

use App\Repositories\Interfaces\IRepository;

abstract class Repository implements IRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = app($this->model());
    }

    public function listRecords(array $filters, int $paginationAmount)
    {
        $query = $this->model->query();

        foreach ($filters as $name => $value) {
            if ($value != null && $name != 'pagination_amount') {
                $query = $query->where($name, $value);
            }
        }

        return $query->paginate($paginationAmount);
    }

    public function insert($data)
    {
        return $this->model->create($data);
    }

    public function edit($id, $data)
    {
        $model = $this->model->find($id);

        return $model->update($data);
    }

    public function delete($id)
    {
        $model = $this->model->find($id);

        return $model->delete();
    }

    public function getAll()
    {
        return $this->model->get();
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    abstract public function model();
}

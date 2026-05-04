<?php

namespace App\Services;

use App\Repositories\Interfaces\IRepository;
use App\Services\Interfaces\IService;

abstract class Service implements IService
{
    protected IRepository $repository;

    public function __construct(IRepository $repository)
    {
        $this->repository = $repository;
    }

    public function insert($data)
    {
        return $this->repository->insert($data);
    }

    public function edit($id, $data)
    {
        return $this->repository->edit($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function listRecords(array $filters, int $paginationAmount = 15)
    {
        return $this->repository->listRecords($filters, $paginationAmount);
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function find(int $id)
    {
        return $this->repository->find($id);
    }
}

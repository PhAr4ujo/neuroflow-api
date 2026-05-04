<?php

namespace App\Repositories\Interfaces;

interface IRepository
{
    public function create($data);
    public function listRecords(array $filters, int $paginationAmount);
    public function insert($data);
    public function edit($id, $data);
    public function delete($id);
    public function getAll();
    public function find(int $id);
}

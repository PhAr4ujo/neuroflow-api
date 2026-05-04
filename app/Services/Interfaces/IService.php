<?php

namespace App\Services\Interfaces;

interface IService
{
    public function insert($data);
    public function edit($id, $data);
    public function delete($id);
    public function listRecords(array $filters, int $paginationAmount = 15);
    public function getAll();
    public function find(int $id);
}

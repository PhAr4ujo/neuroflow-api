<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IModeRepository extends IRepository
{
    public function paginate(int $paginationAmount): LengthAwarePaginator;
}

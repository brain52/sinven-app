<?php
namespace App\Repositories\Contracts;

interface MaintenanceRepositoryInterface
{
    public function create(array $data);
    public function update($id, array $data);
}
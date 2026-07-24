<?php
namespace App\Repositories\Contracts;

interface BorrowingRepositoryInterface
{
    public function create(array $data);
    public function getActiveBorrowingByItem($itemId);
    public function update($id, array $data);
}
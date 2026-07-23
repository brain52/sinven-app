<?php
namespace App\Repositories\Contracts;

interface ItemRepositoryInterface
{
    public function getAllForUser($user);
    public function countByCategoryAndYear($categoryId, $year);
    public function create(array $data);
}
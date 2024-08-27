<?php

namespace App\Repositories\Interfaces;

interface AbstractRepositoryInterface
{
    public function all();

    public function find(int $id);

    public function findByUUID(string $uuid);

    public function findBy(array $array);

    public function findByOr(array $criteria, $orderBy=[], $limit=null);

    public function findFirstBy(array $array);

    public function findByOrFirst(array $criteria, $orderBy=[]);

    public function update($data=[]);
}

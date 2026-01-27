<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use App\Repositories\Interfaces\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Application $app)
    {
        $this->model = $app->make($this->getModel());
    }

    abstract protected function getModel(): string;

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?object
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): object
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): object
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }
}
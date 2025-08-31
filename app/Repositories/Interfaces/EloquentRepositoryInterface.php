<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface EloquentRepositoryInterface
{
    /**
     * Get all models
     *
     * @param  array|string[]  $columns
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Count models
     */
    public function count(): int;

    /**
     * Get all models
     *
     * @param  array|string[]  $columns
     */
    public function getByCondition(array $condition, array $relations = [], array $columns = ['*']): Collection;

    /**
     * Find model by id
     *
     * @param  array|string[]  $columns
     */
    public function findById(
        int $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model;

    /**
     * Create a model
     */
    public function create(array $payload): ?Model;

    /**
     * Update existing model
     */
    public function update(int $modelId, array $payload): bool;

    /**
     * Delete model by id
     */
    public function deleteById(int $modelId): bool;
}

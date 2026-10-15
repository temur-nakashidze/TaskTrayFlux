<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasPosition
{
    public static function bootHasPosition(): void
    {
        // New records are appended to the end of their group.
        static::creating(function ($model) {
            $column = $model->getPositionColumn();

            if ($model->getAttribute($column) === null) {
                $max = $model->newPositionQuery()->max($column);

                $model->setAttribute($column, $max === null ? 0 : $max + 1);
            }
        });
    }

    public function getPositionColumn(): string
    {
        return 'position';
    }

    public function positionGroupColumns(): array
    {
        return [];
    }

    public function scopeOrdered(Builder $query, string $direction = 'desc'): Builder
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy($this->qualifyColumn($this->getPositionColumn()), $direction)
            ->orderBy($this->getQualifiedKeyName(), $direction);
    }

    public function moveToPosition(int $position, string $direction = 'desc'): static
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $this->getConnection()->transaction(function () use ($position, $direction) {
            $column = $this->getPositionColumn();

            // id => stored position, in the SAME order the UI displays.
            $current = $this->newPositionQuery()
                ->ordered($direction)
                ->lockForUpdate()
                ->pluck($column, $this->getKeyName())
                ->all();

            $ids = array_values(
                array_filter(
                    array_keys($current),
                    fn ($id) => (string) $id !== (string) $this->getKey(),
                ),
            );

            $index = max(0, min($position, count($ids)));

            array_splice($ids, $index, 0, [$this->getKey()]);

            if ($direction === 'desc') {
                // visually first item must receive the highest position
                $positions = array_reverse($ids);
                $newOwn = count($ids) - 1 - $index;
            } else {
                // visually first item receives position 0
                $positions = $ids;
                $newOwn = $index;
            }

            $this->writePositions($positions, $current);

            $this->setAttribute($column, $newOwn);
            $this->syncOriginalAttribute($column);
        });

        return $this;
    }

    /** Query that matches every record in the same group as this one (polymorphic columns work too). */
    protected function newPositionQuery(): Builder
    {
        $query = $this->newQuery();

        foreach ($this->positionGroupColumns() as $col) {
            $value = $this->getAttribute($col);

            $value === null
                ? $query->whereNull($col)
                : $query->where($col, $value);
        }

        return $query;
    }

    /**
     * Give every id its new index, touching only rows whose position really changes,
     * with a single UPDATE ... CASE statement.
     *
     * @param  array<int, int|string>  $orderedIds  ids in the desired order
     * @param  array<int|string, int|null>  $current  id => stored position
     */
    protected function writePositions(array $orderedIds, array $current): void
    {
        $cases = [];
        $ids = [];

        foreach ($orderedIds as $index => $id) {
            if (isset($current[$id]) && (int) $current[$id] === $index) {
                continue;
            }

            $cases[] = 'WHEN ? THEN '.(int) $index; // $index is our own int, safe to inline
            $ids[] = $id;
        }

        if ($ids === []) {
            return;
        }

        $connection = $this->getConnection();
        $grammar = $connection->getQueryGrammar();
        $keyCol = $grammar->wrap($this->getKeyName());

        $sql = sprintf(
            'UPDATE %s SET %s = CASE %s %s END WHERE %s IN (%s)',
            $grammar->wrapTable($this->getTable()),
            $grammar->wrap($this->getPositionColumn()),
            $keyCol,
            implode(' ', $cases),
            $keyCol,
            implode(',', array_fill(0, count($ids), '?')),
        );

        $connection->update($sql, [...$ids, ...$ids]);
    }
}

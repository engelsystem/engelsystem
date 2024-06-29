<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api\Resources;

use Engelsystem\Models\BaseModel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Stringable;

/** @phpstan-consistent-constructor */
abstract class BasicResource implements Arrayable, Jsonable, Stringable
{
    public function __construct(protected BaseModel | Collection $model)
    {
    }

    /**
     * @param iterable|Collection|BaseModel[]|Collection[] $data
     */
    public static function collection(iterable $data): Collection
    {
        $collection = new Collection();
        foreach ($data as $item) {
            $collection->add(new static($item));
        }
        return $collection;
    }

    public function toArray(): array
    {
        return $this->model->toArray();
    }

    public static function toIdentifierArray(array | Arrayable $data): array
    {
        $data = $data instanceof Arrayable ? $data->toArray() : $data;
        $identifier = ['id' => $data['id']];
        if (array_key_exists('name', $data)) {
            $identifier['name'] = $data['name'];
        }
        return $identifier;
    }

    /**
     * @param int $options
     */
    public function toJson($options = 0): string // phpcs:ignore
    {
        return json_encode($this->toArray(), $options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}

<?php

namespace App\Support;

use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\Resource\Item as FractalItem;
use App\Transformers\EmptyTransformer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Contracts\Pagination\Paginator as IlluminatePaginator;

class Transform
{
    /**
     * Fractal manager.
     *
     * @var \League\Fractal\Manager
     */
    private $fractal;

    /**
     * Indicates if eager loading is enabled.
     *
     * @var bool
     */
    protected $eagerLoading = true;

    /**
     * Create a new class instance.
     */
    public function __construct(Manager $fractal, $eagerLoading = true)
    {
        $this->fractal = $fractal;
        $this->eagerLoading = $eagerLoading;
    }

    /**
     * Transform a collection of data.
     *
     * @param  mixed                    $data
     * @param  TransformerAbstract|null $transformer
     * @param  array                    $meta
     *
     * @return array
     */
    public function collection($data, TransformerAbstract $transformer = null, array $meta = [])
    {
        $transformer = $transformer ?: $this->fetchDefaultTransformer($data);

        if ($this->shouldEagerLoad($data)) {
            $eagerLoads = $this->mergeEagerLoads($transformer, $this->fractal->getRequestedIncludes());

            $data->load($eagerLoads);
        }

        $collection = (new FractalCollection($data, $transformer))->setMeta($meta);

        if ($data instanceof LengthAwarePaginator) {
            $collection->setPaginator(new IlluminatePaginatorAdapter($data));
        }

        return $this->fractal->createData($collection)->toArray();
    }

    /**
     * Get includes as their array keys for eager loading.
     *
     * @param \League\Fractal\TransformerAbstract $transformer
     * @param string|array                        $requestedIncludes
     *
     * @return array
     */
    protected function mergeEagerLoads($transformer, $requestedIncludes)
    {
        $includes = array_merge($requestedIncludes, $transformer->getDefaultIncludes());

        $eagerLoads = [];

        foreach ($includes as $key => $value) {
            $eagerLoads[] = is_string($key) ? $key : $value;
        }

        return $eagerLoads;
    }

    /**
     * Eager loading is only performed when the response is or contains an
     * Eloquent collection and eager loading is enabled.
     *
     * @param mixed $response
     *
     * @return bool
     */
    protected function shouldEagerLoad($response)
    {
        if ($response instanceof IlluminatePaginator) {
            $response = $response->getCollection();
        }

        return $response instanceof EloquentCollection && $this->eagerLoading;
    }

    /**
     * Transform a single data.
     *
     * @param  mixed                    $data
     * @param  TransformerAbstract|null $transformer
     *
     * @return array
     */
    public function item($data, TransformerAbstract $transformer = null)
    {
        $transformer = $transformer ?: $this->fetchDefaultTransformer($data);

        return $this->fractal->createData(
            new FractalItem($data, $transformer)
        )->toArray();
    }

    /**
     * Tries to fetch a default transformer for the given data.
     *
     * @param  mixed $data
     *
     * @return \League\Fractal\TransformerAbstract|null
     */
    protected function fetchDefaultTransformer($data)
    {
        if (($data instanceof LengthAwarePaginator || $data instanceof Collection) && $data->isEmpty()) {
            return new EmptyTransformer();
        }

        $classname = $this->getClassnameFrom($data);

        if ($this->hasDefaultTransformer($classname)) {
            $transformer = config('api.transformers.'.$classname);
        } else {
            $classBasename = class_basename($classname);

            if (!class_exists($transformer = "App\\Transformers\\Admin\\{$classBasename}Transformer")) {
                throw new \Exception("No transformer for {$classname}");
            }
        }

        return new $transformer;
    }

    /**
     * Check if the class has a default transformer.
     *
     * @param  string $classname
     *
     * @return bool
     */
    protected function hasDefaultTransformer($classname)
    {
        return ! is_null(config('api.transformers.'.$classname));
    }

    /**
     * Get the class name from the given object.
     *
     * @param  object $object
     *
     * @return string
     */
    protected function getClassnameFrom($object)
    {
        if ($object instanceof LengthAwarePaginator or $object instanceof Collection) {
            return get_class(array_first($object));
        }

        if (!is_string($object) && !is_object($object)) {
            throw new \Exception("No transformer of \"{$object}\"found.");
        }

        return get_class($object);
    }
}

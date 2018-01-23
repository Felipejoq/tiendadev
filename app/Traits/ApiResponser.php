<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

trait ApiResponser{

    private function successResponse($data,$code){

        return response()->json($data,$code);

    }

    protected function errorResponse($menssage, $code){

        return response()->json(['error' => $menssage,'code' => $code],$code);

    }

    protected function showAll(Collection $collection, $code = 200){

        if ($collection->isEmpty()){
            return $this->successResponse(['data' => $collection], $code);
        }

        /**
         * Como tenemos la certeza de que todos los recursos de la coleción serán iguales,
         * obtenemos el atributo del primer elemento de la colección.
         */
        $transformer = $collection->first()->transformer;

        $collection = $this->filterData($collection, $transformer);

        $collection = $this->sortData($collection, $transformer);

        //Paginar la colección una vez filtrada pero antes de ser ordenada y transformada.
        $collection = $this->paginate($collection);

        $collection = $this->transformData($collection, $transformer);

        return $this->successResponse($collection,$code);

    }

    protected function showOne(Model $instance, $code = 200){

        $transformer = $instance->transformer;

        $data = $this->transformData($instance, $transformer);

        return $this->successResponse($data, $code);

    }

    protected function showMessage($message, $code = 200){

        return $this->successResponse(['data' => $message], $code);

    }

    protected function filterData(Collection $collection, $transformer){

        foreach (request()->query() as $query => $value ){
            $attribute = $transformer::originalAttribute($query);

            if(isset($attribute,$value)){
                $collection = $collection->where($attribute, $value);
            }

        }

        return $collection;
    }

    protected function sortData(Collection $collection, $transformer){

        if (request()->has('sort_by')){
            $attribute = $transformer::originalAttribute(request()->sort_by);
            $collection = $collection->sortBy->{$attribute};
        }

        return $collection;
    }

    protected function paginate(Collection $collection){

        $rules = [
            'per_pages' => 'integer|min:2|max:50'
        ];

        Validator::validate(request()->all(), $rules);

        $page = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 15;

        if (request()->has('per_pages')){
            $perPage = (int) request()->per_pages;
        }

        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends(request()->all());

        return $paginated;
    }

    protected function transformData($data, $transformer){
        $transformation = fractal($data, new $transformer);

        return $transformation->toArray();

    }

}
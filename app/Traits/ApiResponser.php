<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    protected function transformData($data, $transformer){
        $transformation = fractal($data, new $transformer);

        return $transformation->toArray();

    }

}
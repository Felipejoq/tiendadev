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

        return $this->successResponse(['data' => $collection],$code);

    }

    protected function showOne(Model $instance, $code = 200){

        return $this->successResponse(['data' => $instance], $code);

    }

}
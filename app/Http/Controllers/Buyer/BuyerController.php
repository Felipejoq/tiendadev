<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BuyerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Selecciona sólo a los compradores que tengan transacciones y los devuelve.
        $compradores = Buyer::has('transactions')->get();

        // Usando el método shoAll del controlador general se devuelve la respuesta en formato Json.
        return $this->showAll($compradores);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){

        /**
         * Para buscar un comprador específico, se hace lo mismo, se buscan los compradores
         * con trasacciones y dentro de ese grupo se busca el específico. Si no tiene trasacciones no es cliente
         * por lo tanto no se encuentra.
         * Ocupando el método showOne del controlador general se entrega la respuesta en formato Json.
         */
        $comprador = Buyer::has('transactions')->findOrFail($id);

        return $this->showOne($comprador);

    }


}

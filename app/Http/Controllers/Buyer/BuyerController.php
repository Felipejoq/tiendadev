<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use App\Http\Controllers\ApiController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class BuyerController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('scope:read-general')->only('show');
        $this->middleware('can:view,buyer')->only('show');

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->allowAdminActions();

        // Selecciona sólo a los compradores que tengan transacciones y los devuelve.
        $compradores = Buyer::has('transactions')->get();

        // Usando el método shoAll del controlador general se devuelve la respuesta en formato Json.
        return $this->showAll($compradores);
    }


    /**
     * Display the specified resource.
     *
     * @param Buyer $buyer
     * @return \Illuminate\Http\Response
     */
    public function show(Buyer $buyer){

        /**
         * Para buscar un comprador específico, se hace lo mismo, se buscan los compradores
         * con trasacciones y dentro de ese grupo se busca el específico. Si no tiene trasacciones no es cliente
         * por lo tanto no se encuentra.
         * Ocupando el método showOne del controlador general se entrega la respuesta en formato Json.
         */

        return $this->showOne($buyer);

    }


}

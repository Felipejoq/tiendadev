<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SellerController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('scope:read-general')->only('show');
        $this->middleware('can:view,seller')->only('show');

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->allowAdminActions();

        /**
         * Un vendedor es alquel que tiene al menos un producto,
         * por lo tanto se devuelven solo los que tengan productos asociados
         * y se devuelve con el método shoAll del controlador general.
         */
        $vendedores = Seller::has('products')->get();

        return $this->showAll($vendedores);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Seller $seller)
    {
        /**
         * Para buscar un vendedor en específico, se hace una búsqueda dentro de los vendedores
         * que tengan al menos un producto, luego se hace la búsqueda por ID.
         * Se devuelve una respuesta mediante el controlador general y su método showOne.
         */

        return $this->showOne($seller);
    }

}

<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Product;
use App\Transaction;
use App\Transformers\TransactionTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('transform.input:'. TransactionTransformer::class)->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Product $product
     * @param User $buyer
     * @return void
     */
    public function store(Request $request, Product $product, User $buyer)
    {

        $rules = [
            'quantity' => 'required|min:1',
        ];

        $this->validate($request,$rules);

        if($buyer->id == $product->seller_id){
            return $this->errorResponse('El vendedor debe ser diferente al comprador',409);
        }

        if (!$buyer->esVerificado()){
            return $this->errorResponse('El comprador debe ser un usuario verificado.', 409);
        }

        if (!$product->seller->esVerificado()){
            return $this->errorResponse('El vendedor debe ser un usuario verificado.', 409);
        }

        if (!$product->estaDisponible()){
            return $this->errorResponse('El producto para esta transacción no está disponible.', 409);
        }

        if ($product->quantity < $request->quantity){
            return $this->errorResponse('No hay suficientes productos para esta transacción', 409);
        }

        return DB::transaction(function () use ($request, $product, $buyer){
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
               'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);

            return $this->showOne($transaction,201);
        });

    }
}

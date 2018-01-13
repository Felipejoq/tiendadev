<?php

namespace App\Http\Controllers\Product;

use App\Category;
use App\Http\Controllers\ApiController;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product)
    {
        $categories = $product->categories;

        return $this->showAll($categories);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Product $product
     * @param Category $category
     * @return void
     */
    public function update(Request $request, Product $product, Category $category)
    {

        /**
         * En esta opción podemos ocupar cualqueira de estos tres métodos:
         * Ya que vamos a añadir un producto a una categoría.
         * sync: Reemplaza todas las categorías que tiene un producto por la categoría que se envía.
         * attach: Adjunta la nueva categoría envíada, pero si se envía de nuevo la misma la vuelve a agregar.
         * syncWithoutDetaching: Agrega una nueva categoría siempre y cuando ya no esté agregada.
         */
        $product->categories()->syncWithoutDetaching([$category->id]);

        $categories = $product->categories;

        return $this->showAll($categories);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, Category $category)
    {
        if(!$product->categories()->find($category->id)){
            return $this->errorResponse('La categoría especifcada no es una categoría de este producto', 404);
        }

        $product->categories()->detach([$category->id]);

        return $this->showAll($product->categories);

    }
}

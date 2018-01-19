<?php

namespace App;

use App\Scopes\BuyerScope;
use App\Transformers\BuyerTransformer;

class Buyer extends User{

    protected static function boot(){
        parent::boot();

        static::addGlobalScope(new BuyerScope);
    }

    public $transformer = BuyerTransformer::class;

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }



}

<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApiController extends Controller
{
    use ApiResponser;


    public function __construct()
    {

        $this->middleware('auth:api');

    }

    /**
     * @throws AuthorizationException
     */
    protected function allowAdminActions(){
        if (Gate::denies('admin-actions')){
            throw new AuthorizationException('Esta acción no es permitida');
        }
    }

}

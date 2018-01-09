<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class UserController
 * @package App\Http\Controllers\User
 * Este controlador tiene por función manejar el negocio con los usuarios:
 * pasando por los métodos de crear, editar, eliminar y ver usuarios registrados en el sistema.
 */

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        /**
         * Usuarios son todos los registrados en el sistema, tengan estos el rol de vendedores o compradores.
         */
        $usuarios = User::all();

        return $this->showAll($usuarios);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Reclas que definen cuáles son los campos obligatorios para la creación de un usuario.
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ];

        // Validamos que la petición (Request) cumpla las reglas, sino se entrega una excepción.
        $this->validate($request,$rules);

        //Guardamos todos los campos que vienen en el request en un array.
        $campos = $request->all();
        $campos['password'] = bcrypt($request->password); //Encriptamos la contraseña.
        $campos['verified'] = User::USUARIO_NO_VERIFICADO; //Asignamos usuario no verificado.
        $campos['verification_token'] = User::generarVerificationToken(); //Token para que se verifique posteriomente.
        $campos['admin'] = User::USUARIO_REGULAR; //Le entregamos el rol de usuario normal, no admin.

        //Se crea el usuario.
        $usuario = User::create($campos);

        //Se informa mediante showOne del controlador general que el usuario fue creado.
        return $this->showOne($usuario);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //$usuario = User::findOrFail($user);

        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        $user = User::findOrFail($id);

        $rules = [
            'email' => 'email|unique:users,email,'.$user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:' . User::USUARIO_ADMINISTRADOR.','.User::USUARIO_REGULAR,
        ];

        $this->validate($request, $rules);

        if ($request->has('name')){
            $user->name = $request->name;
        }

        if ($user->email != $request->email && $request->has('email')){
            $user->verified = User::USUARIO_NO_VERIFICADO;
            $user->verification_token = User::generarVerificationToken();
            $user->email = $request->email;
        }

        if ($request->has('password')){
            $user->password = bcrypt($request->password);
        }

        if ($request->has('admin')){
            if (!$user->esVerificado()){
                return $this->errorResponse('Unicamente los usuarios verificados pueden cambiar su valor de administrador',409);
            }

            $user->admin = $request->admin;
        }


        if (!$user->isDirty()){
            return $this->errorResponse('Debe incluir al menos un campo distinto para editar',422);
        }

        $user->save();

        return $this->showOne($user);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){

        $user = User::findOrFail($id);

        $user->delete();

        return $this->showOne($user);

    }
}
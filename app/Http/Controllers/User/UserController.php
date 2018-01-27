<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Mail\UserCreated;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

/**
 * Class UserController
 * @package App\Http\Controllers\User
 * Este controlador tiene por función manejar el negocio con los usuarios:
 * pasando por los métodos de crear, editar, eliminar y ver usuarios registrados en el sistema.
 */

class UserController extends ApiController
{

    public function __construct()
    {
        $this->middleware('client.credentials')->only(['store', 'resend']);
        $this->middleware('auth:api')->except(['store','verify', 'resend']);
        $this->middleware('transform.input:'. UserTransformer::class)->only(['store','update']);

        $this->middleware('scope:manage-account')->only('show','update');
        $this->middleware('can:view,user')->only('show');
        $this->middleware('can:delete,user')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $this->allowAdminActions();

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
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, User $user){


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
            $this->allowAdminActions();

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
     * @param  User $user
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(User $user){

        $user->delete();

        return $this->showOne($user);

    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify($token){

        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::USUARIO_VERIFICADO;

        $user->verification_token = null;

        $user->save();

        return $this->showMessage('La cuenta ha sido verificada');
    }

    /**
     * @param User $user
     */
    public function resend(User $user){
        if ($user->esVerificado()){
            return $this->errorResponse('Este usuario ya ha sido verificado', 409);
        }

        retry(5, function() use ($user){
            Mail::to($user)->send(new UserCreated($user));
        }, 100);

        return $this->showMessage('El correo de verificación fue enviado nuevamente.');
    }
}

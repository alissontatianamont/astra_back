<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $users = User::where('estado_eliminar', 1)->get();
        $usersArray = $users->toArray();
        return response()->json($usersArray, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fechaContratacion = Carbon::createFromFormat('d/m/Y', $request->fecha_contratacion)->format('Y-m-d');
        $file = $request->file("avatar");
    
        if ($file) {
            $uploadPath = "images/profile";
            $originalName = $request->cedula . '_' . $file->getClientOriginalName();
            $file->move($uploadPath, $originalName);
        } else {
            $originalName = null;
        }
        $user = new User();
        $user->nombre_usuario = $request->nombre_usuario;
        $user->email = $request->email;
        $user->password = bcrypt($request->contrasena);
        $user->estado_usuario = $request->estado_usuario;
        $user->cedula = $request->cedula;
        $user->rol = $request->rol;
        $user->fecha_contratacion = $fechaContratacion;
        $user->telefono = $request->telefono;
        $user->avatar = $originalName;
        $user->save();
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'data' => $user,
            'message' => "Registro Agregado Correctamente!",
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }


    public function getProfileImage($filename)
    {
        \Log::info("Acceso a la imagen: " . $filename);
    
        $path = public_path('images/profile/' . $filename);
    
        if (!File::exists($path)) {
            return response()->json(['message' => 'Imagen no encontrada'], 404);
        }
    
        // Verificar si el usuario está autenticado
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
    
        return response()->json(['url' => asset('images/profile/' . $filename)], 200);
    }


    /**
     * Display the specified resource.
     */
    public function show($usuario_id)
    {
        $user = User::find($usuario_id);
        if (!empty($user)) {
            return response()->json($user, 200);
        } else {
            return response()->json([
                'message' => 'registro no encontrado'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $usuario_id)
    {
        $fechaContratacion = Carbon::createFromFormat('d/m/Y', $request->fecha_contratacion)->format('Y-m-d');
        $users = User::find($usuario_id);
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $uploadPath = "images/profile";
            $originalName = $request->cedula . '_' . $file->getClientOriginalName();
            $file->move($uploadPath, $originalName);
            $users->avatar = $originalName;
        }
        $users->nombre_usuario = $request->nombre_usuario;
        $users->email = $request->email;
        if ($request->has('contrasena') && $request->contrasena !== 'undefined') {
            $users->password = bcrypt($request->contrasena);
        }
        $users->estado_usuario = $request->estado_usuario;
        $users->cedula = $request->cedula;
        $users->rol = $request->rol;
        $users->fecha_contratacion = $fechaContratacion;
        $users->telefono = $request->telefono;
        $users->save();
        return response()->json([
            "message" => "Registro actualizado Correctamente !"
        ]);
    }
    public function updateProfile(Request $request, string $usuario_id)
    {
        $users = User::find($usuario_id);
    
        // Verifica si el usuario existe
        if (!$users) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
    
        // Manejo de la actualización de campos permitidos
        $fieldsToUpdate = ['nombre_usuario', 'email', 'cedula', 'telefono'];
    
        foreach ($fieldsToUpdate as $field) {
            if ($request->has($field)) {
                $users->$field = $request->$field;
            }
        }
    
        // Validación de la contraseña
        if ($request->has('password') && !empty($request->password)) {
                $users->password = bcrypt($request->password);
        }
    
        // Guardar cambios
        $users->save();
    
        return response()->json([
            "message" => "Registro actualizado correctamente!"
        ]);
    }
    
    
    

    public function login(Request $request){
        if(!Auth::attempt($request->only('email','password'))){
            return response()->json(['message'=>'no autorizado'],401);
        }
        $user = User::where('email',$request['email'])->where('estado_eliminar', 1)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->
        json([
            'message' => 'ingreso correcto',
            'accessToken'=> $token,
            'token_type'=>'Bearer',
            'user' =>$user
        ]);
    }
    public function logout(){
        auth()->user()->tokens()->delete();
        return[
            'message' => 'se ha deslogueado correctamente :) vuelva pronto'
        ];
    }
    /**
     * Remove the specified resource from storage.
     */
    public function delete($usuario_id)
    {
        $user = User::find($usuario_id);
        if ($user) {
            // Actualizar el campo estado_eliminar a 0
            $user->estado_eliminar = 0;
            $user->save();
            
            return response()->json([
                "message" => "Registro actualizado correctamente, estado_eliminar set a 0."
            ]);
        } else {
            return response()->json([
                "message" => "Usuario no encontrado."
            ], 404);
        }
    }
    public function getDrivers(){
        $users = User::where('estado_eliminar', 1)->where('rol', 1)->get();
        $usersArray = $users->toArray();
        return response()->json($usersArray, 200);
    }
}

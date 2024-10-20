<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{

    protected $usersModel;

    public function __construct() {
        $this->usersModel = new User();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return $this->usersModel->getUsers();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Formatear la fecha de contratación
        $fechaContratacion = Carbon::createFromFormat('d/m/Y', $request->fecha_contratacion)->format('Y-m-d');
        
        // Manejo del archivo avatar
        $file = $request->file("avatar");
        $originalName = 'default-avatar.png'; // Valor predeterminado en caso de que no se suba imagen
    
        if ($file) {
            // Definir la carpeta de almacenamiento
            $uploadPath = 'profiles';
    
            // Generar el nombre del archivo con la cédula y el nombre original del archivo
            $originalName = $request->cedula . '_' . $file->getClientOriginalName();
            
            // Almacenar el archivo en la carpeta "profiles" dentro de storage/app
            $file->storeAs($uploadPath, $originalName);
        }
    
        // Guardar el usuario en la base de datos
        $user = $this->usersModel->saveUser($request, $originalName, $fechaContratacion);
    
        // Generar el token de autenticación
        $token = $this->usersModel->createToken('auth_token')->plainTextToken;
    
        // Responder con los datos del usuario y el token
        return response()->json([
            'data' => $user,
            'message' => "Registro Agregado Correctamente!",
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    


    public function getProfileImage($filename)
    {
        $path = storage_path('app/profiles/' . $filename);
    
        if (!File::exists($path)) {
            return response()->json(['message' => 'Imagen no encontrada'], 404);
        }
    
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
    
        // Devuelve la imagen como un archivo
        return response()->file($path);
    }
    
    
    


    /**
     * Display the specified resource.
     */
    public function show($usuario_id)
    {
        $user = $this->usersModel->getUser($usuario_id);
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
        // Formatear la fecha de contratación
        $fechaContratacion = Carbon::createFromFormat('d/m/Y', $request->fecha_contratacion)->format('Y-m-d');
    
        // Verificar si hay archivo de avatar para subir
        $avatar = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            // Definir la carpeta de almacenamiento en "profiles"
            $uploadPath = 'profiles';
            
            // Generar el nombre del archivo con la cédula y el nombre original del archivo
            $originalName = $request->cedula . '_' . $file->getClientOriginalName();
            
            // Almacenar el archivo en la carpeta "profiles" dentro de storage/app
            $file->storeAs($uploadPath, $originalName);
            
            // Asignar el nombre del archivo almacenado al avatar
            $avatar = $originalName;
        }
    
        // Lógica de actualización en el modelo
        $this->usersModel->updateUser($usuario_id, $request, $fechaContratacion, $avatar);
    
        return response()->json([
            "message" => "Registro actualizado Correctamente !"
        ]);
    }
    
    



    public function updateProfile(Request $request, string $usuario_id)
    {
        // Lógica de actualización en el modelo
        $updateResult = $this->usersModel->updateProfileData($usuario_id, $request);
    
        if (!$updateResult) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
    
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
        $findUserRelatedTravels = $this->usersModel->findUserRelatedTravels($usuario_id);
         if ($user ) {
            if($findUserRelatedTravels == 0){
                $user->estado_eliminar = 0;
                $user->save();
                
                return response()->json([
                    "message" => "Registro actualizado correctamente, estado_eliminar set a 0.",
                    "status" => 1
                ]);
            }else{
                return response()->json([
                    "message" => "Usuario no se puede eliminar, tiene viajes asociados.",
                    "status" => 0
                ]);
            }
           
        } else {
            return response()->json([
                "message" => "Usuario no encontrado y/o no se puede eliminar.",
                "status" => 2
            ]);
        }
    }
    public function getDrivers(){
        $users = User::where('estado_eliminar', 1)->where('rol', 1)->get();
        $usersArray = $users->toArray();
        return response()->json($usersArray, 200);
    }
}

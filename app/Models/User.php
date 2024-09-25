<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'usuarios';
    public $timestamps = false;
    protected $primaryKey = 'usuario_id';
    protected $fillable = [
        'nombre_usuario',
        'email',
        'contrasena',
        'estado_usuario',
        'fecha_creacion',
        'telefono',
        'cedula',
        'rol',
        'avatar',
        'estado_eliminar'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'contrasena',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'contrasena' => 'hashed',
    ];



    public function getUsers(){
        $users = $this->where('estado_eliminar', 1)->get();
        $usersArray = $users->toArray();
        return response()->json($usersArray, 200);
    }


    public function saveUser($request, $originalName, $fechaContratacion){
        $this->nombre_usuario = $request->nombre_usuario;
        $this->email = $request->email;
        $this->password = bcrypt($request->contrasena);
        $this->estado_usuario = $request->estado_usuario;
        $this->cedula = $request->cedula;
        $this->rol = $request->rol;
        $this->fecha_contratacion = $fechaContratacion;
        $this->telefono = $request->telefono;
        $this->avatar = $originalName;
        $this->save();
    }

    public function getUser($usuario_id){
        $user = $this->find($usuario_id);
        return $user;
    }
    public function updateUser($usuario_id, $request, $fechaContratacion, $avatar = null)
{
    $user = self::find($usuario_id);
    if ($avatar) {
        $user->avatar = $avatar;
    }

    $user->nombre_usuario = $request->nombre_usuario;
    $user->email = $request->email;

    // Si la contraseña es proporcionada, actualízala
    if ($request->has('contrasena') && $request->contrasena !== 'undefined') {
        $user->password = bcrypt($request->contrasena);
    }

    $user->estado_usuario = $request->estado_usuario;
    $user->cedula = $request->cedula;
    $user->rol = $request->rol;
    $user->fecha_contratacion = $fechaContratacion;
    $user->telefono = $request->telefono;

    $user->save();
}


public function updateProfileData($usuario_id, $request)
{
    $user = self::find($usuario_id);

    // Verifica si el usuario existe
    if (!$user) {
        return false;
    }

    // Manejo de la actualización de campos permitidos
    $fieldsToUpdate = ['nombre_usuario', 'email', 'cedula', 'telefono'];

    foreach ($fieldsToUpdate as $field) {
        if ($request->has($field)) {
            $user->$field = $request->$field;
        }
    }

    // Validación de la contraseña
    if ($request->has('password') && !empty($request->password)) {
        $user->password = bcrypt($request->password);
    }

    // Guardar cambios
    $user->save();

    return true;
}


}

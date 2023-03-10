<?php

namespace App\Models;

use File;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'bio', 'role', 'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /*
    |------------------------------------------------------------------------------------
    | Validations
    |------------------------------------------------------------------------------------
    */
    public static function rules($update = false, $id = null, $client = false)
    {
        $email_rule = $client ? "required|unique:users,email,$id" : "required|email|unique:users,email,$id";
        $common = [
            'email'    => $email_rule,
            'password' => 'nullable|confirmed',
            'avatar' => 'image',
        ];

        if ($update) {
            return $common;
        }

        return array_merge($common, [
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /*
    |------------------------------------------------------------------------------------
    | Attributes
    |------------------------------------------------------------------------------------
    */
    public function getAvatarAttribute($value)
    {
        if (!$value) {
            return 'https://www.gravatar.com/avatar/' . md5($this->email) . '?s=200&d=mm';
        }

        return config('variables.avatar.public') . $value;
    }
    public function setAvatarAttribute($photo)
    {
        //delete file first before update if exists
        if ($this->avatar != null) {
            $path = public_path($this->avatar);
            // unlink($path);
            File::delete($path);
        }
        $this->attributes['avatar'] = move_file($photo, 'avatar');
    }


    public function client()
    {
        return $this->hasOne(Client::class);
    }
}

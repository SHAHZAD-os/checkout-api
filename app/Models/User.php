<?php

     namespace App\Models;

     use Illuminate\Database\Eloquent\Factories\HasFactory;
     use Illuminate\Foundation\Auth\User as Authenticatable;
     use Tymon\JWTAuth\Contracts\JWTSubject;

     class User extends Authenticatable implements JWTSubject
     {
         use HasFactory;

         protected $fillable = ['name', 'email', 'password', 'last_activity_at'];

         protected $hidden = ['password'];

         public function getJWTIdentifier()
         {
             return $this->getKey();
         }

         public function getJWTCustomClaims()
         {
             return [];
         }

         public function sessions()
         {
             return $this->hasMany(UserSession::class);
         }

         public function carts()
         {
             return $this->hasMany(Cart::class);
         }

         public function orders()
         {
             return $this->hasMany(Order::class);
         }
     }
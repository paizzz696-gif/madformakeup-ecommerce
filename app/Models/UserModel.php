<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'user_id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'email', 'password_hash', 'phone', 'role', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
<?php

namespace App\Models;


interface UserRepository {
    public function save(User $user);
    public function sendActivationEmail(User $user);
    public function sendPasswordResetEmail(User $user);
    public function resetPassword(String $password, User $user);
    
}
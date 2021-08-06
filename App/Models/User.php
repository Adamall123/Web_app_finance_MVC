<?php

namespace App\Models;

use PDO;
use \App\Token;
/**
 * Example user model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
{

    public $errors = [];

   public function __construct($data = [])
   {
       foreach($data as $key => $value){
           $this->$key = $value;
       };
   }

   public function save()
   {

        $this->validate();

        if(empty($this->errors)){
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'INSERT INTO users (name, email, password)
                    VALUES (:name, :email, :password)';
            $db = static::getDB();
            $stmt = $db->prepare($sql);
    
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);
            
            return $stmt->execute();
        }
       return false;
   }

   public function validate()
   {
        
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }
        if($this->emailExists($this->email)){
            $this->errors[] = 'email already taken';
        }
        if (strlen($this->password) < 6) {
            $this->errors[] = 'Please enter at least 6 characters for the password';
        }
        if (preg_match('/.*[a-z]+.*/i', $this->password) === 0) {
            $this->errors[] = 'Password need at least one letter';
        }
        if (preg_match('/.*\d+.*/i', $this->password) === 0) {
            $this->errors[] = 'Password need at least one number';
        }
   }

   public static function emailExists($email)
   {
        return static::findByEmail($email) !== false;
   }

   public static function findByEmail($email)
   {
        $sql = 'SELECT * FROM users WHERE email = :email';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
       // $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Models\User');
        $stmt->setFetchMode(PDO::FETCH_CLASS,  get_called_class());
        return $stmt->fetch();
   }
   
   public static function authenticate($email, $password)
   {
       $user = static::findByEmail($email);
       if($user){
        if (password_verify($password, $user->password)){
            
            return $user;
        }
       }
        return false;
   }

   public static function findByID($id)
   {
        $sql = 'SELECT * FROM users WHERE id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
       // $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Models\User');
        $stmt->setFetchMode(PDO::FETCH_CLASS,  get_called_class());
        return $stmt->fetch();
   }

   public function rememberLogin()
   {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->rememberToken = $token->getValue();
        $this->expiryTimestamp = time() + 60; 

        $sql = 'INSERT INTO remembered_login (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';
                
         $db = static::getDB();
         $stmt = $db->prepare($sql);
 
         $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
         $stmt->bindValue(':user_id', $this->id, PDO::PARAM_STR);
         $stmt->bindValue(':expires_at', date('Y-m-d H:i:s',$this->expiryTimestamp), PDO::PARAM_STR);
        
         return $stmt->execute();
   }
}

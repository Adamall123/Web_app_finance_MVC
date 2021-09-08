<?php

namespace App\Models;

use PDO;
use \App\Token;
use Core\View;
use \Core\Model;



class UserDB  implements UserRepository
{

    public function save(User $user)
    {
        $walidator = new Walidator();
        $walidator->validate($user);
        if (empty($user->errors)) {
            $password_hash = password_hash($user->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $user->password_reset_token = $token->getValue();
            $sql = 'INSERT INTO users (name, email, password, activation_hash)
                        VALUES (:name, :email, :password, :activation_hash)';
            $db = Model::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $user->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $user->email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);


            if ($stmt->execute()) {
                $user = static::findByEmail($user->email);

                $sql = 'INSERT INTO incomes_category_assigned_to_users (user_id, name)
                            SELECT users.id as user_id, incomes_category_default.name 
                            FROM incomes_category_default,users 
                            WHERE users.id=:id';
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
                $stmt->execute();
                $sql = 'INSERT INTO expenses_category_assigned_to_users(user_id,name)
                            SELECT users.id as user_id, expenses_category_default.name
                            FROM expenses_category_default,users 
                            WHERE users.id=:id';
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
                $stmt->execute();
                $sql = 'INSERT INTO payment_methods_assigned_to_users(user_id,name)
                            SELECT users.id as user_id, payment_methods_default.name 
                            FROM payment_methods_default,users 
                            WHERE users.id=:id';
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
                $stmt->execute();

                return true;
            }
        }
        return false;
    }
    public function sendActivationEmail(User $user)
    {
        $url = 'https://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $user->password_reset_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        // Mail::send($user->email, 'Account activation', $text, $html);
        $headers = 'From: adam.wojdylo.programista@gmail.com' . "\r\n" .
            'Reply-To: webmaster@example.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail($user->email, 'Account activation', $text, $headers);
    }
    public static function emailExists($email, $ignore_id = null)
    {

        $user = static::findByEmail($email);
        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }
        return false;
    }
    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        // $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Models\User');
        $stmt->setFetchMode(PDO::FETCH_CLASS,  get_called_class());
        return $stmt->fetch();
    }
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        // $stmt->setFetchMode(PDO::FETCH_CLASS, 'App\Models\User');
        $stmt->setFetchMode(PDO::FETCH_CLASS,  get_called_class());

        return $stmt->fetch();
    }

    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);
        if ($user && $user->is_active) {
            if (password_verify($password, $user->password)) {
                return $user;
            }
        }
        return false;
    }

    public function rememberLogin(User $user)
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->rememberToken = $token->getValue();
        $this->expiryTimestamp = time() + 60;

        $sql = 'INSERT INTO remembered_login (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';

        $db = Model::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $user->expiryTimestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }
    /**
     * Start the password reset process by generating a new token and expiry 
     */
    public function startPasswordReset($user)
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $user->password_reset_token = $token->getValue();
        $expiryTimestamp = time() + 3600 * 2; // 2 hours from now

        $sql = 'UPDATE users
                 SET password_reset_hash = :token_hash,
                     password_reset_exp = :expires_at 
                     WHERE id = :id';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiryTimestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function sendPasswordResetEmail($user)
    {
        $url = 'https://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $user->password_reset_token;

        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);

        // Mail::send($user->email, 'Password reset', $text, $html);
        $headers = 'From: adam.wojdylo.programista@gmail.com' . "\r\n" .
            'Reply-To: webmaster@example.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        mail($user->email, 'Account activation', $text, $headers);
    }
    public static function sendPasswordReset($email)
    {
        $user = static::findByEmail($email);
        if ($user) {
            if ((new self)->startPasswordReset($user)) {
                (new self)->sendPasswordResetEmail($user);
            }
        }
    }


    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';

        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS,  get_called_class());
        $stmt->execute();

        $user =  $stmt->fetch();

        if ($user) {

            if (strtotime($user->password_reset_exp) > time()) {
                return $user;
            }
        }
    }

    public function resetPassword($password, $user)
    {
        $user->password = $password;
        $walidator = new Walidator();
        $walidator->validate($user);

        if (empty($user->errors)) {

            $password_hash = password_hash($user->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_exp = NULL
                    WHERE id = :id';
            $db = Model::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }
    public static function activate($value)
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';

        $db = Model::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt->execute();
    }
    public function deleteUserAccount($user)
    {
        $sql = 'DELETE FROM users 
            WHERE id=:id';

        $db = Model::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

<?php

namespace App\Models;

use PDO;
use \App\Token;
use  \App\Mail;
use Core\View;
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

            $token = new Token();
            $hashed_token = $token->getHash(); 
            $this->password_reset_token = $token->getValue();
            $sql = 'INSERT INTO users (name, email, password, activation_hash)
                    VALUES (:name, :email, :password, :activation_hash)';
            $db = static::getDB();
            $stmt = $db->prepare($sql);
    
            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);
  
            
            if($stmt->execute()) {
                $user = static::findByEmail($this->email);

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

   public function validate()
   {
        
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }
        if($this->emailExists($this->email, $this->id ?? null)){
            $this->errors[] = 'email already taken';
        }
        if (isset($this->password)) {
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
   }

   public static function emailExists($email, $ignore_id = null)
   {
        $user = static::findByEmail($email);
        if($user) {
            if($user->id != $ignore_id) {
                return true;
            }
        }
        return false;
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
       if($user && $user->is_active){   
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
         $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
         $stmt->bindValue(':expires_at', date('Y-m-d H:i:s',$this->expiryTimestamp), PDO::PARAM_STR);
        
         return $stmt->execute();
   }
   public static function sendPasswordReset($email)
   {
       $user = static::findByEmail($email);
       if($user) {
            if ($user->startPasswordReset()) {
                $user->sendPasswordResetEmail();
            }
       }
   }
   /**
    * Start the password reset process by generating a new token and expiry 
    */
   protected function startPasswordReset()
   {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->password_reset_token = $token->getValue();
        $expiryTimestamp = time() + 3600 * 2; // 2 hours from now

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_exp = :expires_at 
                    WHERE id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiryTimestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        
        return $stmt->execute();
   }

   protected function sendPasswordResetEmail()
   {
       $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

       $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
       $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);
       
        Mail::send($this->email, 'Password reset', $text, $html);
   }

   public static function findByPasswordReset($token)
   {
       $token = new Token($token);
       $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS,  get_called_class());
        $stmt->execute();
        $user =  $stmt->fetch();
        if($user) {
            
            if (strtotime($user->password_reset_exp) > time()) {
                return $user;
            }
        }
   }

   public function resetPassword($password)
   {
        $this->password = $password; 
        
        $this->validate();

        if(empty($this->errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_exp = NULL
                    WHERE id = :id';
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $stmt->execute();
        } 

        return false;
   }

   public function sendActivationEmail()
   {
       $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->password_reset_token;

       $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
       $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);
       
        Mail::send($this->email, 'Account activation', $text, $html);
   }

   public static function activate($value) 
   {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);
        
        $stmt->execute();
   }

   public function updateProfile($data)
   {
       $this->name = $data['name'];
       $this->email = $data['email'];
       
       if ($data['password'] != '') {
            $this->password = $data['password'];
        }
        

       $this->validate();

       if (empty($this->errors)) {
       
           $sql = 'UPDATE users
                    SET name = :name,
                        email = :email';
            //Add password if it's set
            if ($data['password'] != '') {
                $sql .= ', password = :password_hash';
            }
      
            $sql .= "\nWHERE id = :id";
            
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            if ($data['password'] != '') {
                $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            }
            return $stmt->execute();
       }
       return false;
   }
   /////////////////////////////////////////////////////////////////////// INCOMES  ///////////////////////////////////////////////////////////////////////
   public static function getIncomesCategoryAssignedToUser($id)
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE user_id = :id';
        
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('id', $id , PDO::PARAM_INT);

        $stmt->execute();
       
        return $stmt->fetchAll();
    }
    public function saveIncome($id, $params)
    {

        $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
                    VALUES (:id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
    
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':income_category_assigned_to_user_id', $_POST['income_category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':amount', $_POST['amount'], PDO::PARAM_INT);
        $stmt->bindValue(':date_of_income', $_POST['date'], PDO::PARAM_STR);
        $stmt->bindValue(':income_comment', $_POST['comment'], PDO::PARAM_STR);
         return $stmt->execute();
    }
    /////////////////////////////////////////////////////////////////////// EXPENSES  ///////////////////////////////////////////////////////////////////////

    public static function getExpensesCategoryAssignedToUser($id)
    {
        $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $id , PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getPaymentMethodsAssignedToUser($id)
    {
        $sql = 'SELECT * FROM payment_methods_assigned_to_users WHERE user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $id , PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function saveExpense($id, $params)
    {

        $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
                    VALUES (:id, :expense_category_assigned_to_user_id, :payment_method_assigned_to_user_id , :amount, :date_of_expense, :expense_comment)';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':expense_category_assigned_to_user_id', $_POST['expense_category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':payment_method_assigned_to_user_id', $_POST['payment_method_id'], PDO::PARAM_INT);
        $stmt->bindValue(':amount', $_POST['amount'], PDO::PARAM_INT);
        $stmt->bindValue(':date_of_expense', $_POST['date'], PDO::PARAM_STR);
        $stmt->bindValue(':expense_comment', $_POST['comment'], PDO::PARAM_STR);
         return $stmt->execute();
    }
    /////////////////////////////////////////////////////////////////////// BALANCE  ///////////////////////////////////////////////////////////////////////

    public static function fillIncomesOfUser($id) 
    {
        $month = date('m');
		$year = date('Y');
		$numberOfDaysOfSelectedMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDayOfCurrentMonth = $year .'-'.$month.'-01';
        $amountOfDaysOfCurrentMonth = $year .'-' . $month .'-' . $numberOfDaysOfSelectedMonth;

        $sql = 'SELECT name, SUM(amount) AS sum 
                FROM incomes, incomes_category_assigned_to_users 
                WHERE incomes_category_assigned_to_users.id = incomes.income_category_assigned_to_user_id 
                AND incomes.user_id = :id 
                AND date_of_income >= :firstDayOfCurrentMonth 
                AND date_of_income <= :amountOfDaysOfCurrentMonth 
                GROUP BY name';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':firstDayOfCurrentMonth', $firstDayOfCurrentMonth, PDO::PARAM_STR);
        $stmt->bindValue(':amountOfDaysOfCurrentMonth', $amountOfDaysOfCurrentMonth, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function fillExpensesOfUser($id) 
    {
        $month = date('m');
		$year = date('Y');
		$numberOfDaysOfSelectedMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDayOfCurrentMonth = $year .'-'.$month.'-01';
        $amountOfDaysOfCurrentMonth = $year .'-' . $month .'-' . $numberOfDaysOfSelectedMonth;

        $sql = 'SELECT name, SUM(amount) AS sum 
                FROM expenses, expenses_category_assigned_to_users
                WHERE expenses_category_assigned_to_users.id = expenses.expense_category_assigned_to_user_id 
                AND expenses.user_id = :id
                AND date_of_expense >= :firstDayOfCurrentMonth
                AND date_of_expense <= :amountOfDaysOfCurrentMonth 
                GROUP BY name';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':firstDayOfCurrentMonth', $firstDayOfCurrentMonth, PDO::PARAM_STR);
        $stmt->bindValue(':amountOfDaysOfCurrentMonth', $amountOfDaysOfCurrentMonth, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll();
    }
   
}
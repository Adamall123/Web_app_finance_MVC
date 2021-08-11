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

   /////////////////////////////////////////////////////////////////////// INCOMES  ///////////////////////////////////////////////////////////////////////
   public function getIncomesCategoryAssignedToUser()
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE user_id = :id';
        
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('id', $this->id , PDO::PARAM_INT);

        $stmt->execute();
       
        return $stmt->fetchAll();
    }
    public function saveIncome($params)
    {

        $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
                    VALUES (:id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
    
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':income_category_assigned_to_user_id', $_POST['income_category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':amount', $_POST['amount'], PDO::PARAM_INT);
        $stmt->bindValue(':date_of_income', $_POST['date'], PDO::PARAM_STR);
        $stmt->bindValue(':income_comment', $_POST['comment'], PDO::PARAM_STR);
         return $stmt->execute();
    }
    /////////////////////////////////////////////////////////////////////// EXPENSES  ///////////////////////////////////////////////////////////////////////

    public function getExpensesCategoryAssignedToUser()
    {
        $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $this->id , PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPaymentMethodsAssignedToUser()
    {
        $sql = 'SELECT * FROM payment_methods_assigned_to_users WHERE user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $this->id , PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function saveExpense($params)
    {

        $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
                    VALUES (:id, :expense_category_assigned_to_user_id, :payment_method_assigned_to_user_id , :amount, :date_of_expense, :expense_comment)';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expense_category_assigned_to_user_id', $_POST['expense_category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':payment_method_assigned_to_user_id', $_POST['payment_method_id'], PDO::PARAM_INT);
        $stmt->bindValue(':amount', $_POST['amount'], PDO::PARAM_INT);
        $stmt->bindValue(':date_of_expense', $_POST['date'], PDO::PARAM_STR);
        $stmt->bindValue(':expense_comment', $_POST['comment'], PDO::PARAM_STR);
         return $stmt->execute();
    }
    /////////////////////////////////////////////////////////////////////// BALANCE  ///////////////////////////////////////////////////////////////////////

    public function getSumSpendMoneyOnEachIncomeOfUser($scopeOfDate = 1) 
    {
        

        $month = date('m');
		$year = date('Y');
		$numberOfDaysOfSelectedMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
       
        if($scopeOfDate == 1) {
            $firstDayOfCurrentMonth = $year .'-'.$month.'-01';
            $amountOfDaysOfCurrentMonth = $year .'-' . $month .'-' . $numberOfDaysOfSelectedMonth;
            $sql = 'SELECT name, SUM(amount) AS sum 
            FROM incomes, incomes_category_assigned_to_users 
            WHERE incomes_category_assigned_to_users.id = incomes.income_category_assigned_to_user_id 
            AND incomes.user_id = :id 
            AND date_of_income >= :firstDayOfCurrentMonth 
            AND date_of_income <= :amountOfDaysOfCurrentMonth 
            GROUP BY name';
        } else if($scopeOfDate == 2) {
            
            $lastmonth = date('m', strtotime("last month"));
            $firstDayOfLastMonth = $year .'-'.$lastmonth.'-01';
            $amountOfDaysOfLastMonth = $year .'-' . $lastmonth .'-' . $numberOfDaysOfSelectedMonth;
            
			$sql = 'SELECT name, SUM(amount) AS sum 
                    FROM incomes, incomes_category_assigned_to_users 
                    WHERE incomes_category_assigned_to_users.id = incomes.income_category_assigned_to_user_id 
                    AND incomes.user_id = :id
                    AND date_of_income >= :firstDayOfLastMonth 
                    AND date_of_income <= :amountOfDaysOfLastMonth 
                    GROUP BY name';
        } else if ($scopeOfDate == 3) {
            $currentDay = date('d');
            $beginningOfCurrentYear = $year . '-' . '01-01';
            $currentDayOfCurrentYear = $year . '-' . $month . '-' . $currentDay;
		    $sql = 'SELECT name, SUM(amount) AS sum 
                    FROM incomes, incomes_category_assigned_to_users 
                    WHERE incomes_category_assigned_to_users.id = incomes.income_category_assigned_to_user_id 
                    AND incomes.user_id = :id 
                    AND date_of_income >= :beginningOfCurrentYear
                    AND date_of_income <= :currentDayOfCurrentYear
                    GROUP BY name';
        } else if ($scopeOfDate == 4) {
            $sql = 'SELECT name, SUM(amount) AS sum 
                    FROM incomes, incomes_category_assigned_to_users 
                    WHERE incomes_category_assigned_to_users.id = incomes.income_category_assigned_to_user_id 
                    AND incomes.user_id = :id 
                    GROUP BY name';
        }
       

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        if($scopeOfDate == 1) {
            $stmt->bindValue(':firstDayOfCurrentMonth', $firstDayOfCurrentMonth, PDO::PARAM_STR);
            $stmt->bindValue(':amountOfDaysOfCurrentMonth', $amountOfDaysOfCurrentMonth, PDO::PARAM_STR);
        } else if ($scopeOfDate == 2) {
            $stmt->bindValue(':firstDayOfLastMonth', $firstDayOfLastMonth, PDO::PARAM_STR);
            $stmt->bindValue(':amountOfDaysOfLastMonth', $amountOfDaysOfLastMonth, PDO::PARAM_STR);
        } else if ($scopeOfDate == 3) {
            $stmt->bindValue(':beginningOfCurrentYear', $beginningOfCurrentYear, PDO::PARAM_STR);
            $stmt->bindValue(':currentDayOfCurrentYear', $currentDayOfCurrentYear, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSumSpendMoneyOnEachExpenseOfUser($scopeOfDate = 1) 
    {
        
        $month = date('m');
		$year = date('Y');
		$numberOfDaysOfSelectedMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDayOfCurrentMonth = $year .'-'.$month.'-01';
        $amountOfDaysOfCurrentMonth = $year .'-' . $month .'-' . $numberOfDaysOfSelectedMonth;
        
        if ($scopeOfDate == 1) {
            $sql = 'SELECT name, SUM(amount) AS sum 
                FROM expenses, expenses_category_assigned_to_users
                WHERE expenses_category_assigned_to_users.id = expenses.expense_category_assigned_to_user_id 
                AND expenses.user_id = :id
                AND date_of_expense >= :firstDayOfCurrentMonth
                AND date_of_expense <= :amountOfDaysOfCurrentMonth 
                GROUP BY name';
        } else if ($scopeOfDate == 2) {
            $lastmonth = date('m', strtotime("last month"));
            $firstDayOfLastMonth = $year .'-'.$lastmonth.'-01';
            $amountOfDaysOfLastMonth = $year .'-' . $lastmonth .'-' . $numberOfDaysOfSelectedMonth;

			$sql = 'SELECT name, SUM(amount) AS sum 
                    FROM expenses, expenses_category_assigned_to_users 
                    WHERE expenses_category_assigned_to_users.id = expenses.expense_category_assigned_to_user_id 
                    AND expenses.user_id = :id
                    AND date_of_expense >= :firstDayOfLastMonth 
                    AND date_of_expense <= :amountOfDaysOfLastMonth
                    GROUP BY name';
        } else if ($scopeOfDate == 3) {
            $currentDay = date('d');
            $beginningOfCurrentYear = $year . '-' . '01-01';
            $currentDayOfCurrentYear = $year . '-' . $month . '-' . $currentDay;
            $sql =  'SELECT name, SUM(amount) AS sum 
            FROM expenses, expenses_category_assigned_to_users 
            WHERE expenses_category_assigned_to_users.id = expenses.expense_category_assigned_to_user_id 
            AND expenses.user_id = :id
            AND date_of_expense >= :beginningOfCurrentYear 
            AND date_of_expense <= :currentDayOfCurrentYear 
            GROUP BY name';
        } else if ($scopeOfDate == 4) {
            $sql = 'SELECT name, SUM(amount) AS sum 
                    FROM expenses, expenses_category_assigned_to_users 
                    WHERE expenses_category_assigned_to_users.id = expenses.expense_category_assigned_to_user_id 
                    AND expenses.user_id = :id 
                    GROUP BY name';
        }

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        if ($scopeOfDate == 1) {
            $stmt->bindValue(':firstDayOfCurrentMonth', $firstDayOfCurrentMonth, PDO::PARAM_STR);
             $stmt->bindValue(':amountOfDaysOfCurrentMonth', $amountOfDaysOfCurrentMonth, PDO::PARAM_STR);
        } else if ($scopeOfDate == 2) {
            $stmt->bindValue(':firstDayOfLastMonth', $firstDayOfLastMonth, PDO::PARAM_STR);
            $stmt->bindValue(':amountOfDaysOfLastMonth', $amountOfDaysOfLastMonth, PDO::PARAM_STR);
        } else if ($scopeOfDate == 3) {
            $stmt->bindValue(':beginningOfCurrentYear', $beginningOfCurrentYear, PDO::PARAM_STR);
            $stmt->bindValue(':currentDayOfCurrentYear', $currentDayOfCurrentYear, PDO::PARAM_STR);
        }
        

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function sumFromIncomesAndExpenses($id, $scopeOfDate = 1) 
    {
        $month = date('m');
        $year = date('Y');
        $numberOfDaysOfSelectedMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDayOfCurrentMonth = $year .'-'.$month.'-01';
        $amountOfDaysOfCurrentMonth = $year .'-' . $month .'-' . $numberOfDaysOfSelectedMonth;
        
        if ($scopeOfDate == 1) {
            $sqlSumIncomes =   'SELECT SUM(incomes.amount) as sumIncomes
                                FROM incomes 
                                WHERE incomes.user_id = :id 
                                AND date_of_income >= :firstDayOfCurrentMonth 
                                AND date_of_income <= :amountOfDaysOfCurrentMonth ';
            $sqlSumExpenses =  'SELECT SUM(expenses.amount) as sumExpenses 
                                FROM expenses 
                                WHERE expenses.user_id = :id
                                AND date_of_expense >= :firstDayOfCurrentMonth 
                                AND date_of_expense <= :amountOfDaysOfCurrentMonth';
        } else if ($scopeOfDate == 2) {
            $lastmonth = date('m', strtotime("last month"));
            $firstDayOfLastMonth = $year .'-'.$lastmonth.'-01';
            $amountOfDaysOfLastMonth = $year .'-' . $lastmonth .'-' . $numberOfDaysOfSelectedMonth;

            $sqlSumIncomes =   'SELECT SUM(incomes.amount) as sumIncomes 
                                FROM incomes 
                                WHERE incomes.user_id = :id 
                                AND date_of_income >= :firstDayOfLastMonth 
                                AND date_of_income <= :amountOfDaysOfLastMonth ';
            $sqlSumExpenses =  'SELECT SUM(expenses.amount) as sumExpenses 
                                FROM expenses 
                                WHERE expenses.user_id = :id 
                                AND date_of_expense >= :firstDayOfLastMonth 
                                AND date_of_expense <= :amountOfDaysOfLastMonth';
        } else if ($scopeOfDate == 3) {
            $currentDay = date('d');
            $beginningOfCurrentYear = $year . '-' . '01-01';
            $currentDayOfCurrentYear = $year . '-' . $month . '-' . $currentDay;
            $sqlSumIncomes =   'SELECT SUM(incomes.amount) as sumIncomes 
                                FROM incomes 
                                WHERE incomes.user_id = :id 
                                AND date_of_income >= :beginningOfCurrentYear 
                                AND date_of_income <= :currentDayOfCurrentYear';
            $sqlSumExpenses =  'SELECT SUM(expenses.amount) as sumExpenses 
                                FROM expenses 
                                WHERE expenses.user_id = :id 
                                AND date_of_expense >= :beginningOfCurrentYear 
                                AND date_of_expense <= :currentDayOfCurrentYear';
        } else if ($scopeOfDate == 4) {
            $sqlSumIncomes =   'SELECT SUM(incomes.amount) as sumIncomes 
                                FROM incomes 
                                WHERE incomes.user_id = :id' ;
            $sqlSumExpenses =  'SELECT SUM(expenses.amount) as sumExpenses 
                                FROM expenses
                                WHERE expenses.user_id = :id';
        }

        $db = static::getDB();
        $stmt = $db->prepare($sqlSumIncomes);
        $stmt2 = $db->prepare($sqlSumExpenses);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt2->bindValue(':id', $id, PDO::PARAM_INT);

        if ($scopeOfDate == 1) {
            $stmt->bindValue(':firstDayOfCurrentMonth', $firstDayOfCurrentMonth, PDO::PARAM_STR);
             $stmt->bindValue(':amountOfDaysOfCurrentMonth', $amountOfDaysOfCurrentMonth, PDO::PARAM_STR);
             $stmt2->bindValue(':firstDayOfCurrentMonth', $firstDayOfCurrentMonth, PDO::PARAM_STR);
             $stmt2->bindValue(':amountOfDaysOfCurrentMonth', $amountOfDaysOfCurrentMonth, PDO::PARAM_STR);
        } else if ($scopeOfDate == 2) {
            $stmt->bindValue(':firstDayOfLastMonth', $firstDayOfLastMonth, PDO::PARAM_STR);
            $stmt->bindValue(':amountOfDaysOfLastMonth', $amountOfDaysOfLastMonth, PDO::PARAM_STR);
            $stmt2->bindValue(':firstDayOfLastMonth', $firstDayOfLastMonth, PDO::PARAM_STR);
            $stmt2->bindValue(':amountOfDaysOfLastMonth', $amountOfDaysOfLastMonth, PDO::PARAM_STR);
        } else if ($scopeOfDate == 3) {
            $stmt->bindValue(':beginningOfCurrentYear', $beginningOfCurrentYear, PDO::PARAM_STR);
            $stmt->bindValue(':currentDayOfCurrentYear', $currentDayOfCurrentYear, PDO::PARAM_STR);
            $stmt2->bindValue(':beginningOfCurrentYear', $beginningOfCurrentYear, PDO::PARAM_STR);
            $stmt2->bindValue(':currentDayOfCurrentYear', $currentDayOfCurrentYear, PDO::PARAM_STR);
        }

        $stmt->execute();
        $stmt2->execute();
        $sqlSumIncomesResult = $stmt->fetchAll();  
        $sqlSumExpensesResult = $stmt2->fetchAll();  

		foreach($sqlSumIncomesResult as $sqlSumIncomeResult)
		  {  
				$sumIncome = $sqlSumIncomeResult["sumIncomes"];
		  }  
		
		  foreach($sqlSumExpensesResult as $sqlSumExpenseResult) 
		  {  
				$sumExpense = $sqlSumExpenseResult["sumExpenses"];
		  }
		  $sumFromIncomesExpenses = $sumIncome - $sumExpense;

          return $sumFromIncomesExpenses;
    }
/////////////////////////////////////////////////////////////////////// Settings  ///////////////////////////////////////////////////////////////////////
    
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
   public function addNewIncomeCategory($new_income)
   {
        if($this->ifIncomeCategoryExists($new_income)) {
            if ($this->validateLengthOfCategoryInSettings($new_income)){
                $sql = 'INSERT INTO incomes_category_assigned_to_users VALUES(NULL,:id,:new_income)';
                $db = static::getDB();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':new_income',$new_income,PDO::PARAM_STR);
                $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
   }

   public function addNewExpenseCategory($new_expense)
   {
        if($this->ifExpenseCategoryExists($new_expense)) {
            if ($this->validateLengthOfCategoryInSettings($new_expense)){
                $sql = 'INSERT INTO expenses_category_assigned_to_users VALUES(NULL,:id,:new_expense)';
                $db = static::getDB();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':new_expense',$new_expense,PDO::PARAM_STR);
                $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
   }

   public function addNewPaymentMethodCategory($new_payment_method)
   {
        if($this->ifPaymentMethodCategoryExists($new_payment_method)) {
            if ($this->validateLengthOfCategoryInSettings($new_payment_method)){
                $sql = 'INSERT INTO payment_methods_assigned_to_users VALUES(NULL,:id,:new_payment_method)';
                $db = static::getDB();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':new_payment_method',$new_payment_method,PDO::PARAM_STR);
                $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
   }
    public function editIncome($idOfIncome, $newNameOfIncome) {
        if($this->ifIncomeCategoryExists($newNameOfIncome)) {
            if ($this->validateLengthOfCategoryInSettings($newNameOfIncome)){
                $sql = 'UPDATE incomes_category_assigned_to_users 
                    SET name=:edit_income 
                    WHERE user_id=:id 
                    AND id= :income_id ';
                $db = static::getDB();
                $stmt = $db->prepare($sql);

                $stmt->bindValue(':edit_income',$newNameOfIncome,PDO::PARAM_STR);
                $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
                $stmt->bindValue(':income_id',$idOfIncome,PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
    }
    public function editExpense($idOfExpense, $newNameOfExpense) {
       
        if($this->ifExpenseCategoryExists($newNameOfExpense)) {
            if ($this->validateLengthOfCategoryInSettings($newNameOfExpense)){
                $sql = 'UPDATE expenses_category_assigned_to_users 
                SET name=:edit_expense 
                WHERE user_id=:id 
                AND id= :expense_id ';
                $db = static::getDB();
                $stmt = $db->prepare($sql);

                $stmt->bindValue(':edit_expense',$newNameOfExpense,PDO::PARAM_STR);
                $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
                $stmt->bindValue(':expense_id',$idOfExpense,PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
    }
    public function editPaymentMethod($idOfPaymentMethod, $newNameOfPaymentMethod) {
       
        if($this->ifPaymentMethodCategoryExists($newNameOfPaymentMethod)) {
            if ($this->validateLengthOfCategoryInSettings($newNameOfPaymentMethod)){
                $sql = 'UPDATE payment_methods_assigned_to_users 
                    SET name=:edit_payment_method
                    WHERE user_id=:id 
                    AND id= :payment_method_id ';
                $db = static::getDB();
                $stmt = $db->prepare($sql);

                $stmt->bindValue(':edit_payment_method',$newNameOfPaymentMethod,PDO::PARAM_STR);
                $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
                $stmt->bindValue(':payment_method_id',$idOfPaymentMethod,PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
    }

    protected function ifIncomeCategoryExists($new_income) {
        $sql =  'SELECT name from incomes_category_assigned_to_users
        WHERE name = :new_income
        AND user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':new_income',$new_income,PDO::PARAM_STR);
        $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
        $stmt->execute();
        //if(!$stmt) throw new Exception($db->error);
        $amount_of_category_income = $stmt->rowCount();

        if ($amount_of_category_income) {
        $this->errors[] = 'A category name exists.';
        return 0;
        }
        return 1;
   }
   
   protected function ifExpenseCategoryExists($new_expence) {
        $sql =  'SELECT name from expenses_category_assigned_to_users
        WHERE name = :new_expence
        AND user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':new_expence',$new_expence,PDO::PARAM_STR);
        $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
        $stmt->execute();
        //if(!$stmt) throw new Exception($db->error);
        $amount_of_category_expense = $stmt->rowCount();

        if ($amount_of_category_expense) {
        $this->errors[] = 'A category name exists.';
        return 0;
        }
        return 1;
    }
    protected function ifPaymentMethodCategoryExists($new_payment_method) {
        $sql =  'SELECT name from payment_methods_assigned_to_users
        WHERE name = :new_payment_method
        AND user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':new_payment_method',$new_payment_method,PDO::PARAM_STR);
        $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
        $stmt->execute();
        $amount_of_category_payment_method = $stmt->rowCount();
        // echo $amount_of_category_payment_method;
        //     exit;
        if ($amount_of_category_payment_method) {
        $this->errors[] = 'A category name exists.';
        return 0;
        }
        return 1;
    }

    protected function validateLengthOfCategoryInSettings($text) 
    {
        if (strlen($text) > 20 || strlen($text) < 3) {
            $this->errors[] = "A category name must have characters between 3 and 20.";
            return 0;
        }
        return 1;
    }
    
}



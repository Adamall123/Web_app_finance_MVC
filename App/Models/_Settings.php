<?php

namespace App\Models;

use PDO;
use \App\Token;
use  \App\Mail;
use Core\View;

/**
 * Example settings model
 *
 * PHP version 7.0
 */
class _Settings extends User
{
    /////////////////////////////////////////////////////////////////////// EDIT USER PROFILE ///////////////////////////////////////////////////////////////////////
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
    public function deleteAllIncomesAndExpenses()
    {
        $sqlIncomes = 'DELETE FROM incomes 
            WHERE user_id=:id';
        $sqlExpenses = 'DELETE FROM expenses 
          WHERE user_id=:id';

         $db = static::getDB();
         $stmt = $db->prepare($sqlIncomes);
         $stmt2 = $db->prepare($sqlExpenses);
         $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
         $stmt2->bindValue(':id',$this->id,PDO::PARAM_INT);

         if ($stmt->execute())
        return $stmt2->execute();

    }
    public function deleteAllIncomesAndExpensesCategoriesAssignedToUser()
    {
        $sqlIncomes = 'DELETE FROM incomes_category_assigned_to_users 
            WHERE user_id=:id';
        $sqlExpenses = 'DELETE FROM expenses_category_assigned_to_users 
          WHERE user_id=:id';

         $db = static::getDB();
         $stmt = $db->prepare($sqlIncomes);
         $stmt2 = $db->prepare($sqlExpenses);
         $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
         $stmt2->bindValue(':id',$this->id,PDO::PARAM_INT);

         if ($stmt->execute())
        return $stmt2->execute();
    }
    public function deleteUserAccount()
    {
        $sql = 'DELETE FROM users 
            WHERE id=:id';

         $db = static::getDB();
         $stmt = $db->prepare($sql);
         
         $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);

         return $stmt->execute();
    }
    /////////////////////////////////////////////////////////////////////// EDIT USER PROFILE ///////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////// INCOMES CATEGORIES ///////////////////////////////////////////////////////////////////////
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
    public function deleteIncomeAndUpdateIncomeCategoryAssignedToUser($idOfIncome, $nameOfDefaultCategory) {

        $defaultCategoryId = $this->getDefaultIncomeCategoryIdRelatedToUser($nameOfDefaultCategory)['id'] ;
       
        $sql = 'UPDATE incomes 
        SET income_category_assigned_to_user_id = :defaultCategoryId
        WHERE user_id = :id
        AND income_category_assigned_to_user_id = :idOfIncome';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
        $stmt->bindValue(':defaultCategoryId',$defaultCategoryId,PDO::PARAM_INT);
        $stmt->bindValue(':idOfIncome',$idOfIncome,PDO::PARAM_INT);
        if ($stmt->execute()) {
        $sql = 'DELETE FROM incomes_category_assigned_to_users 
            WHERE user_id=:id 
            AND id= :income_id ';
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
        $stmt->bindValue(':income_id',$idOfIncome,PDO::PARAM_INT);
        return $stmt->execute();
        }
        return 0;
    }
    public function deleteExpenseAndUpdateExpenseCategoryAssignedToUser($idOfExpense, $nameOfDefaultCategory) {
 
        $defaultCategoryId = $this->getDefaultExpenseCategoryIdRelatedToUser($nameOfDefaultCategory)['id'] ;
     
        $sql = 'UPDATE expenses 
                SET expense_category_assigned_to_user_id = :defaultCategoryId
                WHERE user_id = :id
                AND expense_category_assigned_to_user_id = :idOfExpense';

         $db = static::getDB();
         $stmt = $db->prepare($sql);

         $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
         $stmt->bindValue(':defaultCategoryId',$defaultCategoryId,PDO::PARAM_INT);
         $stmt->bindValue(':idOfExpense',$idOfExpense,PDO::PARAM_INT);
         if ($stmt->execute()) {
            $sql = 'DELETE FROM expenses_category_assigned_to_users 
            WHERE user_id=:id 
            AND id= :expense_id ';
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
            $stmt->bindValue(':expense_id',$idOfExpense,PDO::PARAM_INT);
            return $stmt->execute();
         } 
         return 0;
    }
    protected function getDefaultIncomeCategoryIdRelatedToUser($nameOfDefaultCategory) {
        $sql = 'SELECT id FROM `incomes_category_assigned_to_users`
                WHERE name = :nameOfDefaultCategory
                AND user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':nameOfDefaultCategory', $nameOfDefaultCategory, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();  
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

        $amount_of_category_income = $stmt->rowCount();

        if ($amount_of_category_income) {
        $this->errors[] = 'A category name exists.';
        return 0;
        }
        return 1;
   }
    /////////////////////////////////////////////////////////////////////// INCOMES CATEGORIES ///////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////// EXPENSES CATEGORIES ///////////////////////////////////////////////////////////////////////
   public function addNewExpenseCategory($new_expense)
   {
        if($this->ifExpenseCategoryExists($new_expense)) {
            if ($this->validateLengthOfCategoryInSettings($new_expense)){
                
                $sql = 'INSERT INTO expenses_category_assigned_to_users VALUES(NULL,:id,:new_expense,:limit)';
                $db = static::getDB();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':new_expense',$new_expense,PDO::PARAM_STR);
                $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
                $stmt->bindValue(':limit',null,PDO::PARAM_INT);
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
    protected function getDefaultExpenseCategoryIdRelatedToUser($nameOfDefaultCategory) {
        $sql = 'SELECT id FROM `expenses_category_assigned_to_users`
                WHERE name = :nameOfDefaultCategory
                AND user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':nameOfDefaultCategory', $nameOfDefaultCategory, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
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
    ///////////////////////////////////////////////////////////////////// PAYMENT_METHOD CATEGORIES /////////////////////////////////////////////////////////////////////
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
    public function deletePaymentMethodAndUpdatePaymenthMethodAssignedToUser($idOfPaymentMethod, $nameOfDefaultCategory) {

        $defaultCategoryId = $this->getDefaultPaymentMethodCategoryIdRelatedToUser($nameOfDefaultCategory)['id'] ; 
        
        $sql = 'UPDATE expenses 
                SET payment_method_assigned_to_user_id = :defaultCategoryId
                WHERE user_id = :id
                AND payment_method_assigned_to_user_id = :idOfPaymentMethod';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
        $stmt->bindValue(':defaultCategoryId',$defaultCategoryId,PDO::PARAM_INT);
        $stmt->bindValue(':idOfPaymentMethod',$idOfPaymentMethod,PDO::PARAM_INT);
        if ($stmt->execute()) {
            $sql = 'DELETE FROM payment_methods_assigned_to_users 
                    WHERE user_id=:id 
                    AND id= :payment_method_id ';
            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
            $stmt->bindValue(':payment_method_id',$idOfPaymentMethod,PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
    protected function getDefaultPaymentMethodCategoryIdRelatedToUser($nameOfDefaultCategory) {
        $sql = 'SELECT id FROM `payment_methods_assigned_to_users`
                WHERE name = :nameOfDefaultCategory
                AND user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':nameOfDefaultCategory', $nameOfDefaultCategory, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
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
    /////////////////////////////////////////////////////////////////////// PAYMENT_METHOD CATEGORIES ///////////////////////////////////////////////////////////////////////
    
    protected function validateLengthOfCategoryInSettings($text) 
    {
        if (strlen($text) > 20 || strlen($text) < 3) {
            $this->errors[] = "A category name must have characters between 3 and 20.";
            return 0;
        }
        return 1;
    }
}
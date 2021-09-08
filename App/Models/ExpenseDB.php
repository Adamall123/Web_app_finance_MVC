<?php

namespace App\Models;

use PDO;
use \Core\Model;

class ExpenseDB implements ExpenseRepository
{
    public function getExpensesCategoryAssignedToUser($user)
    {
        $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE user_id = :id';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $user->id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function addNewExpenseCategory(String $expense_category_name, $user)
    {
        if ($this->ifExpenseCategoryExists($expense_category_name, $user)) {
            if ($this->validateLengthOfCategory($expense_category_name)) {

                $sql = 'INSERT INTO expenses_category_assigned_to_users VALUES(NULL,:id,:new_expense,:limit)';
                $db = Model::getDB();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':new_expense', $expense_category_name, PDO::PARAM_STR);
                $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
                $stmt->bindValue(':limit', null, PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
    }
    public function editExpenseCategory(int $id_of_expense,String $name_of_expense_category, $user) {
      
    if($this->ifExpenseCategoryExists($name_of_expense_category, $user)) {
        if ($this->validateLengthOfCategory($name_of_expense_category, $user)){
            $sql = 'UPDATE expenses_category_assigned_to_users 
            SET name=:edit_expense 
            WHERE user_id=:id 
            AND id= :expense_id ';
            $db = Model::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':edit_expense',$name_of_expense_category,PDO::PARAM_STR);
            $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
            $stmt->bindValue(':expense_id',$id_of_expense,PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
}

public function deleteExpenseAndUpdateExpenseCategoryAssignedToUser(int $id_of_expense,String $name_of_default_category, $user) {
 
        $defaultCategoryId = $this->getDefaultExpenseCategoryIdRelatedToUser($name_of_default_category, $user)['id'] ;
     
        $sql = 'UPDATE expenses 
                SET expense_category_assigned_to_user_id = :defaultCategoryId
                WHERE user_id = :id
                AND expense_category_assigned_to_user_id = :idOfExpense';

         $db = Model::getDB();
         $stmt = $db->prepare($sql);

         $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
         $stmt->bindValue(':defaultCategoryId',$defaultCategoryId,PDO::PARAM_INT);
         $stmt->bindValue(':idOfExpense',$id_of_expense,PDO::PARAM_INT);
         if ($stmt->execute()) {
            $sql = 'DELETE FROM expenses_category_assigned_to_users 
            WHERE user_id=:id 
            AND id= :expense_id ';
            $db = Model::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
            $stmt->bindValue(':expense_id',$id_of_expense,PDO::PARAM_INT);
            return $stmt->execute();
         } 
         return 0;
    }
        public function deleteAllExpenses($user)
    {
        $sqlExpenses = 'DELETE FROM expenses 
                        WHERE user_id=:id';
        $db = Model::getDB();
        $stmt = $db->prepare($sqlExpenses);
        $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
        return $stmt->execute();
    }
        protected function ifExpenseCategoryExists($new_expence, $user) {
        $sql =  'SELECT name from expenses_category_assigned_to_users
        WHERE name = :new_expence
        AND user_id = :id';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':new_expence',$new_expence,PDO::PARAM_STR);
        $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
        $stmt->execute();
        //if(!$stmt) throw new Exception($db->error);
        $amount_of_category_expense = $stmt->rowCount();

        if ($amount_of_category_expense) {
        $this->errors[] = 'A category name exists.';
        return 0;
        }
        return 1;
    }
        public function updateLimitExpenseCategory(int $id_of_expense, $monthlyLimit, $user)
    {
        if($monthlyLimit != null) {
            $sql = 'UPDATE expenses_category_assigned_to_users
            SET monthly_limit=:monthlyLimit
            WHERE user_id=:id
            AND id=:expense_id';
            $db = Model::getDB();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':monthlyLimit',$monthlyLimit,PDO::PARAM_STR);
            $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
            $stmt->bindValue(':expense_id',$id_of_expense,PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
    protected function validateLengthOfCategory($text)
    {
        if (strlen($text) > 20 || strlen($text) < 3) {
            $this->errors[] = "A category name must have characters between 3 and 20.";
            return 0;
        }
        return 1;
    }
        protected function getDefaultExpenseCategoryIdRelatedToUser($nameOfDefaultCategory, $user) {
        $sql = 'SELECT id FROM `expenses_category_assigned_to_users`
                WHERE name = :nameOfDefaultCategory
                AND user_id = :id';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
        $stmt->bindValue(':nameOfDefaultCategory', $nameOfDefaultCategory, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

}

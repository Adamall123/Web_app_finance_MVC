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
class _Expense extends User
{
//     public function updateLimitExpenseCategory($idOfExpense, $monthlyLimit)
//     {
//         if($monthlyLimit != null) {
//             $sql = 'UPDATE expenses_category_assigned_to_users
//             SET monthly_limit=:monthlyLimit
//             WHERE user_id=:id
//             AND id=:expense_id';
//             $db = static::getDB();
//             $stmt = $db->prepare($sql);
//             $stmt->bindValue(':monthlyLimit',$monthlyLimit,PDO::PARAM_STR);
//             $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
//             $stmt->bindValue(':expense_id',$idOfExpense,PDO::PARAM_INT);
//             return $stmt->execute();
//         }
//     }
//     public function getExpenseLimit($idOfExpense) {
//             $sqlSumExpenses =  'SELECT monthly_limit  
//                                 FROM expenses_category_assigned_to_users 
//                                 WHERE id = :expense_id
//                                 AND user_id = :id ';
//             $db = static::getDB();
//             $stmt = $db->prepare($sql);

//             $stmt->bindValue(':expense_id', $idOfExpense, PDO::PARAM_INT);
//             $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            
//             return $stmt->execute();
//     }
//     public function getExpensesCategoryAssignedToUser()
//     {
//         $sql = 'SELECT * FROM expenses_category_assigned_to_users WHERE user_id = :id';
//         $db = static::getDB();
//         $stmt = $db->prepare($sql);
//         $stmt->bindValue('id', $this->id , PDO::PARAM_INT);
//         $stmt->execute();
//         return $stmt->fetchAll();
//     }
//     public function deleteAllExpenses()
//     {
//         $sqlExpenses = 'DELETE FROM expenses 
//                         WHERE user_id=:id';
//         $db = static::getDB();
//         $stmt = $db->prepare($sqlExpenses);
//         $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
//         return $stmt->execute();
//     }

//     public function deleteAllExpensesCategoriesAssignedToUser()
//     {
//         $sqlExpenses = 'DELETE FROM expenses_category_assigned_to_users 
//                         WHERE user_id=:id';
//         $db = static::getDB();
//         $stmt = $db->prepare($sqlExpenses);
//         $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
//         return $stmt->execute();
        
//     }
//    public function addNewExpenseCategory($new_expense)
//    {
//         if($this->ifExpenseCategoryExists($new_expense)) {
//             if ($this->validateLengthOfCategoryInSettings($new_expense)){
                
//                 $sql = 'INSERT INTO expenses_category_assigned_to_users VALUES(NULL,:id,:new_expense,:limit)';
//                 $db = static::getDB();
//                 $stmt = $db->prepare($sql);
//                 $stmt->bindValue(':new_expense',$new_expense,PDO::PARAM_STR);
//                 $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
//                 $stmt->bindValue(':limit',null,PDO::PARAM_INT);
//                 return $stmt->execute();
//             }
//         }
//    }
//    public function editExpense($idOfExpense, $newNameOfExpense) {
      
//     if($this->ifExpenseCategoryExists($newNameOfExpense)) {
//         if ($this->validateLengthOfCategoryInSettings($newNameOfExpense)){
//             $sql = 'UPDATE expenses_category_assigned_to_users 
//             SET name=:edit_expense 
//             WHERE user_id=:id 
//             AND id= :expense_id ';
//             $db = static::getDB();
//             $stmt = $db->prepare($sql);

//             $stmt->bindValue(':edit_expense',$newNameOfExpense,PDO::PARAM_STR);
//             $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
//             $stmt->bindValue(':expense_id',$idOfExpense,PDO::PARAM_INT);
//             return $stmt->execute();
//         }
//     }
// }
//     protected function getDefaultExpenseCategoryIdRelatedToUser($nameOfDefaultCategory) {
//         $sql = 'SELECT id FROM `expenses_category_assigned_to_users`
//                 WHERE name = :nameOfDefaultCategory
//                 AND user_id = :id';
//         $db = static::getDB();
//         $stmt = $db->prepare($sql);

//         $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
//         $stmt->bindValue(':nameOfDefaultCategory', $nameOfDefaultCategory, PDO::PARAM_STR);
//         $stmt->execute();
//         return $stmt->fetch();
//     }
//     protected function ifExpenseCategoryExists($new_expence) {
//         $sql =  'SELECT name from expenses_category_assigned_to_users
//         WHERE name = :new_expence
//         AND user_id = :id';
//         $db = static::getDB();
//         $stmt = $db->prepare($sql);
//         $stmt->bindValue(':new_expence',$new_expence,PDO::PARAM_STR);
//         $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
//         $stmt->execute();
//         //if(!$stmt) throw new Exception($db->error);
//         $amount_of_category_expense = $stmt->rowCount();

//         if ($amount_of_category_expense) {
//         $this->errors[] = 'A category name exists.';
//         return 0;
//         }
//         return 1;
//     }
//     public function deleteExpenseAndUpdateExpenseCategoryAssignedToUser($idOfExpense, $nameOfDefaultCategory) {
 
//         $defaultCategoryId = $this->getDefaultExpenseCategoryIdRelatedToUser($nameOfDefaultCategory)['id'] ;
     
//         $sql = 'UPDATE expenses 
//                 SET expense_category_assigned_to_user_id = :defaultCategoryId
//                 WHERE user_id = :id
//                 AND expense_category_assigned_to_user_id = :idOfExpense';

//          $db = static::getDB();
//          $stmt = $db->prepare($sql);

//          $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
//          $stmt->bindValue(':defaultCategoryId',$defaultCategoryId,PDO::PARAM_INT);
//          $stmt->bindValue(':idOfExpense',$idOfExpense,PDO::PARAM_INT);
//          if ($stmt->execute()) {
//             $sql = 'DELETE FROM expenses_category_assigned_to_users 
//             WHERE user_id=:id 
//             AND id= :expense_id ';
//             $db = static::getDB();
//             $stmt = $db->prepare($sql);

//             $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
//             $stmt->bindValue(':expense_id',$idOfExpense,PDO::PARAM_INT);
//             return $stmt->execute();
//          } 
//          return 0;
//     }
}
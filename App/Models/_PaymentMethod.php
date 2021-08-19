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
class _PaymentMethod extends User
{
    
    public function getPaymentMethodsAssignedToUser()
    {
        $sql = 'SELECT * FROM payment_methods_assigned_to_users WHERE user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $this->id , PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
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
    
}
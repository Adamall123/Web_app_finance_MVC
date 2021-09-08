<?php

namespace App\Models;

use PDO;
use \Core\Model;

class PaymentDB implements PaymentRepository
{
      public function getPaymentMethodsAssignedToUser($user)
    {
        $sql = 'SELECT * FROM payment_methods_assigned_to_users WHERE user_id = :id';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $user->id , PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function addNewPaymentMethodCategory(String $new_payment_method, $user)
    {
         if($this->ifPaymentMethodCategoryExists($new_payment_method, $user)) {
                 $sql = 'INSERT INTO payment_methods_assigned_to_users VALUES(NULL,:id,:new_payment_method)';
                 $db = Model::getDB();
                 $stmt = $db->prepare($sql);
                 $stmt->bindValue(':new_payment_method',$new_payment_method,PDO::PARAM_STR);
                 $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
                 return $stmt->execute();
         }
    }
      public function editPaymentMethodCategory(int $id_of_payment_method,String $new_name_of_payment_method, $user) {
        
         if($this->ifPaymentMethodCategoryExists($new_name_of_payment_method, $user)) {
                 $sql = 'UPDATE payment_methods_assigned_to_users 
                     SET name=:edit_payment_method
                     WHERE user_id=:id 
                     AND id= :payment_method_id ';
                 $db = Model::getDB();
                 $stmt = $db->prepare($sql);
 
                 $stmt->bindValue(':edit_payment_method',$new_name_of_payment_method,PDO::PARAM_STR);
                 $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
                 $stmt->bindValue(':payment_method_id',$id_of_payment_method,PDO::PARAM_INT);
                 return $stmt->execute();
         }
     }
       public function deletePaymentMethodAndUpdatePaymenthMethodAssignedToUser(int $id_of_payment_method,String $name_of_default_category, $user) {
 
         $defaultCategoryId = $this->getDefaultPaymentMethodCategoryIdRelatedToUser($name_of_default_category, $user)['id'] ; 
         
         $sql = 'UPDATE expenses 
                 SET payment_method_assigned_to_user_id = :defaultCategoryId
                 WHERE user_id = :id
                 AND payment_method_assigned_to_user_id = :idOfPaymentMethod';
 
         $db = Model::getDB();
         $stmt = $db->prepare($sql);
 
         $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
         $stmt->bindValue(':defaultCategoryId',$defaultCategoryId,PDO::PARAM_INT);
         $stmt->bindValue(':idOfPaymentMethod',$id_of_payment_method,PDO::PARAM_INT);
         if ($stmt->execute()) {
             $sql = 'DELETE FROM payment_methods_assigned_to_users 
                     WHERE user_id=:id 
                     AND id= :payment_method_id ';
             $db = Model::getDB();
             $stmt = $db->prepare($sql);
 
             $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
             $stmt->bindValue(':payment_method_id',$id_of_payment_method,PDO::PARAM_INT);
             return $stmt->execute();
         }
     }
     protected function getDefaultPaymentMethodCategoryIdRelatedToUser($nameOfDefaultCategory, $user) {
         $sql = 'SELECT id FROM `payment_methods_assigned_to_users`
                 WHERE name = :nameOfDefaultCategory
                 AND user_id = :id';
         $db = Model::getDB();
         $stmt = $db->prepare($sql);
 
         $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
         $stmt->bindValue(':nameOfDefaultCategory', $nameOfDefaultCategory, PDO::PARAM_STR);
         $stmt->execute();
         return $stmt->fetch();
     }
       protected function ifPaymentMethodCategoryExists($new_payment_method, $user) {
         $sql =  'SELECT name from payment_methods_assigned_to_users
         WHERE name = :new_payment_method
         AND user_id = :id';
         $db = Model::getDB();
         $stmt = $db->prepare($sql);
         $stmt->bindValue(':new_payment_method',$new_payment_method,PDO::PARAM_STR);
         $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
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
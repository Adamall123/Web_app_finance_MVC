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
class _Income extends User
{
    
    public function getIncomesCategoryAssignedToUser()
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE user_id = :id';
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $this->id , PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function deleteAllIncomes() 
    {
        $sqlIncomes =  'DELETE FROM incomes 
                        WHERE user_id=:id';
        $db = static::getDB();
        $stmt = $db->prepare($sqlIncomes);
        $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteAllIncomesCategoriesAssignedToUser()
    {
        $sqlIncomes =  'DELETE FROM incomes_category_assigned_to_users 
                        WHERE user_id=:id';
        $db = static::getDB();
        $stmt = $db->prepare($sqlIncomes);
        $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
        return $stmt->execute();

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
}
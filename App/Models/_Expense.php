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

    public function __construct($data = [])
    {

        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }
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



//     public function deleteAllExpensesCategoriesAssignedToUser()
//     {
//         $sqlExpenses = 'DELETE FROM expenses_category_assigned_to_users 
//                         WHERE user_id=:id';
//         $db = static::getDB();
//         $stmt = $db->prepare($sqlExpenses);
//         $stmt->bindValue(':id',$this->id,PDO::PARAM_INT);
//         return $stmt->execute();
        
//     }



}
<?php

namespace App\Models;

use PDO;
use \Core\Model;

class IncomesDB implements IncomeRepository
{
    public function getIncomesCategoryAssignedToUser($user)
    {
        $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE user_id = :id';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $user->id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function addNewIncomeCategory(String $income_category_name, $user)
    {

        if ($this->ifIncomeCategoryExists($income_category_name, $user)) {
            if ($this->validateLengthOfCategory($income_category_name)) {
                $sql = 'INSERT INTO incomes_category_assigned_to_users VALUES(NULL,:id,:new_income)';
                $db = Model::getDB();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':new_income', $income_category_name, PDO::PARAM_STR);
                $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
    }
    public function editIncomeCategory(int $id_of_income, String $name_of_income_category, $user)
    {
        if ($this->ifIncomeCategoryExists($name_of_income_category, $user)) {
            if ($this->validateLengthOfCategory($name_of_income_category)) {
                $sql = 'UPDATE incomes_category_assigned_to_users 
                    SET name=:edit_income 
                    WHERE user_id=:id 
                    AND id= :income_id ';
                $db = Model::getDB();
                $stmt = $db->prepare($sql);

                $stmt->bindValue(':edit_income', $name_of_income_category, PDO::PARAM_STR);
                $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
                $stmt->bindValue(':income_id', $id_of_income, PDO::PARAM_INT);
                return $stmt->execute();
            }
        }
    }
    public function deleteIncomeCategoryAndUpdateIncomeCategoryAssignedToUser(int $id_of_income, String $name_of_default_income_category, $user)
    {

        $defaultCategoryId = $this->getDefaultIncomeCategoryIdRelatedToUser($name_of_default_income_category, $user)['id'];

        $sql = 'UPDATE incomes 
        SET income_category_assigned_to_user_id = :defaultCategoryId
        WHERE user_id = :id
        AND income_category_assigned_to_user_id = :idOfIncome';

        $db = Model::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
        $stmt->bindValue(':defaultCategoryId', $defaultCategoryId, PDO::PARAM_INT);
        $stmt->bindValue(':idOfIncome', $id_of_income, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $sql = 'DELETE FROM incomes_category_assigned_to_users 
            WHERE user_id=:id 
            AND id= :income_id ';
            $db = Model::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
            $stmt->bindValue(':income_id', $id_of_income, PDO::PARAM_INT);
            return $stmt->execute();
        }
        return 0;
    }
        public function deleteAllIncomes($user) 
    {
        $sqlIncomes =  'DELETE FROM incomes 
                        WHERE user_id=:id';
        $db = Model::getDB();
        $stmt = $db->prepare($sqlIncomes);
        $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getSumSpendMoneyOnEachIncomeOfUser($startDate, $endDate, $user) 
    {
        
        $sql = 'SELECT name, SUM(amount) AS sum 
                FROM incomes, incomes_category_assigned_to_users 
                WHERE incomes_category_assigned_to_users.id = incomes.income_category_assigned_to_user_id 
                AND incomes.user_id = :id 
                AND date_of_income >= :startDate 
                AND date_of_income <= :endDate 
                GROUP BY name';
        
        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
        
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetchAll();
        
    }

    public function saveIncome(_Income $income, $user){
       // $this->validateAmountAndComment($params);
        if(empty($this->errors)){
            $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
            VALUES (:id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)';
            $db = Model::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
            $stmt->bindValue(':income_category_assigned_to_user_id', $income->income_category_id, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $income->amount, PDO::PARAM_STR);
            $stmt->bindValue(':date_of_income', $income->date, PDO::PARAM_STR);
            $stmt->bindValue(':income_comment', $income->comment, PDO::PARAM_STR);
            return $stmt->execute();
        }
       
        return false;
    }

    protected function getDefaultIncomeCategoryIdRelatedToUser($name_of_default_category, $user)
    {
        $sql = 'SELECT id FROM `incomes_category_assigned_to_users`
                WHERE name = :nameOfDefaultCategory
                AND user_id = :id';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
        $stmt->bindValue(':nameOfDefaultCategory', $name_of_default_category, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
    protected function ifIncomeCategoryExists($new_income, $user)
    {
        $sql =  'SELECT name from incomes_category_assigned_to_users
        WHERE name = :new_income
        AND user_id = :id';
        $db = Model::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':new_income', $new_income, PDO::PARAM_STR);
        $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
        $stmt->execute();

        $amount_of_category_income = $stmt->rowCount();

        if ($amount_of_category_income) {
            $this->errors[] = 'A category name exists.';
            return 0;
        }
        return 1;
    }
    protected function validateLengthOfCategory($text)
    {
        if (strlen($text) > 20 || strlen($text) < 3) {
            $this->errors[] = "A category name must have characters between 3 and 20.";
            return 0;
        }
        return 1;
    }
        public function deleteAllIncomesCategoriesAssignedToUser($user)
    {
        $sqlIncomes =  'DELETE FROM incomes_category_assigned_to_users 
                        WHERE user_id=:id';
        $db = Model::getDB();
        $stmt = $db->prepare($sqlIncomes);
        $stmt->bindValue(':id',$user->id,PDO::PARAM_INT);
        return $stmt->execute();

    }
}

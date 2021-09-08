<?php

namespace App\Models;


interface IncomeRepository {
    public function getIncomesCategoryAssignedToUser($user);
    public function addNewIncomeCategory(String $income_category_name, $user); 
    public function editIncomeCategory(int $id_of_income, String $name_of_income_category, $user);
    public function deleteIncomeCategoryAndUpdateIncomeCategoryAssignedToUser(int $id_of_income, String $name_of_default_income_category, $user);
    public function deleteAllIncomes($user);
    public function getSumSpendMoneyOnEachIncomeOfUser($startDate, $endDate, $user);

    public function saveIncome(_Income $income, $user);
}
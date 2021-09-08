<?php

namespace App\Models;


interface ExpenseRepository {
    public function getExpensesCategoryAssignedToUser($user);
    public function addNewExpenseCategory(String $expense_category_name, $user);
    public function editExpenseCategory(int $id_of_expense,String $name_of_expense_category, $user);
    public function deleteExpenseAndUpdateExpenseCategoryAssignedToUser(int $id_of_expense,String $name_of_default_category, $user);
    public function deleteAllExpenses($user);
    public function updateLimitExpenseCategory(int $id_of_expense, $monthlyLimit, $user);

    public function MonthlyCostsOfEachExpenseFromSelectedDate(int $id_of_expense, $date, $user);
    public function getSumSpendMoneyOnEachExpenseOfUser($startDate, $endDate, $user);

    public function saveExpense(_Expense $income, $user);
}
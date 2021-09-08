<?php

namespace App\Models;


interface PaymentRepository {
    public function getPaymentMethodsAssignedToUser($user);
    public function addNewPaymentMethodCategory(String $new_payment_method, $user);
    public function editPaymentMethodCategory(int $id_of_payment_method,String $new_name_of_payment_method, $user);
    public function deletePaymentMethodAndUpdatePaymenthMethodAssignedToUser(int $id_of_payment_method,String $name_of_default_category, $user);
}
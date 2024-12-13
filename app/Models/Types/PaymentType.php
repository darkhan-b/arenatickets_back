<?php


namespace App\Models\Types;


abstract class PaymentType
{
    const CASH          = 'cash';
    const CARD          = 'card';
    const INVITATION    = 'invitation';
    const PARTNER       = 'partner';
    const FORUM         = 'forum';
    const KASPI         = 'kaspi';
}
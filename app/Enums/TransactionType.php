<?php

namespace App\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case TransferDebit = 'transfer_debit';
    case TransferCredit = 'transfer_credit';
}

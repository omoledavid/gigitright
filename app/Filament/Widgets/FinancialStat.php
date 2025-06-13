<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialStat extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', '₦' . number_format(Transaction::where('transaction_type', TransactionType::CREDIT)
                ->where('status', PaymentStatus::COMPLETED)
                ->sum('amount'), 2))
                ->description('Total revenue from all completed transactions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Withdrawals', '₦' . number_format(Transaction::where('transaction_type', TransactionType::DEBIT)
                ->where('status', PaymentStatus::COMPLETED)
                ->sum('amount'), 2))
                ->description('Total withdrawals from all completed transactions')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Net Profit', '₦' . number_format(
                Transaction::where('transaction_type', TransactionType::CREDIT)
                    ->where('status', PaymentStatus::COMPLETED)
                    ->sum('amount') -
                    Transaction::where('transaction_type', TransactionType::DEBIT)
                    ->where('status', PaymentStatus::COMPLETED)
                    ->sum('amount'),
                2
            ))
                ->description('Total profit (Revenue - Withdrawals)')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Total Transactions', Transaction::count())
                ->description('Total number of transactions')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Completed Transactions', Transaction::where('status', PaymentStatus::COMPLETED)->count())
                ->description('Number of completed transactions')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pending Transactions', Transaction::where('status', PaymentStatus::PENDING)->count())
                ->description('Number of pending transactions')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}

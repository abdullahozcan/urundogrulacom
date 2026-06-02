<?php

namespace App\Filament\Widgets;

class AccountWidget extends \Filament\Widgets\AccountWidget
{
    protected int | string | array $columnSpan = [
        'lg' => 6,
        'xl' => 6,
    ];
}

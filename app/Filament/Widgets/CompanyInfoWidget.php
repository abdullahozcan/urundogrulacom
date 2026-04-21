<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CompanyInfoWidget extends Widget
{
    protected static string $view = 'filament.widgets.company-info-widget';
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Yalnızca şirket ataması olan kullanıcılar görebilir
        $user = Auth::user();
        return $user && $user->company_id;
    }
}

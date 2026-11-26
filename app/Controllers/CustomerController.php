<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
use App\Models\BusinessData;

final class CustomerController
{
    public static function catalog(): void
    {
        Auth::requireRole('Administrator', 'Manager', 'Employee', 'Customer');
        $categoryId = (int)Request::input('category_id', 0);
        $search = (string)Request::input('search', '');

        View::render('customer/catalog', [
            'title' => 'Product catalog - Algorithm Hardware Plug',
            'rows' => BusinessData::productCatalog(
                $categoryId > 0 ? $categoryId : null,
                $search !== '' ? $search : null
            ),
            'categories' => BusinessData::categories(),
            'categoryId' => $categoryId,
            'search' => $search,
        ]);
    }

    public static function mySales(): void
    {
        Auth::requireRole('Customer');
        $user = Auth::user();
        $profile = BusinessData::myCustomerProfile((int)($user['id'] ?? 0));
        $sales = [];
        if ($profile !== null) {
            $sales = BusinessData::mySales((int)$profile['CustomerID']);
        }

        View::render('customer/sales', [
            'title' => 'My purchases - Algorithm Hardware Plug',
            'profile' => $profile,
            'rows' => $sales,
        ]);
    }
}

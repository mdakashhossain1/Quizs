<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfferwallTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferwallController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
        ]);

        $query = OfferwallTransaction::with('user');

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($users) use ($search) {
                $users->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('login_id', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['from'])) {
            $query->where('completed_at', '>=', $filters['from'].' 00:00:00');
        }

        if (! empty($filters['to'])) {
            $query->where('completed_at', '<=', $filters['to'].' 23:59:59');
        }

        $transactions = $query->orderByDesc('completed_at')->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return view('admin.offerwall.index', compact('transactions'));
    }
}

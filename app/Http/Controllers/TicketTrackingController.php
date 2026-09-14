<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TicketTrackingController extends Controller
{
    public function show(Request $request): View
    {
        $query = trim((string) $request->query('q'));

        $tickets = $query === ''
            ? collect()
            : Ticket::query()
                ->where(function ($builder) use ($query) {
                    $builder->where('tracking_code', strtoupper($query))
                        ->orWhere('site_id', 'like', "%{$query}%");
                })
                ->with('reportTypes')
                ->orderByDesc('created_at')
                ->get();

        return view('public.ticket-status', [
            'title' => 'Acompanhar Ticket',
            'query' => $query,
            'tickets' => $tickets,
            'attempted' => $request->has('q'),
        ]);
    }
}

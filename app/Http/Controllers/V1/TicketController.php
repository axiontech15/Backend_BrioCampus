<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Services\V1\TicketService;

class TicketController
{
   
    private $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }
    public function index()
    {
        $tickets = $this->ticketService->getAllTickets();
        return response()->json($tickets, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validated();
        $ticket = $this->ticketService->createTicket($data);
        return response()->json($ticket, 201);
    }

    public function show(string $id)
    {
        $ticket = $this->ticketService->getTicketById($id);
        return response()->json($ticket, 200);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validated();
        $ticket = $this->ticketService->updateTicket($id, $data);
        return response()->json($ticket, 200);
    }

    public function destroy(string $id)
    {
        $result = $this->ticketService->deleteTicket($id);
        if ($result) {
            return response()->json(['message' => 'Ticket deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
    }
}

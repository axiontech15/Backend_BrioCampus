<?php
    namespace App\Http\Services\V1;
    use App\Models\Ticket;

class TicketService{

    public function getAllTickets()
    {
        return Ticket::all();
    }

    public function getTicketById(string $id)
    {
        return Ticket::find($id);
    }

    public function createTicket(array $data)
    {
        return Ticket::create($data);
    }

    public function updateTicket(string $id, array $data)
    {
        return Ticket::where('id', $id)->update($data);
    }

    public function deleteTicket(string $id):bool
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return false;
        }
    
        $ticket->delete();
        return true;
    }
}
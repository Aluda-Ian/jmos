<?php

use App\Models\Client;
use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sync Leads into Contacts
        $leads = Lead::all();
        foreach ($leads as $lead) {
            Contact::updateOrCreate(
                ['lead_id' => $lead->id],
                [
                    'first_name' => $lead->first_name,
                    'last_name' => $lead->last_name,
                    'contact_name' => $lead->lead_name ?: ($lead->company ?: 'Prospect Contact'),
                    'company_name' => $lead->company,
                    'title' => $lead->title ?? 'Prospect',
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'owner' => $lead->lead_owner ?? 'Jeota Media',
                    'notes' => $lead->notes ?? 'Generated from Lead capture',
                ]
            );
        }

        // Sync Clients into Contacts
        $clients = Client::all();
        foreach ($clients as $client) {
            $contactPerson = $client->contact_person ?: $client->client_name;
            Contact::updateOrCreate(
                ['client_id' => $client->id],
                [
                    'contact_name' => $contactPerson,
                    'company_name' => $client->client_name,
                    'title' => 'Client Representative',
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'owner' => $client->owner ?? 'Jeota Media',
                    'notes' => 'Generated from Client creation',
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

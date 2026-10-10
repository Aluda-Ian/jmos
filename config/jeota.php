<?php

/*
|--------------------------------------------------------------------------
| Jeota Media — Official Brand & Contact Details
|--------------------------------------------------------------------------
| Single source of truth for company details shown on client-facing
| quotes, invoices, emails and WhatsApp messages. Change them here only.
*/

return [
    'company' => 'Jeota Media Limited',
    'short_name' => 'Jeota Media Ltd',
    'tagline' => 'Storytellers for a better world',

    'email' => 'info@jeotamedia.co.ke',
    'phone' => '+254 791 388 683',
    'phone_e164' => '+254791388683',
    'website' => 'https://jeotamedia.co.ke/',
    'website_label' => 'jeotamedia.co.ke',
    'address_line1' => 'Keystone Park Riverside',
    'address_line2' => 'Nairobi, Kenya',

    // Calendar times are entered and stored in local (East Africa) time
    'timezone' => 'Africa/Nairobi',

    'kra_pin' => 'P052209707D',

    'bank' => [
        'name' => 'NCBA Bank Kenya',
        'branch' => 'Junction Branch',
        'account_name' => 'Jeota Media Limited',
        'account_no' => '6237790012',
    ],

    'mpesa' => [
        'paybill' => '880100',
        'account' => '6237790012',
    ],

    /*
    |--------------------------------------------------------------------------
    | Client Contracts & E-Signature
    |--------------------------------------------------------------------------
    | The company signatory's signature is appended to every contract sent
    | from JMOS. Only the roles listed below can create or send contracts.
    */
    'po_box' => env('JEOTA_PO_BOX', ''),

    'signatory' => [
        'name' => 'Barny Kiome',
        'title' => 'Director',
        'signature' => 'assets/img/signatures/barny-kiome-signature.png',
    ],

    'contracts' => [
        'allowed_roles' => ['owner', 'manager'],

        // Template key => label. Each key maps to resources/views/contracts/templates/{key}.blade.php
        'templates' => [
            'photo-video-social' => 'Photography, Videography & Social Media Services Agreement',
        ],
    ],
];

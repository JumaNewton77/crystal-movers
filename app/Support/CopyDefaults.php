<?php

namespace App\Support;

/**
 * Single source for the official Crystal Movers starter copy and business defaults.
 * Used by the seeders (insert only when a row is missing) and by `php artisan site:apply-copy`
 * (explicitly overwrites the copy on an existing database). Nothing here invents facts:
 * no founding year, staff or fleet numbers, insurance, guarantees or statistics.
 */
class CopyDefaults
{
    /** @return array<string, array<string, string|null>> website_content keys grouped by group */
    public static function content(): array
    {
        return [
            'home' => [
                'home.hero.eyebrow' => 'Moving, Installation & Repair Services',
                'home.hero.title' => 'Professional Moving and Installation Services You Can Rely On',
                'home.hero.text' => 'Crystal Movers provides moving, installation, repair and furniture assembly services for homes, offices and businesses. From household and office relocations to CCTV installation, Wi-Fi networking, TV mounting and everyday installation needs, contact our team to discuss your requirements and request a quotation.',
                'home.moving.title' => 'Moving Services for Homes, Offices and Businesses',
                'home.moving.text' => 'Planning a move? Crystal Movers helps customers arrange residential relocations, office moves and local or inter-county transport. Tell us where you are moving from, your destination and the assistance you need. We will review your requirements and contact you to discuss the next steps and quotation.',
                'home.install.title' => 'Installation, Repairs and Home Services',
                'home.install.text' => 'Need help with CCTV, Wi-Fi, TV mounting, satellite installation, lighting, washing machines, showers or furniture assembly? Crystal Movers offers a convenient way to enquire about a range of home and business services. Describe the work you need, and our team will follow up to confirm the requirements, availability and quotation.',
            ],
            'about' => [
                'about.summary' => 'Crystal Movers brings moving, installation and repair services together to make everyday projects easier to arrange.',
                'about.who_we_are' => "Crystal Movers brings moving, installation and repair services together to make everyday projects easier to arrange. We serve customers from Nairobi and consider enquiries for other areas across Kenya, depending on the service and job requirements.\n\nOur approach is built around clear communication, careful work, practical solutions and respect for our customers' property. Whether you are planning a relocation or need assistance with an installation or repair, we invite you to tell us what you need so we can discuss the appropriate service.",
                'about.mission' => 'To make moving, installation and repair services easier to arrange through professional communication, careful work and practical solutions.',
                'about.commitment' => "Listen to each customer's requirements.\nExplain the proposed work and quotation clearly.\nAgree on the scope and schedule before work begins.\nHandle customer property responsibly.\nCommunicate about any changes or additional requirements.",
            ],
            'why' => [
                'why.1.title' => 'A Range of Services',
                'why.1.text' => 'Moving, installation, repair and furniture assembly enquiries can be directed through one convenient contact point.',
                'why.2.title' => 'Clear Communication',
                'why.2.text' => 'We discuss your requirements, the proposed work and the next steps before arrangements are confirmed.',
                'why.3.title' => 'Careful Handling',
                'why.3.text' => 'We aim to handle belongings and property responsibly and to clarify any special handling requirements in advance.',
                'why.4.title' => 'Quotation Based on Your Needs',
                'why.4.text' => 'Pricing depends on the service, scope of work, location, materials and other relevant requirements. Contact us for a quotation.',
                'why.5.title' => 'Convenient Enquiry Process',
                'why.5.text' => 'Submit your request online, call us or start a WhatsApp conversation to discuss the work you need.',
                'why.6.title' => 'Service-Area Enquiries',
                'why.6.text' => 'We receive enquiries from Nairobi and other areas across Kenya, subject to the service and job requirements.',
            ],
            'cta' => [
                'cta.title' => 'Need Help with a Move or Installation?',
                'cta.text' => 'Tell us about your project and the service you need. Contact Crystal Movers to discuss availability and request a quotation.',
            ],
            'safety' => [
                'safety.text' => "We approach each job with attention to the customer's property, the work involved and the agreed requirements. Where appropriate, we discuss protective handling, installation conditions and any special precautions before work begins.",
            ],
        ];
    }

    /** @return array<string, array<string, string|null>> settings grouped by group */
    public static function settings(): array
    {
        return [
            'general' => ['tagline' => 'Move Smarter. Live Better.'],
            'contact' => [
                'phone' => '+254 779 606 100',
                'address' => 'Nairobi, Kenya',
                'working_hours' => 'Monday–Saturday, 7:00 AM–6:00 PM',
            ],
            'trust' => [
                'insurance_heading' => 'Safety and Care',
                'insurance_text' => null,
            ],
        ];
    }

    /** Short service descriptions that were reworded to avoid promises. slug => text */
    public const SERVICE_SHORT = [
        'wifi-installation-and-troubleshooting' => 'Wi-Fi installation and help with weak or unreliable connections.',
        'chandelier-installation' => 'Installation of chandeliers, subject to the fitting and site requirements.',
        'residential-moving' => 'Household relocations for homes and apartments. Tell us where you are moving from and to.',
    ];

    /** Email addresses that were placeholders in earlier builds and must never be shown as real. */
    public const PLACEHOLDER_EMAILS = ['info@smartmovers.co.ke', 'info@crystalmovers.co.ke'];
}

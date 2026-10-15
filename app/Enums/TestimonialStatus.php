<?php

namespace App\Enums;

/** Only `approved` testimonials are ever shown publicly. */
enum TestimonialStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Hidden = 'hidden';
}

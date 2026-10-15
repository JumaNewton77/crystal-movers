<?php

namespace App\Models;

use App\Enums\ContactMessageStatus;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'subject', 'message', 'contact_consent', 'status', 'admin_notes', 'ip_address'];

    protected function casts(): array
    {
        return ['status' => ContactMessageStatus::class, 'contact_consent' => 'boolean'];
    }
}

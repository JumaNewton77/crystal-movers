<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            // e.g. CM-Q-2026-0001 (generated in application code; UNIQUE guards against duplicates).
            $table->string('reference_number', 20)->unique();
            $table->string('name', 120);
            $table->string('phone', 30);
            $table->string('email', 190)->nullable();
            // Values of App\Enums\MoveType
            $table->string('move_type', 30);
            $table->string('from_location', 190);
            $table->string('to_location', 190);
            $table->date('preferred_date')->nullable();
            $table->text('message')->nullable();
            // Values of App\Enums\QuoteStatus
            $table->string('status', 30)->default('new');
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('preferred_date');
            $table->index('created_at');
        });

        // Optional customer photos / short videos that help prepare a quotation.
        Schema::create('quote_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained('quote_requests')->cascadeOnDelete();
            $table->string('disk', 30)->default('local'); // private disk
            $table->string('path');                        // generated safe name, never the user's
            $table->string('original_name', 190);          // display only
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            $table->index('quote_request_id');
        });

        // Audit trail of status changes (who moved a quote from X to Y, and when).
        Schema::create('quote_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained('quote_requests')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('quote_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_status_history');
        Schema::dropIfExists('quote_attachments');
        Schema::dropIfExists('quote_requests');
    }
};

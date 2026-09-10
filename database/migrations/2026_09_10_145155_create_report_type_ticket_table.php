<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_type_ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->unique(['report_type_id', 'ticket_id']);
            $table->timestamps();
        });

        DB::table('report_type_ticket')->insertUsing(
            ['report_type_id', 'ticket_id', 'created_at', 'updated_at'],
            DB::table('tickets')
                ->select(['report_type_id', 'id'])
                ->addSelect(DB::raw('CURRENT_TIMESTAMP'))
                ->addSelect(DB::raw('CURRENT_TIMESTAMP'))
                ->whereNotNull('report_type_id')
        );

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('report_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('report_type_id')->nullable()->after('technician_name')->constrained()->nullOnDelete();
        });

        DB::table('report_type_ticket')->each(function (object $row) {
            DB::table('tickets')
                ->where('id', $row->ticket_id)
                ->update(['report_type_id' => $row->report_type_id]);
        });

        Schema::dropIfExists('report_type_ticket');
    }
};

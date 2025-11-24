<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. VOTERS LIST - Optimization for "Millions" of records (Read-Heavy)
        Schema::table('voters_list', function (Blueprint $table) {
            // REMOVED: Single 'cancelled' index (low cardinality, useless on its own).

            // KEEP: Phone is high cardinality (unique-ish values).
            $table->index('phone', 'idx_voters_list_phone');

            // NEW: Composite for common filtering.
            // Better than 'city_id' alone. Covers: "Show me active voters in Beirut".
            $table->index(['city_id', 'cancelled'], 'idx_voters_list_city_cancelled');
        });

        // NEW: Composite Name Index (B-Tree) with prefix lengths
        // Critical for sorting and "Starts With" logic: "Select * order by first, father, last"
        // Using prefix lengths to stay under the 1000 byte limit: (50+50+50)*4 = 600 bytes
        DB::statement('ALTER TABLE voters_list ADD INDEX idx_voters_list_full_name_sort (first_name(50), father_name(50), last_name(50))');

        // NEW: Full-Text Index for Search
        // Standard indexes cannot handle "Middle of name" searches efficiently on millions of rows.
        // This enables: WHERE MATCH(first_name, father_name, last_name) AGAINST('+Ali +Hassan' IN BOOLEAN MODE)
        DB::statement('ALTER TABLE voters_list ADD FULLTEXT idx_voters_list_search (first_name, father_name, last_name)');


        // 2. REQUEST HEADERS - Optimization for High Transactions (Write-Heavy)
        Schema::table('request_headers', function (Blueprint $table) {
            // STRATEGY: Minimize index count to keep INSERT/UPDATE fast.
            // We remove single column indexes if a composite index already starts with that column.
            
            // 1. Main Status Filter (Covers 'request_status_id' single search too)
            $table->index(['request_status_id', 'cancelled'], 'idx_req_status_cancelled');

            // 2. Member History (Covers 'sender_id' single search too)
            $table->index(['sender_id', 'cancelled'], 'idx_req_sender_cancelled');

            // 3. Reporting/Timeline (Covers 'request_date' single search too)
            $table->index(['request_date', 'cancelled'], 'idx_req_date_cancelled');

            // 4. Specific lookups
            $table->index('reference_member_id', 'idx_req_ref_member');
            // 'ready_date' is often NULL, useful to index for "Where ready_date IS NOT NULL"
            $table->index('ready_date', 'idx_req_ready_date');
        });


        // 3. INBOX NOTIFICATIONS - Optimization for Real-time Queries
        Schema::table('inbox_notifications', function (Blueprint $table) {
            // REMOVED: 'type' single index (Low cardinality).
            // REMOVED: 'request_id' (Unless you join inbox to requests frequently, if so, keep it).
            $table->index('request_id', 'idx_inbox_req_id');

            // THE "MONEY" INDEX:
            // This single index handles the most frequent query in the app: 
            // "Show me user X's unread notifications ordered by date"
            // It covers: WHERE user_id = ? AND is_read = ? ORDER BY created_at
            $table->index(['user_id', 'is_read', 'created_at'], 'idx_inbox_user_read_time');

            // Fallback for "All Notifications" history
            // If the user clicks "View All", we ignore 'is_read'.
            $table->index(['user_id', 'created_at'], 'idx_inbox_user_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voters_list', function (Blueprint $table) {
            $table->dropIndex('idx_voters_list_phone');
            $table->dropIndex('idx_voters_list_city_cancelled');
            $table->dropIndex('idx_voters_list_full_name_sort');
            $table->dropIndex('idx_voters_list_search'); // Drop FullText
        });

        Schema::table('request_headers', function (Blueprint $table) {
            $table->dropIndex('idx_req_status_cancelled');
            $table->dropIndex('idx_req_sender_cancelled');
            $table->dropIndex('idx_req_date_cancelled');
            $table->dropIndex('idx_req_ref_member');
            $table->dropIndex('idx_req_ready_date');
        });

        Schema::table('inbox_notifications', function (Blueprint $table) {
            $table->dropIndex('idx_inbox_req_id');
            $table->dropIndex('idx_inbox_user_read_time');
            $table->dropIndex('idx_inbox_user_history');
        });
    }
};
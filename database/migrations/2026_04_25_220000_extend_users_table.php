<?php

use App\Enums\AccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'last_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('last_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('status')->default(AccountStatus::ACTIVE->value)->after('phone');
            $table->timestamp('registered_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'phone', 'status', 'registered_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('last_name', 'name');
        });
    }
};

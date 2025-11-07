<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * MODULES TABLE
         */
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        /**
         * ROLES & PERMISSIONS
         */
       Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->nullable();
    $table->string('description')->nullable();
    $table->string('guard_name')->default('web'); // 🟢 REQUIRED by Spatie
    $table->timestamps();
});


        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
    $table->unsignedBigInteger('permission_id');
    $table->unsignedBigInteger('model_id');
    $table->string('model_type');
    $table->primary(['permission_id', 'model_id', 'model_type']);
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
});

Schema::create('model_has_roles', function (Blueprint $table) {
    $table->unsignedBigInteger('role_id');
    $table->unsignedBigInteger('model_id');
    $table->string('model_type');
    $table->primary(['role_id', 'model_id', 'model_type']);
    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
});
      

        /**
         * USERS TABLE
         */
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password');
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('department_id')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * CLIENTS TABLE
         */
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('gst_no', 20)->nullable();
            $table->string('industry')->nullable();
            $table->foreignId('account_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        /**
         * CLIENT SALES TABLE
         */
        Schema::create('client_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('invoice_no', 50)->unique();
            $table->date('sale_date');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->char('currency', 3)->default('INR');
            $table->enum('payment_mode', ['UPI', 'Bank', 'Cash', 'Card'])->default('Bank');
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        /**
         * CLIENT PURCHASES TABLE
         */
        Schema::create('client_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('order_no', 50)->unique();
            $table->date('order_date');
            $table->json('items_json')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        /**
         * LEADS TABLE
         */
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->enum('source', ['web', 'referral', 'event', 'call'])->default('web');
            $table->enum('stage', ['new', 'contacted', 'quoted', 'won', 'lost'])->default('new');
            $table->decimal('value_amount', 12, 2)->default(0);
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('lost_reason')->nullable();
            $table->foreignId('converted_client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamps();
        });

        /**
         * DEALS TABLE
         */
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('account_id')->constrained('clients')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->tinyInteger('probability')->default(0);
            $table->date('expected_close_date')->nullable();
            $table->text('lost_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        /**
         * SALES TABLE
         */
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->unique()->constrained('deals')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'posted', 'reversed'])->default('pending');
            $table->date('booked_at')->nullable();
            $table->timestamps();
        });

        /**
         * TASKS TABLE
         */
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->morphs('taskable'); // lead/deal/client
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->enum('status', ['Pending', 'InProgress', 'Completed', 'Cancelled'])->default('Pending');
            $table->dateTime('due_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * TICKETS TABLE
         */
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('subject');
            $table->enum('category', ['Billing', 'Technical', 'General'])->default('General');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->enum('status', ['Open', 'Pending', 'Resolved', 'Closed'])->default('Open');
            $table->text('message');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        /**
         * ACTIVITY LOGS TABLE
         */
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->morphs('subject');
            $table->morphs('causer');
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        /**
         * NOTIFICATIONS TABLE
         */
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('client_purchases');
        Schema::dropIfExists('client_sales');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('modules');
    }
};

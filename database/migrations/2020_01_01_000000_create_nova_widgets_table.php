<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNovaWidgetsTable extends Migration
{

    public function up(): void
    {
        Schema::create(config('nova-widgets.table_name'), static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('dashboard');
            $table->string('view');
            $table->string('key');
            $table->json('options');
            $table->json('coordinates');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop(config('nova-widgets.table_name'));
    }

}

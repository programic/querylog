<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionInheritancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_inheritances', function (Blueprint $table) {
            $table->string('source_permission_name');
            $table->string('target_permission_name');
            $table->string('direction', 5);

            $table->foreign('source_permission_name')
                ->references('name')
                ->on('permissions');

            $table->foreign('target_permission_name')
                ->references('name')
                ->on('permissions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_inheritances');
    }
}

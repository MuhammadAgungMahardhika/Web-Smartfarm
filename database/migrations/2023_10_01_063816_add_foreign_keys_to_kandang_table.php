<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kandang', function (Blueprint $table) {
            $table->foreign(['id_user'], 'id_user')
                ->references(['id'])
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
            $table->foreign(['id_peternak'], 'id_peternak')
                ->references(['id'])
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kandang', function (Blueprint $table) {
            $table->dropForeign('id_user');
            $table->dropForeign('id_peternak');
        });
    }
};

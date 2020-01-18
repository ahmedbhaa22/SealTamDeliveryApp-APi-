<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('amount');
            $table->text('describtion');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('mini_dashboard_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('type', ['order','others']);

            $table->date('date');
            $table->foreign('mini_dashboard_id')->references('id')->on('mini_dashboards')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incomes');
    }
}

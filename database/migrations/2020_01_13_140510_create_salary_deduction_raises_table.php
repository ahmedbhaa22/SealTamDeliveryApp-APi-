<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryDeductionRaisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_deduction_raises', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('amount');
            $table->string('reason');
            $table->date('date');
            $table->enum('type', ['deduction','raises']);
            $table->boolean('is_used')->default(0);

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
        Schema::dropIfExists('salary_deduction_raises');
    }
}

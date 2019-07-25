<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('status',['-2','-1','0','1','2','3','4','5'])->default('0');
            $table->string('customerPhone');
            $table->string('customerName');
            $table->string('OrderNumber');
            $table->string('JobId')->nullable();
            $table->longtext('orderDest');
            $table->double('orderCost', 8, 2);
            $table->double('deliveryCost', 8, 2)->nullable();
            $table->double('companyProfit', 8, 2)->nullable();
            $table->double('expectedDeliveryCost', 8, 2);
            $table->dateTime('expectedTimeToArrive')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('resturant_id')->nullable();
            $table->foreign('resturant_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('orders');
    }
}

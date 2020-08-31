<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('misecode')->nullable();
            $table->string('pluzokusei',2)->nullable();
            $table->string('scancode_new',13)->index();
            $table->string('scancode_old',13)->nullable();
            $table->string('itemcode',20)->nullable();
            $table->integer('bumon')->nullable();
            $table->integer('hinsyu')->nullable();
            $table->string('hinmei',80)->nullable();
            $table->string('hinmei_kana',20)->nullable();
            $table->integer('pointjoflg')->nullable();
            $table->integer('nbkwbkjoflg')->nullable();
            $table->integer('urijoflg')->nullable();
            $table->integer('stanka1')->nullable();
            $table->integer('btanka1')->nullable();
            $table->integer('otanka1')->nullable();
            $table->integer('hbaika1')->nullable();
            $table->string('price2date',8)->nullable();
            $table->integer('stanka2')->nullable();
            $table->integer('btanka2')->nullable();
            $table->integer('otanka2')->nullable();
            $table->integer('hbaika2')->nullable();
            $table->integer('genka')->nullable();
            $table->integer('zeikbn')->nullable();
            $table->integer('zeiptn')->nullable();
            $table->integer('tpnnwflg')->nullable();
            $table->integer('tpnnwgaku')->nullable();
            $table->integer('kaiinflg')->nullable();
            $table->integer('kaiinbaika')->nullable();
            $table->integer('tokubaiflg')->nullable();
            $table->integer('timeflg')->nullable();
            $table->string('timestart',4)->nullable();
            $table->string('timeend',4)->nullable();
            $table->integer('timebaika')->nullable();
            $table->integer('bmflg')->nullable();
            $table->integer('bmno')->nullable();
            $table->integer('bmpkgsuryo')->nullable();
            $table->integer('bmpkggaku')->nullable();
            $table->string('seisansyacode',10)->nullable();
            $table->integer('tanpingroupno1')->nullable();
            $table->integer('tanpingroupno2')->nullable();
            $table->string('urikage_date',8)->nullable();
            $table->string('update_date',8)->nullable();
            $table->string('update_time',6)->nullable();
            $table->string('comment',40)->nullable();
            $table->integer('syain_hanbai_flg')->nullable();
            $table->integer('syain_kakaku')->nullable();
            $table->integer('tokusyu_hanbai_flg1')->nullable();
            $table->integer('tokusyu_kakaku1')->nullable();
            $table->integer('tokusyu_hanbai_flg2')->nullable();
            $table->integer('tokusyu_kakaku2')->nullable();
            $table->integer('tokusyu_hanbai_flg3')->nullable();
            $table->integer('tokusyu_kakaku3')->nullable();
            $table->integer('hontai_kakaku')->nullable();
            $table->integer('kakaku1')->nullable();
            $table->integer('kakaku2')->nullable();
            $table->integer('kakaku3')->nullable();
            $table->string('description',40)->nullable();
            $table->string('plu_url',40)->nullable();
            $table->string('image_url',40)->nullable();
            $table->string('hanbai_syudan',10)->nullable();
            $table->string('print_flg',10)->nullable();
            $table->string('size_code',5)->nullable();
            $table->string('color_code',5)->nullable();
            $table->string('reserve_chr1',5)->nullable();
            $table->string('reserve_chr2',5)->nullable();
            $table->string('reserve_chr3',5)->nullable();
            $table->string('reserve_chr4',5)->nullable();
            $table->string('reserve_chr5',5)->nullable();
            $table->integer('reserve_int1')->nullable();
            $table->integer('reserve_int2')->nullable();
            $table->integer('reserve_int3')->nullable();
            $table->integer('reserve_int4')->nullable();
            $table->integer('reserve_int5')->nullable();
            $table->string('reserve_chr6',5)->nullable();
            $table->string('reserve_chr7',5)->nullable();
            $table->string('reserve_chr8',5)->nullable();
            $table->string('reserve_chr9',5)->nullable();
            $table->string('reserve_chr10',5)->nullable();
            $table->integer('reserve_int6')->nullable();
            $table->integer('reserve_int7')->nullable();
            $table->integer('reserve_int8')->nullable();
            $table->integer('reserve_int9')->nullable();
            $table->integer('reserve_int10')->nullable();
            $table->string('hosthinmei',40)->nullable();
            $table->string('kikakumei',40)->nullable();
            $table->integer('keiryoflg')->nullable();
            $table->integer('ItemKbn')->nullable();
            $table->integer('toppingflg')->nullable();
            $table->integer('jushokuflg')->nullable();
            $table->integer('orderprintflg')->nullable();
            $table->string('orderhinmei',80)->nullable();
            $table->integer('freepriceflg')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_master');
    }
}

 <?php
 
 use Illuminate\Database\Migrations\Migration;
 use Illuminate\Database\Schema\Blueprint;
 use Illuminate\Support\Facades\Schema;
 
 return new class extends Migration
 {
     /**
      * Run the migrations.
      */
     public function up(): void
     {
         Schema::table('order_items', function (Blueprint $table) {
             if (!Schema::hasColumn('order_items', 'product_id')) {
                 $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
             }
             if (!Schema::hasColumn('order_items', 'quantity')) {
                 $table->integer('quantity')->default(1);
             }
             if (!Schema::hasColumn('order_items', 'price')) {
                 $table->decimal('price', 10, 2)->nullable();
             }
             if (!Schema::hasColumn('order_items', 'subtotal')) {
                 $table->decimal('subtotal', 10, 2)->nullable();
             }
         });
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::table('order_items', function (Blueprint $table) {
             if (Schema::hasColumn('order_items', 'product_id')) {
                 $table->dropConstrainedForeignId('product_id');
             }
             if (Schema::hasColumn('order_items', 'quantity')) {
                 $table->dropColumn('quantity');
             }
             if (Schema::hasColumn('order_items', 'price')) {
                 $table->dropColumn('price');
             }
             if (Schema::hasColumn('order_items', 'subtotal')) {
                 $table->dropColumn('subtotal');
             }
         });
     }
 };

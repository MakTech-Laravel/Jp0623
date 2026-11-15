<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_types', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->string('image')->nullable()->after('description');
            $table->string('kind')->nullable()->after('image');
            $table->boolean('show_on_industries')->default(true)->after('is_active');
        });

        // Give every existing row a unique slug so public industry URLs never break.
        $used = [];

        DB::table('location_types')->orderBy('id')->get(['id', 'name'])->each(function ($row) use (&$used): void {
            $base = Str::slug($row->name) ?: 'location-type';
            $slug = $base;
            $suffix = 1;

            while (in_array($slug, $used, true)) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $used[] = $slug;

            DB::table('location_types')->where('id', $row->id)->update(['slug' => $slug]);
        });
    }

    public function down(): void
    {
        Schema::table('location_types', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'description', 'image', 'kind', 'show_on_industries']);
        });
    }
};

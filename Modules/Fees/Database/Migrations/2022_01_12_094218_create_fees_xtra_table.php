<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeesXtraTable extends Migration
{
    public function up(): void
    {
        $column = 'record_id';
        if (! Schema::hasColumn('fm_fees_invoices', $column)) {
            Schema::table('fm_fees_invoices', function (Blueprint $blueprint) use ($column): void {
                $blueprint->foreignId($column)->unsigned()->nullable();
            });
        }

        if (! Schema::hasColumn('fm_fees_transactions', $column)) {
            Schema::table('fm_fees_transactions', function (Blueprint $blueprint) use ($column): void {
                $blueprint->foreignId($column)->unsigned()->nullable();
            });
        }

        Schema::table('fm_fees_types', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('fm_fees_types', 'type')) {
                $blueprint->string('type')->nullable()->default('fees')->comment('fees, lms');
            }
        });

        Schema::table('fm_fees_invoices', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('fm_fees_invoices', 'type')) {
                $blueprint->string('type')->nullable()->default('fees')->comment('fees, lms');
            }
        });

        Schema::table('fm_fees_transactions', function (Blueprint $blueprint): void {
            if (! Schema::hasColumn('fm_fees_transactions', 'total_paid_amount')) {
                $blueprint->string('total_paid_amount')->nullable();
            }

        });
    }

    public function down(): void
    {
        $column = 'record_id';
        if (Schema::hasColumn('fm_fees_invoices', $column)) {
            Schema::table('fm_fees_invoices', function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropColumn($column);
            });
        }

        if (Schema::hasColumn('fm_fees_transactions', $column)) {
            Schema::table('fm_fees_transactions', function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropColumn($column);
            });
        }

        Schema::table('fm_fees_types', function (Blueprint $blueprint): void {
            if (Schema::hasColumn('fm_fees_types', 'type')) {
                $blueprint->dropColumn('type');
            }
        });
        Schema::table('fm_fees_invoices', function (Blueprint $blueprint): void {
            if (Schema::hasColumn('fm_fees_invoices', 'type')) {
                $blueprint->dropColumn('type');
            }
        });

        Schema::table('fm_fees_transactions', function (Blueprint $blueprint): void {
            if (Schema::hasColumn('fm_fees_transactions', 'total_paid_amount')) {
                $blueprint->dropColumn('total_paid_amount');
            }
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LeadFeesTable extends Migration
{
    public function up(): void
    {
        $column = 'record_id';
        if (! Schema::hasColumn('sm_fees_assigns', $column)) {
            Schema::table('sm_fees_assigns', function (Blueprint $blueprint) use ($column): void {
                $blueprint->foreignId($column)->unsigned()->nullable()->constrained('student_records')->cascadephpOnDelete();
            });
        }

        if (! Schema::hasColumn('sm_fees_payments', $column)) {
            Schema::table('sm_fees_payments', function (Blueprint $blueprint) use ($column): void {
                $blueprint->foreignId($column)->unsigned()->nullable()->constrained('student_records')->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('sm_fees_discounts', $column)) {
            Schema::table('sm_fees_discounts', function (Blueprint $blueprint) use ($column): void {
                $blueprint->foreignId($column)->unsigned()->nullable()->constrained('student_records')->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('sm_fees_assign_discounts', $column)) {
            Schema::table('sm_fees_assign_discounts', function (Blueprint $blueprint) use ($column): void {
                $blueprint->foreignId($column)->unsigned()->nullable()->constrained('student_records');
            });
        }

        if (! Schema::hasColumn('sm_bank_payment_slips', $column)) {
            Schema::table('sm_bank_payment_slips', function (Blueprint $blueprint) use ($column): void {
                $blueprint->foreignId($column)->unsigned()->nullable()->constrained('student_records');
            });
        }
    }

    public function down(): void
    {
        $column = 'record_id';
        if (Schema::hasColumn('sm_fees_assigns', $column)) {
            Schema::table('sm_fees_assigns', function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropForeign([$column]);
                $blueprint->dropColumn($column);
            });
        }

        if (Schema::hasColumn('sm_fees_payments', $column)) {
            Schema::table('sm_fees_payments', function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropForeign([$column]);
                $blueprint->dropColumn($column);
            });
        }

        if (Schema::hasColumn('sm_fees_discounts', $column)) {
            Schema::table('sm_fees_discounts', function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropForeign([$column]);
                $blueprint->dropColumn($column);
            });
        }

        if (Schema::hasColumn('sm_fees_assign_discounts', $column)) {
            Schema::table('sm_fees_assign_discounts', function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropForeign([$column]);
                $blueprint->dropColumn($column);
            });
        }

        if (Schema::hasColumn('sm_bank_payment_slips', $column)) {
            Schema::table('sm_bank_payment_slips', function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropForeign([$column]);
                $blueprint->dropColumn($column);
            });
        }
    }
}

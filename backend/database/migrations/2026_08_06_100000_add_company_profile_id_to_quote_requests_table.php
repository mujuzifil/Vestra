<?php

use App\Models\CompanyProfile;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('quote_requests', 'company_profile_id')) {
                $table->foreignId('company_profile_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('company_profiles')
                    ->nullOnDelete();
            }
        });

        QuoteRequest::query()
            ->whereNull('company_profile_id')
            ->cursor()
            ->each(function (QuoteRequest $quote): void {
                $user = null;

                if ($quote->user_id) {
                    $user = User::query()->find($quote->user_id);
                }

                if ($user === null && filled($quote->email)) {
                    $user = User::query()->where('email', $quote->email)->first();

                    if ($user === null) {
                        $user = User::create([
                            'name' => $quote->full_name ?: Str::before((string) $quote->email, '@'),
                            'email' => $quote->email,
                            'phone' => $quote->phone,
                            'password' => bcrypt(Str::random(32)),
                            'status' => 'active',
                        ]);
                    }

                    if (! $quote->user_id) {
                        $quote->user_id = $user->id;
                    }
                }

                if ($user === null) {
                    return;
                }

                $profile = CompanyProfile::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $quote->company_name,
                        'primary_contact_name' => $quote->full_name,
                        'primary_contact_email' => $quote->email,
                        'primary_contact_phone' => $quote->phone,
                        'district' => $quote->district,
                        'city' => $quote->city,
                        'address' => $quote->address,
                        'country' => 'Uganda',
                    ]
                );

                $quote->company_profile_id = $profile->id;
                $quote->save();
            });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            if (Schema::hasColumn('quote_requests', 'company_profile_id')) {
                $table->dropConstrainedForeignId('company_profile_id');
            }
        });
    }
};

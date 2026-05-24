<?php

namespace App\Console\Commands;

use App\Services\PopulationService;
use Illuminate\Console\Command;
use RuntimeException;

class FetchPopulationCommand extends Command
{
    /**
     * php artisan population:fetch {country?} {--year=}
     */
    protected $signature = 'population:fetch
                            {country? : The country name to fetch data for (default: runs predefined list)}
                            {--year= : Specific year to fetch (optional)}
                            {--countries=* : Multiple countries to fetch, space-separated}';

    protected $description = 'Run the Population ETL pipeline: fetch from API-Ninjas and store in PostgreSQL';

    /** Default countries to fetch when none specified */
    private const DEFAULT_COUNTRIES = [
        'United States',
        'India',
        'China',
        'Germany',
        'Brazil',
        'Nigeria',
    ];

    public function __construct(
        private readonly PopulationService $populationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $year      = $this->option('year') ? (int) $this->option('year') : null;
        $countries = $this->resolveCountries();

        $this->info('🌍 Population ETL — starting pipeline');
        $this->info(sprintf('Countries: %s | Year filter: %s', implode(', ', $countries), $year ?? 'all'));
        $this->newLine();

        $totalStored = 0;
        $errors      = [];

        foreach ($countries as $country) {
            $this->line("  → Fetching: <comment>{$country}</comment>");

            try {
                $result = $this->populationService->runEtl($country, $year);
                $totalStored += $result['stored'];

                $this->line("    ✅ Stored <info>{$result['stored']}</info> record(s)");
            } catch (RuntimeException $e) {
                $errors[] = "{$country}: {$e->getMessage()}";
                $this->line("    ❌ Failed: <fg=red>{$e->getMessage()}</>");
            }
        }

        $this->newLine();
        $this->info("✅ Pipeline complete — {$totalStored} total record(s) stored.");

        if (!empty($errors)) {
            $this->warn(count($errors) . ' error(s) occurred:');
            foreach ($errors as $err) {
                $this->warn("  - {$err}");
            }
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function resolveCountries(): array
    {
        // Single country argument
        if ($country = $this->argument('country')) {
            return [$country];
        }

        // --countries=USA --countries=India
        $optionCountries = $this->option('countries');
        if (!empty($optionCountries)) {
            return $optionCountries;
        }

        // Default list
        return self::DEFAULT_COUNTRIES;
    }
}
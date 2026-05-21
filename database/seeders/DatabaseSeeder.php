<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(OrganizationSectionSeeder::class);
        $this->call(QcFormTemplateSeeder::class);
        $this->call(TemplateApprovalStepSeeder::class);

        $this->call(FinishMillMasterDataRecordSeeder::class);
        $this->call(FinishMill419MasterDataRecordSeeder::class);
        $this->call(Crusher4MasterDataRecordSeeder::class);
        $this->call(CoalMill4MasterDataRecordSeeder::class);
        $this->call(Kiln4MasterDataRecordSeeder::class);
        $this->call(RawMill4MasterDataRecordSeeder::class);
    }
}

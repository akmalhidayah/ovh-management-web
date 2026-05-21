<?php

namespace Database\Seeders;

use App\Models\MasterDataRecord;
use Illuminate\Database\Seeder;

class Crusher4MasterDataRecordSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['402BC01', 'ST-4201-CR-402-BC01', '50003948', 'CRUSHER 4', 'BELT CONVEYOR'],
            ['402BC02', 'ST-4201-CR-402-BC02', '50003949', 'CRUSHER 4', 'BELT CONVEYOR'],
            ['402BUILD', 'ST-4201-CR-402-BUILD', '10001340', 'CRUSHER 4', 'BANGUNAN LIMESTONE CRUSHER IV'],
            ['402CCR01', 'ST-4201-CR-402-CCR01', '40000080', 'CRUSHER 4', 'CENTRAL CONTROL ROOM'],
            ['402CR01', 'ST-4201-CR-402-CR01', '50003943', 'CRUSHER 4', 'IMPACT CRUSHER'],
            ['402CS01', 'ST-4201-CR-402-CS01', '50003945', 'CRUSHER 4', 'CHAIN SCRAPPER'],
            ['402DC01', 'ST-4201-CR-402-DC01', '70000981', 'CRUSHER 4', 'DUST COLLECTOR'],
            ['402FA01', 'ST-4201-CR-402-FA01', '50003951', 'CRUSHER 4', 'FILTER FAN'],
            ['402HO01', 'ST-4201-CR-402-HO01', '50003957', 'CRUSHER 4', 'OVER HEAD CRANE'],
            ['402MBS01', 'ST-4201-CR-402-MBS01', '50048204', 'CRUSHER 4', 'ELECTROMAGNETIC BELT SEPARATOR'],
            ['402MDB', 'ST-4201-CR-402-MDB01', '20003546', 'CRUSHER 4', 'MAIN DISTRB. BOARD'],
            ['402OP02', 'ST-4201-CR-402-OP02', '50003947', 'CRUSHER 4', 'OIL PUMP'],
            ['402PC01', 'ST-4201-CR-402-PC01', '50003944', 'CRUSHER 4', 'PAN CONVEYOR'],
            [null, 'ST-4201-CR-402-PIPE', 'N/A', 'CRUSHER 4', 'PIPING LIMESTONE CRUSHER IV'],
            ['402SC01', 'ST-4201-CR-402-SC01', '50003952', 'CRUSHER 4', 'SCREW CONVEYOR'],
            ['402TR01', 'ST-4201-CR-402-TR01', '20003547', 'CRUSHER 4', 'TRANSFORMATOR LIMESTONE CRUSHER'],
            ['402VF01', 'ST-4201-CR-402-VF01', '50003946', 'CRUSHER 4', 'VIBRATING SCREEN'],

            ['405BC01', 'ST-4201-CR-405-BC01', '50003950', 'CRUSHER 4', 'BELT CONVEYOR'],
            ['405BS01', 'ST-4201-CR-405-BS01', '40000079', 'CRUSHER 4', 'BELT SCALE'],
            ['405DC01', 'ST-4201-CR-405-DC01', '70000982', 'CRUSHER 4', 'DUST COLLECTOR'],
            ['405FA01', 'ST-4201-CR-405-FA01', '50003953', 'CRUSHER 4', 'FILTER FAN'],
            ['405FG01', 'ST-4201-CR-405-FG01', '50003954', 'CRUSHER 4', 'FLOW CONTROL GATE'],
            ['405RS01', 'ST-4201-CR-405-RS01', '50003955', 'CRUSHER 4', 'STACKER UNIT'],
            ['405RS01BC01', 'ST-4201-CR-405-RS01BC01', '50003956', 'CRUSHER 4', 'SHUTTLE CONVEYOR'],

            ['403BC01', 'ST-4201-CR-403-BC01', '50003970', 'CRUSHER 4', 'BELT CONVEYOR'],
            ['403BC02', 'ST-4201-CR-403-BC02', '50003972', 'CRUSHER 4', 'BELT CONVEYOR'],
            ['403BS01', 'ST-4201-CR-403-BS01', '40000083', 'CRUSHER 4', 'BELT SCALE'],
            ['403BUILD', 'ST-4201-CR-403-BUILD', '10001342', 'CRUSHER 4', 'BANGUNAN CLAY CRUSHER IV'],
            ['403CCR01', 'ST-4201-CR-403-CCR01', '40000084', 'CRUSHER 4', 'CENTRAL CONTROL ROOM'],
            ['403CR01', 'ST-4201-CR-403-CR01', '50003968', 'CRUSHER 4', 'DOUBLE ROLLER CRUSHER'],
            ['403CS01', 'ST-4201-CR-403-CS01', '50017732', 'CRUSHER 4', 'CHAIN SCRAPER'],
            ['403CS02', 'ST-4201-CR-403-CS02', '50003971', 'CRUSHER 4', 'CHAIN SCRAPER'],
            ['403HO01', 'ST-4201-CR-403-HO01', '50003976', 'CRUSHER 4', 'OVER HEAD CRANE'],
            ['403MDB', 'ST-4201-CR-403-MDB01', '20003550', 'CRUSHER 4', 'MAIN DISTRB. BOARD'],
            ['403PC01', 'ST-4201-CR-403-PC01', '50003969', 'CRUSHER 4', 'PAN CONVEYOR'],
            ['403TR01', 'ST-4201-CR-403-TR01', '20003551', 'CRUSHER 4', 'TRANSFORMATOR CLAY CRUSHER IV'],

            [null, 'ST-4201-CR-406-BS01', null, 'CRUSHER 4', 'BELT SCALE'],
            ['406RS01', 'ST-4201-CR-406-RS01', '50003973', 'CRUSHER 4', 'STACKER UNIT'],
            ['406RS01BC01', 'ST-4201-CR-406-RS01BC01', '50003974', 'CRUSHER 4', 'SHUTTLE CONVEYOR'],
            ['406RS01BC02', 'ST-4201-CR-406-RS01BC02', '50003975', 'CRUSHER 4', 'MOVABLE CONVEYOR'],

            ['404BUILD', 'ST-4201-CR-404-BUILD', '10001343', 'CRUSHER 4', 'BANGUNAN SILIKA CRUSHER IV'],
            ['404CCR01', 'ST-4201-CR-404-CCR01', '40000086', 'CRUSHER 4', 'CENTRAL CONTROL ROOM'],
            ['404CR01', 'ST-4201-CR-404-CR01', '50003977', 'CRUSHER 4', 'JAW CRUSHER'],
            ['404HO01', 'ST-4201-CR-404-HO01', '50003980', 'CRUSHER 4', 'OVER HEAD CRANE'],
            ['404MDB', 'ST-4201-CR-404-MDB01', '20003553', 'CRUSHER 4', 'MAIN DISTRB. BOARD'],
            ['404PC01', 'ST-4201-CR-404-PC01', '50003978', 'CRUSHER 4', 'PAN CONVEYOR'],
            ['404TR01', 'ST-4201-CR-404-TR01', '20003554', 'CRUSHER 4', 'TRANSFORMATOR SILIKA CRUSHER IV'],

            ['407BC01', 'ST-4201-CR-407-BC01', '50003979', 'CRUSHER 4', 'BELT CONVEYOR'],
            ['407BS01', 'ST-4201-CR-407-BS01', '40000085', 'CRUSHER 4', 'BELT SCALE'],
            ['407RS01', 'ST-4201-CR-407-RS01', '20003552', 'CRUSHER 4', 'TRIPPER UNIT'],
        ];

        $records = [];
        $now = now();

        foreach (array_keys(MasterDataRecord::documentCategories()) as $documentCategory) {
            foreach ($rows as [$equipment, $funcLocation, $equipmentNo, $area, $description]) {
                $equipment = $this->nullableValue($equipment);
                $equipmentNo = $this->nullableValue($equipmentNo);

                $records[] = [
                    'document_category' => $documentCategory,
                    'year' => '2026',
                    'func_location' => trim((string) $funcLocation),
                    'equipment_no' => $equipmentNo,
                    'section_no' => $equipment,
                    'description' => trim((string) $description),
                    'plant' => 'TONASA 4',
                    'area' => trim((string) $area),
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($records, 1000) as $chunk) {
            MasterDataRecord::upsert(
                $chunk,
                ['document_category', 'func_location'],
                [
                    'year',
                    'equipment_no',
                    'section_no',
                    'description',
                    'plant',
                    'area',
                    'status',
                    'updated_at',
                ]
            );
        }
    }

    private function nullableValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || strtoupper($value) === 'N/A' || $value === '-') {
            return null;
        }

        return $value;
    }
}
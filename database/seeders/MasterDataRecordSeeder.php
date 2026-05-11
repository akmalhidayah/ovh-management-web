<?php

namespace Database\Seeders;

use App\Models\MasterDataRecord;
use Illuminate\Database\Seeder;

class MasterDataRecordSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['ST-4302-RM-405-BC02', '20007019', '405BC02M1', 'MOTOR BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC02', '20007020', '405BC02MCC1', 'MCC6, BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC02', '40003393', '405BC02I1', 'CURRENT', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC02', '40003394', '405BC02S9', 'SPEED MONITOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC02', '40003395', '405BC02X1-01', 'ASKEW RUNNING', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC02', '40003396', '405BC02X1-02', 'ASKEW RUNNING', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC02', '40003397', '405BC02X1-03', 'ASKEW RUNNING', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC02', '40003398', '405BC02X1-04', 'ASKEW RUNNING', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC02', '50005732', '405BC02', 'BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC02', '50008947', '405BC02R', 'GEAR BOX BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC03', '20007021', '405BC03M1', 'MOTOR BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC03', '20007022', '405BC03MCC1', 'MCC6, BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC03', '40003399', '405BC03S9', 'SPEED MONITOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC03', '40003400', '405BC03X1-01', 'ASKEW RUNNING', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC03', '40003401', '405BC03X1-02', 'ASKEW RUNNING', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC03', '40003402', '405BC03X1-03', 'ASKEW RUNNING', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC03', '40003403', '405BC03X1-04', 'ASKEW RUNNING', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC03', '50005733', '405BC03', 'BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC03', '50008948', '405BC03R', 'GEAR BOX BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC04', '20007023', '405BC04M1', 'MOTOR BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC04', '20007024', '405BC04MCC1', 'MCC6, BELT CONVEYOR', 'TONASA 4', 'RAW MILL'],
            ['ST-4302-RM-405-BC04', '40003404', '405BC04S9', 'SPEED MONITOR', 'TONASA 4', 'RAW MILL'],
        ];

        foreach (array_keys(MasterDataRecord::documentCategories()) as $documentCategory) {
            foreach ($rows as [$funcLocation, $equipmentNo, $sectionNo, $description, $plant, $area]) {
                MasterDataRecord::updateOrCreate(
                    [
                        'document_category' => $documentCategory,
                        'equipment_no' => $equipmentNo,
                    ],
                    [
                        'year' => '2026',
                        'func_location' => $funcLocation,
                        'section_no' => $sectionNo,
                        'description' => $description,
                        'plant' => $plant,
                        'area' => $area,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}

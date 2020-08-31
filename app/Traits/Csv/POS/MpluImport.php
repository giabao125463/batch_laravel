<?php

namespace App\Traits\Csv\POS;

use App\Repositories\ItemMasterRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MpluImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private $itemMasterRepository;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $this->itemMasterRepository = app()->make(ItemMasterRepository::class);
        $posConfig = config('pos');
        $tsvHeaders = data_get($posConfig, 'tsv_headers.m_plu');
        Log::info('Start chunk with size: ' . $collection->count());
        foreach ($collection->toArray() as $item) {
            $data = array_combine($tsvHeaders, $item);
            $result = $this->itemMasterRepository->findByCol('scancode_new', $data['scancode_new']);
            if (is_null($result)) {
                $this->itemMasterRepository->create($data);
                Log::info("Created new with scancode_new = {$data['scancode_new']}");
            } else {
                $this->itemMasterRepository->update($data, $result['id']);
                Log::info("Updated item with scancode_new = {$result['scancode_new']}");
            }
        }
    }

    /**
     * Setting number items of block
     *
     * @return integer
     */
    public function chunkSize(): int
    {
        return config('excel.imports.csv.chunk_size');
    }
}

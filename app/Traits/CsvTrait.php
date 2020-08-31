<?php
namespace App\Traits;

use App\Traits\Csv\ExportCollection;
use App\Traits\Csv\ImportCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

/**
 * Trait CsvTrait
 */
trait CsvTrait
{
    /**
     * 配列をCSVに変換して出力
     *
     * @param string $fileName
     * @param array $csvData
     * @param boolean $headings
     * @param boolean $isSJIS
     * @return void
     */
    public function outputCsv(string $fileName, array $csvData, bool $headings = true, $isSJIS = true)
    {
        $collection = new Collection($csvData);
        $export     = new ExportCollection($collection, $headings);
        // Excel support only UTF-8
        if (! $isSJIS) {
            return Excel::download($export, $fileName, \Maatwebsite\Excel\Excel::CSV);
        }
        // SJIS response implementation
        if (!empty($csvData)) {
            // Create fake header to keep EMPTY cells of CSV will be removed automaticlly by this Libarry
            $emptyHeader = range(0, count($collection->first()) - 1);
            // Add fake header at the 1st line to keep all columns
            $collection->prepend($emptyHeader);
        }
        $export     = new ExportCollection($collection, $headings);
        $rawContent = Excel::raw($export, \Maatwebsite\Excel\Excel::CSV);
        // Remove fake csv header at 1st line
        $rawContent = substr($rawContent, strpos($rawContent, "\n") + 1);
        $response   = response()->streamDownload(function () use ($rawContent) {
            echo mb_convert_encoding($rawContent, 'sjis-win', 'UTF-8');
        }, $fileName);
        $response->headers->set('Content-Type', 'text/csv;charset=sjis-win');
        $response->send();
    }

    /**
     * Export CSV to file
     *
     * @param string $fileName
     * @param array $csvData
     * @param array $header
     * @param boolean $isSJIS
     * 
     * @return void
     */
    public function outPutCsvToFile(string $fileName, array $csvData, $header = [], $isSJIS = true)
    {
        $collection = new Collection($csvData);

        // Add header at the 1st line
        $collection->prepend($header);

        $export     = new ExportCollection($collection);
        $rawContent = Excel::raw($export, \Maatwebsite\Excel\Excel::CSV); 
        if ($isSJIS) {
            $rawContent = mb_convert_encoding($rawContent, 'sjis', 'UTF-8');
        }

        file_put_contents($fileName, $rawContent);
    }

    /**
     * Output csv with line ending CRLF
     *
     * @param string $fileName
     * @param array $csvData
     * @param array $header
     * @param boolean $isSJIS
     * 
     * @return void
     */
    public function exportCsvToFile(string $fileName, array $csvData, $header = [], $isSJIS = true)
    {
        config(['excel.exports.csv.line_ending' => "\r\n"]);
        $this->outPutCsvToFile($fileName, $csvData, $header, $isSJIS);
    }

    /**
     * 配列をTSVに変換して出力
     *
     * @param string $fileName
     * @param array $csvData
     * @param boolean $headings
     * @param boolean $isSJIS
     * @return void
     */
    public function outputTsv(string $fileName, array $csvData, bool $headings = true, $isSJIS = true)
    {
        config(['excel.exports.csv.delimiter' => "\t"]);
        $this->outputCsv($fileName, $csvData, $headings, $isSJIS);
    }

    /**
     * CSVの文字列を配列に変換して返却
     *
     * @param string $file
     * @return array
     */
    public function csvFileToArray(string $file = null)
    {
        if (! file_exists($file)) {
            return false;
        }

        return Excel::toArray(new ImportCollection, $file)[0]; // [0] cause of Excel lib return by sheets index
    }

    /**
     * Append Header for csv/tsv file
     *
     * @param $fileName String
     * @param $header String
     * @param $data String
     *
     * @return void
     */
    public function appendHeaderForCsv($fileName, $header, $data)
    {
        Storage::put($fileName, $header);
        Storage::append($fileName, $data);
    }

    /**
     * Remove some special content in cell such as: ','
     *
     * @param string $string
     * @return string
     */
    public function removeSpecialContent($string)
    {
        foreach(config('csv.exclude_characters') as $char) {
            $string = str_replace($char, '', $string);
        }

        return $string;
    }
}

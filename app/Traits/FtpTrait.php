<?php
namespace App\Traits;

use Illuminate\Support\Facades\Storage;

/**
 * Trait FtpTrait
 */
trait FtpTrait
{
    /**
     * Copy file from Ftp to local storage
     *
     * @param $fileName String
     * @param $folder String
     *
     * @return bool
     */
    public function copyFileFromFtpToLocal($fileName, $folder = './')
    {
        $filePath = $folder . $fileName;
        if (Storage::disk('ftp-pos')->exists($filePath)) {
            if (Storage::exists($fileName)) {
                Storage::delete($fileName);
            }
            Storage::writeStream($fileName, Storage::disk('ftp-pos')->readStream($filePath));

            return true;
        }

        return false;
    }

    /**
     * Append header to file
     *
     * @param string $header
     * @param string $file
     * @return void
     */
    public function appendHeaderLine($header, $file)
    {
        $header .= "\n";
        $handle = fopen($file, "r+");
        $len = strlen($header);
        $finalLen = filesize($file) + $len;
        $cacheOld = fread($handle, $len);
        rewind($handle);
        $i = 1;
        while (ftell($handle) < $finalLen) {
            fwrite($handle, $header);
            $header = $cacheOld;
            $cacheOld = fread($handle, $len);
            fseek($handle, $i * $len);
            $i++;
        }
    }
}

<?php declare(strict_types=1);

namespace NZTim\Geolocate;

use Illuminate\Console\Command;

class UpdateGeoDatabaseCommand extends Command
{
    protected $signature = 'geo:update';

    protected $description = 'Update the Maxmind GeoIP2 database';

    public function handle()
    {
        try {
            $gzdata = file_get_contents('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&suffix=tar.gz&license_key=' . config('services.maxmind_key'));
            $md5sum = file_get_contents('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&suffix=tar.gz.md5&license_key=' . config('services.maxmind_key'));
        } catch (\Throwable $e) {
            log_error('comms', 'Error downloading Maxmind geolocation database and checksumUnable to update GeoLite database: ' . $e->getMessage());
            return;
        }
        if (md5($gzdata) !== $md5sum) {
            log_error('comms', 'Downloaded Maxmind db but md5 check failed');
            return;
        }
        $basepath = storage_path('app/geolite2');
        if (!file_exists(storage_path('app/geolite2'))) {
            mkdir(storage_path('app/geolite2'));
        }
        $gzfile = 'country.tar.gz';
        $mmdb = 'country.mmdb';
        file_put_contents("{$basepath}/{$gzfile}", $gzdata);
        $phar = new \PharData("{$basepath}/{$gzfile}");
        $subfolder = $phar->getFilename();
        $phar->extractTo($basepath);
        if (file_exists("{$basepath}/{$mmdb}")) {
            unlink("{$basepath}/{$mmdb}");
        }
        copy("{$basepath}/{$subfolder}/GeoLite2-Country.mmdb", "{$basepath}/{$mmdb}");
        unlink("{$basepath}/{$gzfile}");
        $this->rmdirRecursive("{$basepath}/{$subfolder}");
        //
        $size = intval(filesize("{$basepath}/{$mmdb}") / 1_000_000);
        $message = "GeoIP2 database successfully updated, new size is: {$size} Mb";
        log_info('laravel', $message);
    }

    private function rmdirRecursive($dir): void
    {
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (is_dir("$dir/$file")) {
                $this->rmdirRecursive("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }
        rmdir($dir);
    }
}

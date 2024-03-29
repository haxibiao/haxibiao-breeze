<?php

namespace Haxibiao\Breeze\Console\Cdn;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * 清理Cloudfare Cache
 * --host ：
 *  代表清理该域名下的所有Cache，如果不传代表清理该账号下所有的域名
 *  不要带http协议。
 *  eg. php artisan cloudflare:cache:purge --host=example.com --host=example2.com
 */
class CloudfareCachePurge extends Command
{
    protected $name = 'cloudflare:cache:purge';

    protected $signature = 'cloudflare:cache:purge {--cfkey= : cloudflare api key} {--cfemail= : cloudflare email}
        {--host=* : One or more hosts that should be removed from the cache.}';

    protected $description = 'Purge CloudFlare\'s cache.';

    protected $cust_email;
    protected $cust_xauth;

    public function __construct()
    {
        parent::__construct();

    }

    public function handle()
    {
        $this->cust_email = $this->option('cfemail') ?? config('breeze.cloudfare.email');
        $this->cust_xauth = $this->option('cfkey') ?? config('breeze.cloudfare.key');

        $hosts = $this->getHosts();
        $zones = $this->getZones();
        foreach ($hosts as $cust_domain) {
            if ($this->cust_email == "" || $this->cust_xauth == "" || $cust_domain == "") {
                return;
            }

            $cust_zone = $zones[$cust_domain];
            if (blank($cust_zone)) {
                continue;
            }
            $ch_purge = curl_init();
            curl_setopt($ch_purge, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/" . $cust_zone . "/purge_cache");
            curl_setopt($ch_purge, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch_purge, CURLOPT_RETURNTRANSFER, 1);
            $headers = [
                'X-Auth-Email: ' . $this->cust_email,
                'X-Auth-Key: ' . $this->cust_xauth,
                'Content-Type: application/json',
            ];
            $data = json_encode(array("purge_everything" => true));
            curl_setopt($ch_purge, CURLOPT_POST, true);
            curl_setopt($ch_purge, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch_purge, CURLOPT_HTTPHEADER, $headers);

            $result = json_decode(curl_exec($ch_purge), true);
            curl_close($ch_purge);
            if (data_get($result, 'success') == true) {
                $this->info("$cust_domain purge successful");
            } else {
                $this->error("$cust_domain purge fail");
            }
        }
    }

    public function getHosts()
    {
        $hosts = $this->option('host');
        if (!blank($hosts)) {
            return Arr::wrap($hosts);
        }
        return array_keys($this->getZones());
    }

    public function getZones($page = 1, $per_page = 100)
    {
        $cache_key = sprintf('cloudflare_zones_%s', $this->cust_email);
        return Cache::remember($cache_key, 60, function () use ($page, $per_page) {
            $ch_query = curl_init();
            curl_setopt($ch_query, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones?status=active&page=$page&per_page=$per_page&order=status&direction=desc&match=all");
            curl_setopt($ch_query, CURLOPT_RETURNTRANSFER, 1);
            $qheaders = array(
                'X-Auth-Email: ' . $this->cust_email,
                'X-Auth-Key: ' . $this->cust_xauth,
                'Content-Type: application/json',
            );
            curl_setopt($ch_query, CURLOPT_HTTPHEADER, $qheaders);
            $curl_result = curl_exec($ch_query);
            $qresult     = json_decode($curl_result, true);
            curl_close($ch_query);

            if (Arr::get($qresult, 'success') != true) {
                throw new \Exception("getZones fail !!! raw cloudflare result:" . $curl_result);
            }

            $result = $qresult['result'];
            if (blank($result)) {
                throw new \Exception("getZones result null");
            }

            return array_combine(
                data_get($result, '*.name'),
                data_get($result, '*.id')
            );
        });
    }
}

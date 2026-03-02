<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class EurekaRegister extends Command
{
    protected $signature = 'eureka:register';
    protected $description = 'Register service with Eureka and send heartbeats';

    public function handle()
    {
        $eurekaUrl = env('EUREKA_URL');
        $appName = env('EUREKA_APP_NAME', 'PRODUCT-SERVICE');
        $host = env('EUREKA_HOST', '192.168.11.153');
        $port = env('EUREKA_PORT', 8000);
        $healthUrl = env('EUREKA_HEALTH_CHECK_URL', "http://$host:$port/api/v1/health");
        $instanceId = "$host:$appName:$port";

        // 1. Enregistrement initial
        $payload = [
            'instance' => [
                'instanceId' => $instanceId,
                'app' => $appName,
                'hostName' => $host,
                'ipAddr' => $host,
                'status' => 'UP',
                'port' => ['$' => $port, '@enabled' => true],
                'healthCheckUrl' => $healthUrl,
                'statusPageUrl' => $healthUrl,
                'homePageUrl' => "http://$host:$port",
                'dataCenterInfo' => [
                    '@class' => 'com.netflix.appinfo.InstanceInfo$DefaultDataCenterInfo',
                    'name' => 'MyOwn',
                ],
            ],
        ];

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post("$eurekaUrl/apps/$appName", $payload);

        if ($response->successful()) {
            $this->info("Registered with Eureka successfully.");
        } else {
            $this->error("Registration failed: " . $response->body());
            return 1;
        }

        // 2. Boucle de heartbeat
        $this->info("Starting heartbeat loop...");
        while (true) {
            sleep(30);
            $heartbeat = Http::put("$eurekaUrl/apps/$appName/$instanceId");
            if ($heartbeat->successful()) {
                $this->line("Heartbeat sent at " . now());
            } else {
                $this->warn("Heartbeat failed: " . $heartbeat->status());
                // Tentative de ré-enregistrement si l'instance a expiré
                if ($heartbeat->status() === 404) {
                    $this->warn("Instance not found, re-registering...");
                    Http::post("$eurekaUrl/apps/$appName", $payload);
                }
            }
        }
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EurekaService
{
    private $eurekaUrl;
    private $appName;
    private $instanceId;
    private $hostName;
    private $port;

    public function __construct()
    {
        $this->eurekaUrl   = env('EUREKA_URL', 'http://localhost:8761/eureka');
        $this->appName     = env('APP_NAME', 'product-service');
        $this->hostName    = env('APP_HOST', 'localhost');
        $this->port        = env('APP_PORT', 8000);
        $this->instanceId  = $this->hostName . ':' . strtolower($this->appName) . ':' . $this->port;
    }

    // Enregistrer le service dans Eureka
    public function register()
    {
        $payload = [
            'instance' => [
                'instanceId'        => $this->instanceId,
                'hostName'          => $this->hostName,
                'app'               => strtoupper($this->appName),
                'ipAddr'            => '127.0.0.1',
                'status'            => 'UP',
                'overriddenstatus'  => 'UNKNOWN',
                'port'              => [
                    '$'        => $this->port,
                    '@enabled' => true,
                ],
                'securePort' => [
                    '$'        => 443,
                    '@enabled' => false,
                ],
                'healthCheckUrl'    => 'http://' . $this->hostName . ':' . $this->port . '/api/health',
                'statusPageUrl'     => 'http://' . $this->hostName . ':' . $this->port . '/api/health',
                'homePageUrl'       => 'http://' . $this->hostName . ':' . $this->port,
                'vipAddress'        => strtolower($this->appName),
                'dataCenterInfo'    => [
                    '@class' => 'com.netflix.appinfo.InstanceInfo$DefaultDataCenterInfo',
                    'name'   => 'MyOwn',
                ],
                'metadata' => [
                    'swagger' => 'http://' . $this->hostName . ':' . $this->port . '/api/documentation',
                ],
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post($this->eurekaUrl . '/apps/' . strtoupper($this->appName), $payload);

            if ($response->successful()) {
                Log::info('✅ Service enregistré dans Eureka : ' . $this->instanceId);
                return true;
            }

            Log::error('❌ Erreur Eureka : ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('❌ Eureka non disponible : ' . $e->getMessage());
            return false;
        }
    }

    // Envoyer un heartbeat toutes les 30 secondes
    public function heartbeat()
    {
        try {
            $response = Http::put(
                $this->eurekaUrl . '/apps/' . strtoupper($this->appName) . '/' . $this->instanceId
            );

            if ($response->successful()) {
                Log::info('💓 Heartbeat Eureka envoyé');
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('❌ Heartbeat Eureka échoué : ' . $e->getMessage());
            return false;
        }
    }

    // Se désenregistrer de Eureka
    public function deregister()
    {
        try {
            Http::delete(
                $this->eurekaUrl . '/apps/' . strtoupper($this->appName) . '/' . $this->instanceId
            );
            Log::info('👋 Service désenregistré de Eureka');
        } catch (\Exception $e) {
            Log::error('❌ Désenregistrement Eureka échoué : ' . $e->getMessage());
        }
    }
}
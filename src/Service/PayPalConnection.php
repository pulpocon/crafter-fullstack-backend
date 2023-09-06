<?php

declare(strict_types=1);

namespace App\Service;

use PayPalCheckoutSdk\Core\PayPalEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

class PayPalConnection
{
    private PayPalHttpClient $client;
    private string $currency;

    public function __construct(
        string $paypalClientId,
        string $paypalClientSecret,
        string $paypalClientEnvironment,
        string $paypalCurrency
    ) {
        $this->currency = $paypalCurrency;
        $this->client = new PayPalHttpClient($this->environment(
            $paypalClientId,
            $paypalClientSecret,
            $paypalClientEnvironment
        ));
    }

    private function environment(
        string $paypalClientId,
        string $paypalClientSecret,
        string $paypalClientEnvironment
    ) : PayPalEnvironment {

        if ('prod' !== $paypalClientEnvironment) {
            return new SandboxEnvironment(
                $paypalClientId,
                $paypalClientSecret
            );
        }

        return new ProductionEnvironment(
            $paypalClientId,
            $paypalClientSecret
        );
    }

    public function client() : PayPalHttpClient
    {
        return $this->client;
    }
    
    public function currency() : string
    {
        return $this->currency;
    }
}
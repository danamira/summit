<?php

namespace App\Services;

class GPT
{
    public static function getClient(): \OpenAI\Client
    {
        $apiKey= env('OPENAI_API_KEY');
        return \OpenAI::client($apiKey);
    }
}
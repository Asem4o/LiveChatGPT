<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class ChatGPTService
{
    private Client $client;


    public function __construct()
    {
        $this->client = new Client();

    }

    public function search(string $query): string
    {
        $apiKey = $_ENV['CHATGPT_API_KEY'] ;

        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ];

        $body = json_encode([
            'model' => "gpt-4-turbo",
            'messages' => [
                ['role' => 'user', 'content' => $query]
            ],
            'tools' => [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'get_current_weather',
                        'description' => 'Get the current weather in a given location',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'location' => [
                                    'type' => 'string',
                                    'description' => 'The city and state, e.g. San Francisco, CA'
                                ],
                                'unit' => [
                                    'type' => 'string',
                                    'enum' => ['celsius', 'fahrenheit']
                                ]
                            ],
                            'required' => ['location']
                        ]
                    ]
                ]
            ],
            'tool_choice' => 'auto'
        ]);

        try {
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => $headers,
                'body' => $body
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['choices'][0]['message']['content'] ?? 'No response';
        } catch (GuzzleException $e) {
            return 'Error: ' . $e->getMessage();
        }
    }


}

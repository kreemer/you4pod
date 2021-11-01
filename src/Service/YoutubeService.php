<?php

namespace App\Service;

use App\DTO\Youtube\ChannelDTO;
use App\DTO\Youtube\ChannelResponse;
use App\DTO\Youtube\PlaylistDTO;
use App\DTO\Youtube\PlaylistItemDTO;
use App\DTO\Youtube\SearchDTO;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YoutubeService
{

    private HttpClientInterface $httpClient;
    private SerializerInterface $serializer;
    private String $apiKey;
    private String $baseUrl;

    /**
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client, SerializerInterface $serializer)
    {
        $this->httpClient = $client;
        $this->serializer = $serializer;
        $this->apiKey = 'AIzaSyDy275EAXs0oOlzxRHfAMgR55lEnkhGPsc';
        $this->baseUrl= 'https://www.googleapis.com/youtube';
    }

    public function enumerateType(string $url): ?array
    {
        $patterns = [
            'playlist' => [
                '/(?:http[s]?:\/\/)?(?:\w+\.)?youtube.com\/playlist\?list=([\w_-]+)(?:&.*)?/i',
                '/(?:http[s]?:\/\/)?(?:\w+\.)?youtube.com\/watch\?v=[\w_-]+\&list=([\w_-]+)(?:&.*)?/i'
            ],
            'channel' => [
                '/(?:http[s]?:\/\/)?(?:\w+\.)?youtube.com\/user\/([\w_-]+)(?:\?.*)?/i',
                '/(?:http[s]?:\/\/)?(?:\w+\.)?youtube.com\/channel\/([\w_-]+)(?:\?.*)?/i',
                '/(?:http[s]?:\/\/)?(?:\w+\.)?youtube.com\/channel\/([\w_-]+)(?:\?.*)?/i',
                '/(?:http[s]?:\/\/)?(?:\w+\.)?youtube.com\/([\w_-]+)(?:\?.*)?/i'
            ],
            'video' => [
                '/(?:http[s]?:\/\/)?(?:\w+\.)?youtube.com\/watch\?v=([\w_-]+)(?:&.*)?/i',
                '/(?:http[s]?:\/\/)?(?:\w+\.)?youtube.com\/shorts\/([\w_-]+)(?:&.*)?/i',
                '/(?:http[s]?:\/\/)?youtu.be\/([\w_-]+)(?:\?.*)?/i',
            ],
        ];

        foreach ($patterns as $type => $regexList) {
            foreach ($regexList as $regex) {
                $result = [];
                if (!empty(preg_match($regex, $url, $result))) {
                    return [
                        'type' => $type,
                        'id' => $result[1],
                    ];
                }
            }
        }
        return null;
    }

    public function getPlaylistById(string $playlistId): array
    {
        $response = $this->httpClient->request(
            'GET',
            $this->baseUrl . '/v3/playlists',
            [
                'query' => [
                    'id' => $playlistId,
                    'key' => $this->apiKey,
                    'part' => 'snippet,contentDetails',
                ]
            ]
        );

        return $response->toArray();
    }
}
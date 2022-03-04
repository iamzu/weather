<?php
namespace Drizzle\Weather;



use GuzzleHttp\Client;
use Drizzle\Weather\Exceptions\InvalidArgumentException;
use Drizzle\Weather\Exceptions\Exception;
use Drizzle\Weather\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;

class Weather
{
    protected  $key;
    protected $guzzleOptions = [];

    /**
     * @param  string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return new Client($this->guzzleOptions);
    }

    /**
     * @param  array  $guzzleOptions
     */
    public function setGuzzleOptions($guzzleOptions): void
    {
        $this->guzzleOptions = $guzzleOptions;
    }

    /**
     * @param  string|integer  $city
     * @param  string  $type
     * @param  string  $format
     * @throws Exception
     * @return false|ResponseInterface|string
     */
    private function getWeather($city,$type = 'base',$format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        if(!in_array(strtolower($format),['xml','json'])){
            throw new InvalidArgumentException("Invalid response format：{$format}");
        }

        if(!in_array(strtolower($type),['base','all'])){
            throw new InvalidArgumentException("Invalid type value(base/all)：{$type}");
        }


        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => strtoupper($format),
            'extensions' =>  $type,
        ]);

        try {
            $response = $this->getHttpClient()->get($url,[
                'query' => $query
            ])->getBody()->getContents();
            return 'json' === $format ? json_encode($response, JSON_THROW_ON_ERROR) : $response;
        }catch (\Exception $e){
            var_dump($e->getMessage());
            throw new HttpException($e->getMessage(),$e->getCode(),$e);
        }
    }

    /**
     * 获取实时天气
     * @param  string|integer  $city
     * @param  string  $format
     * @throws HttpException
     * @return false|ResponseInterface|string
     */
    public function getLiveWeather($city,$format = 'json')
    {
        try {
            return $this->getWeather($city, 'base', $format);
        } catch (Exception $e) {
            throw new HttpException($e->getMessage(),$e->getCode(),$e);
        }
    }

    /**
     * 获取天气预报
     * @param  string|integer  $city
     * @param  string  $format
     * @throws HttpException
     * @return false|ResponseInterface|string
     */
    public function getForecastsWeather($city, $format = 'json')
    {
        try {
            return $this->getWeather($city, 'all', $format);
        } catch (Exception $e) {
            throw new HttpException($e->getMessage(),$e->getCode(),$e);
        }
    }
}
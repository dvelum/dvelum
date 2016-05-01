<?php

/**
 * Add-ons repository client
 */
class Externals_Client
{
    /**
     * @var Config_Abstract
     */
    protected $config;
    /**
     * @var string
     */
    protected $language = 'en';
    /**
     * @var Lang
     */
    protected $lang;

    public function __construct(Config_Abstract $repoConfig)
    {
        $this->config = $repoConfig;
    }

    /**
     * Set client language
     * @param $lang
     */
    public function setLanguage($lang)
    {
        $this->language= $lang;
    }

    /**
     * set localization dictionary
     * @param Lang $lang
     */
    public function setLocalization(Lang $lang)
    {
        $this->lang = $lang;
    }

    /**
     * Request remote repository
     * @param string $url
     * @param array $params
     * @param string $method
     * @throws Exception
     * @return array
     */
    protected function request($url,array $params = [], $method='POST')
    {
        $curl = curl_init();

        curl_setopt_array($curl,[
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER =>  0,
            CURLOPT_SSL_VERIFYHOST =>   0,
            CURLOPT_FOLLOWLOCATION =>  1,
            CURLOPT_HEADER =>  0,
            CURLOPT_HTTPHEADER =>  ['Content-Type: application/json'],
            CURLOPT_CONNECTTIMEOUT =>  20,
            CURLOPT_HTTPGET  =>  true,
            // Auth options
            // CURLOPT_HTTPAUTH, CURLAUTH_ANY,
            // CURLOPT_USERPWD, 'user:password',
        ]);

        switch($method)
        {
            case 'POST':
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                    curl_setopt($curl, CURLOPT_POST, 1);
                break;
            case 'PUT':
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
        }

        $result = curl_exec($curl);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if($httpCode!==200){
            throw new Exception($this->lang->get('error_connection') . ' RESPONSE CODE: ' . $httpCode);
        }

        if(!$result){
            throw new Exception($this->lang->get('error_connection') . ': ' . curl_error($curl));
        }

        curl_close($curl);

        if(strlen($result))
            $result = json_decode($result , true);

        if(!is_array($result)){
            throw new Exception($this->lang->get('error_invalid_response'));
        }

        return $result;
    }

    /**
     * Download add-on file
     * @param string $url
     * @param string $path
     * @throws Exception
     */
    protected function downloadRequest($url, $path)
    {
        set_time_limit(0);
        $fp = fopen($path, 'w+');

        $curl = curl_init();

        curl_setopt_array($curl,[
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER =>  0,
            CURLOPT_SSL_VERIFYHOST =>   0,
            CURLOPT_FOLLOWLOCATION =>  1,
            CURLOPT_HEADER =>  0,
            CURLOPT_CONNECTTIMEOUT =>  60,
            CURLOPT_FILE => $fp
            // Auth options
            // CURLOPT_HTTPAUTH, CURLAUTH_ANY,
            // CURLOPT_USERPWD, 'user:password',
        ]);

        $result = curl_exec($curl);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($httpCode!==200){
            throw new Exception($this->lang->get('error_connection') . ' RESPONSE CODE: ' . $httpCode);
        }

        if(!$result){
            throw new Exception($this->lang->get('error_connection') . ': ' . curl_error($curl));
        }
        curl_close($curl);
    }

    /**
     * Get add-ons list
     * @param array $params
     * @throws Exception
     * @return array
     */
    public function getList(array $params)
    {
        $requestUrl = $this->config->get('url').'list/';
        return $this->request($requestUrl, $params);
    }

    /**
     * Download add-on
     * @param string $app
     * @param string $version
     * @param string $saveTo
     * @return boolean
     */
    public function download($app, $version, $saveTo)
    {
        $requestUrl = $this->config->get('url').'download/'.$app.'/'.$version;
        $this->downloadRequest($requestUrl, $saveTo);
        return file_exists($saveTo);
    }
}
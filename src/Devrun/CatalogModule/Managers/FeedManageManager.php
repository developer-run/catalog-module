<?php


namespace Devrun\CatalogModule\Managers;

use Devrun\CatalogModule\Entities\ProductEntity;
use Devrun\CatalogModule\Repositories\ProductRepository;
use Kdyby\Translation\Translator;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Class FeedManager
 * @package Devrun\CatalogModule\Managers
 */
class FeedManageManager extends AbstractFeedManager implements IFeedManage
{

    /** @var string */
    private $_feedXmlUrl = 'unknown';

    /** @var string css selector */
    protected $filteredSelector = "";

    /** @var string */
    protected $feedExpireTime;

    /** @var string */
    protected $htmlExpireTime;

    /** @var bool */
    protected $filteredHtmlInCache = false;

    /** @var Cache */
    protected $cacheFeed;

    /** @var Cache */
    protected $cacheHtml;

    /** @var Translator @inject */
    public $translator;

    /** @var ProductRepository @inject */
    public $productRepository;





    /**
     * DI setter
     * @param string $feedXmlUrl
     */
    public function setFeedXmlUrl(string $feedXmlUrl)
    {
        $this->_feedXmlUrl = $feedXmlUrl;
    }

    /**
     * DI setter
     * @param string $feedExpireTime
     */
    public function setFeedExpireTime(string $feedExpireTime)
    {
        $this->feedExpireTime = $feedExpireTime;
    }

    /**
     * DI setter
     * @param string $htmlExpireTime
     */
    public function setHtmlExpireTime(string $htmlExpireTime)
    {
        $this->htmlExpireTime = $htmlExpireTime;
    }

    /**
     * DI setter
     * @param bool $filteredHtmlInCache
     */
    public function setFilteredHtmlInCache(bool $filteredHtmlInCache)
    {
        $this->filteredHtmlInCache = $filteredHtmlInCache;
    }




    /**
     * @param IStorage $storage
     */
    public function injectStorage(IStorage $storage)
    {
        $this->cacheFeed = new Cache($storage, 'feeds');
        $this->cacheHtml = new Cache($storage, 'html');
    }


    /**
     * @return false|mixed|string
     * @throws \Throwable
     */
    public function readFeed()
    {
        if (!$fileContent = $this->cacheFeed->load($this->_feedXmlUrl)) {
            if ($fileContent = file_get_contents($this->_feedXmlUrl)) {
                $this->cacheFeed->save($this->_feedXmlUrl, $fileContent, [
                    Cache::EXPIRE => $this->feedExpireTime,
                ]);
            }
        }

        return $fileContent;
    }


    /**
     * @param string $url
     * @return string
     * @throws \Throwable
     */
    public function getFeedContent(string $url)
    {
        if (!$wwwContent = $this->cacheHtml->load($url)) {

            $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
            $options    = array(
                CURLOPT_CUSTOMREQUEST  => "GET",        //set request type post or get
                CURLOPT_POST           => false,        //set to GET
                CURLOPT_USERAGENT      => $user_agent, //set user agent
                CURLOPT_COOKIEFILE     => "cookie.txt", //set cookie file
                CURLOPT_COOKIEJAR      => "cookie.txt", //set cookie jar
                CURLOPT_RETURNTRANSFER => true,     // return web page
                CURLOPT_HEADER         => false,    // don't return headers
                CURLOPT_FOLLOWLOCATION => true,     // follow redirects
                CURLOPT_ENCODING       => "",       // handle all encodings
                CURLOPT_AUTOREFERER    => true,     // set referer on redirect
                CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
                CURLOPT_TIMEOUT        => 120,      // timeout on response
                CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            );

            $ch = curl_init($url);
            curl_setopt_array($ch, $options);
            $wwwContent = curl_exec($ch);
            $err        = curl_errno($ch);
            $errmsg     = curl_error($ch);
            $header     = curl_getinfo($ch);
            curl_close($ch);

            if ($header['http_code'] == 200) {
                $wwwContent = iconv('windows-1250', 'UTF-8', $wwwContent);

                if ($this->filteredHtmlInCache) {
                    if ($this->filteredSelector) {
                        $this->htmlFilter($wwwContent, $this->filteredSelector);
                    }
                }

                if ($wwwContent) {
                    $this->cacheHtml->save($url, $wwwContent, [
                        Cache::EXPIRE => $this->htmlExpireTime,
                    ]);
                }
            }
        }

        if (!$this->filteredHtmlInCache) {
            if ($this->filteredSelector) {
                $this->htmlFilter($wwwContent, $this->filteredSelector);
            }
        }

        return $wwwContent;
    }


    protected function htmlFilter(string & $html, string $filter = null)
    {
        if ($filter) {
            $crawler = HtmlPageCrawler::create($html);
            $article = $crawler->filter($filter);

            if ($article->count() == 1) {
                $html = $article->saveHTML();
            }
        }
    }



    /**
     * @return array
     */
    public function getDataSource()
    {
        return [];
    }

    /**
     * @param $id
     * @return void
     */
    public function synchronize($id)
    {

    }


    /**
     * @param IFeed $feed
     * @return string hash object
     */
    public function getSerial(IFeed $feed)
    {
        return hash('sha256', 0);
    }


    /**
     * @param ProductEntity $productEntity
     * @return void
     */
    protected function setSerial(ProductEntity &$productEntity)
    {
        $productEntity->setSerial(hash('sha256', 0));
    }



}
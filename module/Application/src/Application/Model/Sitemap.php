<?php
namespace Application\Model;

class Sitemap
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;
    protected $settings;

    public function __construct()
    {
        if(isset($_GET['images']) && $_GET['images'] == 1) {
            $this->xml = new \SimpleXMLElement(
                '<urlset'
                . ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
                . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'
                . '></urlset>');

            $imagesNs = 'http://www.google.com/schemas/sitemap-image/1.1';
            $this->xml->registerXPathNamespace('image', $imagesNs);

        } else {
            $this->xml = new \SimpleXMLElement(
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        }

        $this->settings = Settings::getInstance();
    }

    public function addPage($options)
    {
        $urlXML = $this->xml->addChild('url');

        if($options['lastmod']) {
            $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $options['lastmod']);

            if(!$dt) {
                $dt = \DateTime::createFromFormat('Y-m-d', $options['lastmod']);
            }

            if(!$dt) {
                throw new \Exception('Wrong "lastmod" date format');
            }

            $urlXML->addChild('lastmod', $dt->format(\DateTime::W3C));
        }

        $url = $this->settings->get('domain') . '/' . ltrim($options['loc'], '/');

        $urlXML->addChild('loc', $url);
        $urlXML->addChild('changefreq', $options['changefreq']);
        $urlXML->addChild('priority', $options['priority']);

        return $urlXML;
    }

    public function addImage($url, $option)
    {
        $imagesNs = 'http://www.google.com/schemas/sitemap-image/1.1';

        $image = $url->addChild('image', null, $imagesNs);
        @$image->addChild('loc', $this->settings->get('domain') . $option['url'], $imagesNs);
        @$image->addChild('title', $option['name'], $imagesNs);
    }

    /**
     * @return mixed
     */
    public function getSitemap()
    {
        return $this->xml->asXML();
    }
}
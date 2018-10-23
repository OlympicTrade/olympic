<?php
namespace ApplicationAdmin\Model;

use Aptero\Db\Entity\EntityHierarchy;
use Aptero\Db\Entity\Entity;

class Settings extends EntityHierarchy
{
    public function __construct()
    {
        $this->setTable('site_settings');

        $this->addProperties([
            'site_name'   => [],
            'site_color_title'  => [],
            'site_color_bg'     => [],
            'site_logo'   => [],
            'domain'      => [],
            'html_head'   => [],
            'html_body'   => [],
            'html_css_js_version'   => [],
            'metriks'     => [],
            'robots'      => [],
            'mail_sender'    => [],
            'mail_email'     => [],
            'mail_password'  => [],
            'mail_smtp'   => [],
        ]);

        $this->getEventManager()->attach(array(Entity::EVENT_PRE_INSERT, Entity::EVENT_PRE_UPDATE), function ($event) {
            file_put_contents(PUBLIC_DIR . '/robots.txt', $event->getTarget()->get('robots'));

            return true;
        });
    }

    static protected $instance;
    static public function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
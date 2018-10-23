<?php

namespace User\Service;

use Aptero\Service\AbstractService;
use SocialAuther\SocialAuther;

class SocialService extends AbstractService
{
    public $adaptersConfig = array(
        'vk' => array(
            'client_id'     => '5864869',
            'client_secret' => 'GP7DZFTYF25RR54YJJlo',
            'redirect_uri'  => 'https://olympic-torch.ru/user/login-social/vk/'
        ),
        'odnoklassniki' => array(
            'client_id'     => '',
            'client_secret' => '',
            'redirect_uri'  => 'https://olympic-torch.ru/user/login-social/',
            'public_key'    => 'CBADCBMKABABABABA'
        ),
        'mailru' => array(
            'client_id'     => '',
            'client_secret' => '',
            'redirect_uri'  => 'https://olympic-torch.ru/user/login-social/'
        ),
        'yandex' => array(
            'client_id'     => '0067a41d62aa45a7ab29bbec96c3479d',
            'client_secret' => '0bb1d8f584c145a1b0b6c32531f2d25d',
            'redirect_uri'  => 'https://olympic-torch.ru/user/login-social/yandex/'
        ),
        'google' => array(
            'client_id'     => '508857585703-o8jje1jjsblo6ti11al5nh31cvr8d6ru.apps.googleusercontent.com',
            'client_secret' => 'qW-iJTxaXPNpsvRPolD5izML',
            'redirect_uri'  => 'https://olympic-torch.ru/user/login-social/google/'
        ),
        'facebook' => array(
            'client_id'     => '1365292783560644',
            'client_secret' => '422494d7ac13ca2dc68f18b7fd3058f7',
            'redirect_uri'  => 'https://olympic-torch.ru/user/login-social/facebook/'
        )
    );

    /**
     * @var array
     */
    protected $adapters = [];

    /**
     * @param $adapter
     * @return $this|bool
     */
    public function setAdapter($adapter)
    {
        if(!isset($this->adaptersConfig[$adapter])) {
            return false;
        }

        $settings = $this->adaptersConfig[$adapter];
        $class = 'SocialAuther\Adapter\\' . ucfirst($adapter);

        $this->adapters[$adapter] = new $class($settings);
        
        return $this->adapters[$adapter];
    }

    /**
     * @return array
     */
    public function getAdapters()
    {
        if($this->adapters) {
            return $this->adapters;
        }

        foreach ($this->adaptersConfig as $adapter => $settings) {
            if(!empty($this->adapters[$adapter])) {
                continue;
            }
            
            $class = 'SocialAuther\Adapter\\' . ucfirst($adapter);
            $this->adapters[$adapter] = new $class($settings);
        }

        return $this->adapters;
    }

    /**
     * @param $adapter
     * @return SocialAuther
     */
    public function getAuther($adapter)
    {
        return new SocialAuther($adapter);
    }
}

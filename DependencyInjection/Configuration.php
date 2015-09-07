<?php

namespace DeejayPoolBundle\DependencyInjection;

use DeejayPoolBundle\DeejayPoolBundle;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\ScalarNode;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder();
        $digital_dj_poolRoot = $treeBuilder->root('deejay_pool');

        $digital_dj_poolRoot
            ->children()
                ->arrayNode(DeejayPoolBundle::PROVIDER_AVD)
                ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->getCredentialsDefinition())
                        ->append($this->getAvdConfigurationDefinition())
                    ->end()
                ->end()
                ->arrayNode(DeejayPoolBundle::PROVIDER_FP)
                ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->getCredentialsDefinition())
                        ->append($this->getFrpConfigurationDefinition())
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    public function isValidurl($v)
    {
        return filter_var($v, FILTER_VALIDATE_URL);
    }

    public function isValidRegex($v)
    {
        return filter_var($v, FILTER_VALIDATE_REGEXP);
    }

    /**
     * Credentials configuration part
     * For AvDistrict and Franchise Record Pool Providers
     * @return ArrayNodeDefinition
     */
    public function getCredentialsDefinition()
    {
        $node = new ArrayNodeDefinition('credentials');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('login')
                ->info('Login of your account')
                ->cannotBeEmpty()
            ->end();
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('password')
                ->info('Password of your account')
                ->cannotBeEmpty()
            ->end();
        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    public function getAvdConfigurationDefinition()
    {
        $configurationDef = new ArrayNodeDefinition('configuration');
        $configurationDef

            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('root_path')
                ->info('Path of your video files')
                ->example('/your/path/to/download/target/files/')
                ->defaultValue('%kernel.cache_dir%')
                ->isRequired()
                ->validate()
                ->ifTrue(function ($v) {
                    if(strlen($v) > 0 && !is_dir($v)){
                        return true;
                    }
                    return 0;
                })
                ->thenInvalid('%s is not a valid folder.')
            ->end();
        $configurationDef
            ->children()
                ->scalarNode('items_properties')
                ->defaultValue('videoid,title,artist,genres,bpm,posted,credits,hd,advisory,filename,approved,editor,downloadid')
                ->cannotBeEmpty()
            ->end();
        $configurationDef
            ->children()
                ->scalarNode('login_check')
                    ->info('Login url for authentication')
                    ->defaultValue('http://www.avdistrict.net/Account/CheckLogin')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                        ->thenInvalid('%s is not a valid url, update yout login_check value.')
                    ->end()
                ->end()
                ->integerNode('items_per_page')
                    ->info('Items per page')
                    ->defaultValue(25)
                    ->cannotBeEmpty()
                ->end()
            ->end();
        $configurationDef
            ->children()
                ->scalarNode('login_form_name')
                    ->info('Name of login field into form')
                    ->defaultValue('email')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('password_form_name')
                    ->info('Name of password field into form')
                    ->defaultValue('password')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('items_url')
                    ->info('Items page')
                    ->defaultValue('http://www.avdistrict.net/Videos/LoadVideosData')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                        ->thenInvalid('%s is not a valid url, update yout item_url value.')
                    ->end()
                ->end()
                ->scalarNode('download_url')
                    ->info('Download url')
                    ->defaultValue('http://www.avdistrict.net/Handlers/DownloadHandler.ashx')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                        ->thenInvalid('%s is not a valid url, update yout download_url value.')
                    ->end()
                ->end()
                ->scalarNode('donwload_keygen_url')
                    ->info('Download keygen url')
                    ->defaultValue('http://www.avdistrict.net/Videos/InitializeDownload')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                        ->thenInvalid('%s is not a valid url, update yout download_keygen_url value.')
                    ->end()
                ->end()
                ->scalarNode('charts_url')
                    ->info('Chart page')
                    ->defaultValue('https://digitaldjpool.com/RecordPool.aspx/MoreMusic?keyword=&genre=&genreCustom=&isGenreCustomOn=false&version=&ascending=false&orderBy=Date&view=Charts&release=&_=1434313544223')
                    ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                    ->thenInvalid('%s is not a valid url, update yout login_check value.')
                    ->end()
                ->end()
                ->scalarNode('trend_url')
                    ->info('Trend page')
                    ->defaultValue('https://digitaldjpool.com/RecordPool.aspx/MoreMusic?keyword=&genre=&genreCustom=&isGenreCustomOn=false&version=&bpmFrom=50&bpmTo=150&ascending=false&orderBy=Date&view=Trending&release=')
                    ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                    ->thenInvalid('%s is not a valid url, update yout login_check value.')
                    ->end()
                ->end()
            ->end();
        return $configurationDef;
    }
    /**
     * @return ArrayNodeDefinition
     */
    public function getFrpConfigurationDefinition()
    {
        $configurationDef = new ArrayNodeDefinition('configuration');
        $configurationDef

            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('root_path')
                ->info('Path of your video files')
                ->example('/your/path/to/download/target/files/')
                ->defaultValue('%kernel.cache_dir%')
                ->isRequired()
                ->validate()
                ->ifTrue(function ($v) {
                    if(strlen($v) > 0 && !is_dir($v)){
                        return true;
                    }
                    return 0;
                })
                ->thenInvalid('%s is not a valid folder.')
            ->end();
        $configurationDef
            ->children()
                ->scalarNode('login_check')
                    ->info('Login url for authentication')
                    ->defaultValue('https://www.franchiserecordpool.com/signin')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                        ->thenInvalid('%s is not a valid url, update yout login_check value.')
                    ->end()
                ->end()
            ->end();
        $configurationDef
            ->children()
                ->scalarNode('login_form_name')
                    ->info('Name of login field into form')
                    ->defaultValue('login')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('password_form_name')
                    ->info('Name of password field into form')
                    ->defaultValue('password')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('items_url')
                    ->info('Items page')
                    ->defaultValue('http://www.franchiserecordpool.com/track/list?_search=false&nd=1441613741936&rows=100&page=1&sidx=tracks.created&sord=desc')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                        ->thenInvalid('%s is not a valid url, update yout item_url value.')
                    ->end()
                ->end()
                ->scalarNode('download_url')
                    ->info('Download url')
                    ->defaultValue('http://www.avdistrict.net/Handlers/DownloadHandler.ashx')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                        ->thenInvalid('%s is not a valid url, update yout download_url value.')
                    ->end()
                ->end()
                ->scalarNode('donwload_keygen_url')
                    ->info('Download keygen url')
                    ->defaultValue('http://www.avdistrict.net/Videos/InitializeDownload')
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                        ->thenInvalid('%s is not a valid url, update yout download_keygen_url value.')
                    ->end()
                ->end()
                ->scalarNode('charts_url')
                    ->info('Chart page')
                    ->defaultValue('https://digitaldjpool.com/RecordPool.aspx/MoreMusic?keyword=&genre=&genreCustom=&isGenreCustomOn=false&version=&ascending=false&orderBy=Date&view=Charts&release=&_=1434313544223')
                    ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                    ->thenInvalid('%s is not a valid url, update yout login_check value.')
                    ->end()
                ->end()
                ->scalarNode('trend_url')
                    ->info('Trend page')
                    ->defaultValue('https://digitaldjpool.com/RecordPool.aspx/MoreMusic?keyword=&genre=&genreCustom=&isGenreCustomOn=false&version=&bpmFrom=50&bpmTo=150&ascending=false&orderBy=Date&view=Trending&release=')
                    ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($v) { return !$this->isValidurl($v); })
                    ->thenInvalid('%s is not a valid url, update yout login_check value.')
                    ->end()
                ->end()
            ->end();
        return $configurationDef;
    }
}

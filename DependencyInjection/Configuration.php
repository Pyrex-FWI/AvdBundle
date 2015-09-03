<?php

namespace AvDistrictBundle\DependencyInjection;

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
        $digital_dj_poolRoot = $treeBuilder->root('avd');
        $digital_dj_poolRoot
            ->children()
                ->append($this->getCredentialsDefinition())->end();
        $digital_dj_poolRoot
            ->children()
                ->append($this->getConfigurationDefinition())->end();
        $digital_dj_poolRoot
            ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('console')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('pool_size')
                                ->defaultValue(200)
                            ->end()
                            ->arrayNode('collection_dumper')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('shell_command')
                                        ->defaultValue("find %avd.configuration.root_path%  -type f | grep -E '([0-9]{1,8})_.*mp3$' | sort")
                                    ->end()
                                    ->scalarNode('dump_file')
                                        ->defaultValue('%kernel.cache_dir%/files_list.txt')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('stream')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('type')
                                ->defaultValue('remote')
                                ->cannotBeEmpty()
                                ->validate()
                                    ->ifNotInArray(array('remote', 'local'))
                                    ->thenInvalid('Invalid Stream type "%s"')
                                ->end()
                            ->end()
                    ->scalarNode('route')->end()
                ->end()
            ->end()
            ->arrayNode('extract')
                ->addDefaultsIfNotSet()
                ->children()
                    ->integerNode('max_size')
                        ->defaultValue('2147483648')//2GO
                        ->cannotBeEmpty()
                    ->end()
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
     * @return ArrayNodeDefinition
     */
    public function getCredentialsDefinition()
    {
        $node = new ArrayNodeDefinition('credentials');
        $node
            ->children()
                ->scalarNode('login')
                ->info('Login of your account')
                ->cannotBeEmpty()
            ->end();
        $node
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
    public function getConfigurationDefinition()
    {
        $configurationDef = new ArrayNodeDefinition('configuration');
        $configurationDef
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
}

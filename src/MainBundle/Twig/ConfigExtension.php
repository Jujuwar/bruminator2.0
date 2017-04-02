<?php

namespace MainBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\Kernel;

class ConfigExtension extends \Twig_Extension {

    protected $doctrine;
    protected $kernel;

    public function __construct( RegistryInterface $doctrine, Kernel $kernel )
    {
        $this->doctrine = $doctrine;
        $this->kernel = $kernel;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter( 'config', array( $this, 'getValue' ) )
        );
    }

    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction( 'isEndRonde', array( $this, 'isEndRonde' ) ),
            new \Twig_SimpleFunction( 'startFinal', array( $this, 'startFinal' ) ),
            new \Twig_SimpleFunction( 'streamOn', array( $this, 'streamOn' ) ),
            new \Twig_SimpleFunction( 'getEditionDates', array( $this, 'getEditionDates' ) )
        );
    }

    public function getValue( $name ) {
        $em = $this->doctrine->getManager();

        $value = $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => $name ) );

        if( json_decode( $value ) )
            return json_decode( $value, true );
        else
            return $value;
    }

    public function getEditionDates( $edition = null ) {
        $em = $this->doctrine->getManager();

        if( is_null( $edition ) )
            $edition = $em->getRepository( 'MainBundle:Edition' )->findLastEdition();
        else
            $edition = $em->getRepository( 'MainBundle:Edition' )->findOneBy( array( 'name' => $edition ) );

        return $edition->getData()[ 'date' ];
    }

    public function isEndRonde() {
        $em = $this->doctrine->getManager();

        $rondes = json_decode( $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'rondes' ) ) );
        $ronde = json_decode( $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'ronde'.$rondes->ronde_actuelle ) ) );

        if( $rondes->ronde_actuelle < $rondes->total ) {
            $now = new \DateTime( 'now' );
            $endRonde = \DateTime::createFromFormat( 'Y-m-d', $ronde->end_date );

            if ( $now >= $endRonde )
                return true;
            else
                return false;
        } else
            return false;
    }

    public function startFinal() {
        $em = $this->doctrine->getManager();

        $rondes = json_decode( $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'rondes' ) ) );
        $ronde = json_decode( $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'ronde'.$rondes->total ) ) );

        if( $rondes->ronde_actuelle == $rondes->total ) {
            $now = new \DateTime( 'now' );
            $endRonde = \DateTime::createFromFormat( 'Y-m-d', $ronde->end_date );

            $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findBy( array( 'type' => 'Quart de finale' ) );

            if ( $now >= $endRonde && count( $matchs ) == 0 )
                return true;
            else
                return false;
        } else
            return false;
    }

    public function streamOn() {
        $fileTwitch = $this->kernel->getCacheDir().'/twitch.tmp';
        if( !file_exists( $fileTwitch ) )
            file_put_contents( $this->kernel->getCacheDir().'/twitch.tmp', '' );

        $channel = null;

        if( time() - filemtime( $fileTwitch > 60 ) || filesize( $fileTwitch ) == 0 ) {
            $channelsApi = 'https://api.twitch.tv/kraken/streams/';
            $channelName = 'bruminator_officiel';
            $clientId = '74dease2xl8ecabhx0ocfbonno2toq';
            $ch = curl_init();

            curl_setopt_array($ch, array(
                CURLOPT_HTTPHEADER => array(
                    'Client-ID:'.$clientId
                ),
                CURLOPT_SSL_VERIFYPEER=>false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $channelsApi.$channelName
            ));

            $channel = curl_exec($ch);
            curl_close($ch);

            file_put_contents( $fileTwitch, $channel );
        } else {
            $channel = file_get_contents( $fileTwitch );
        }

        $channel = json_decode( $channel );

        return !is_null( $channel ) && !is_null( $channel->stream );
    }

    public function getName() {
        return 'config_extension';
    }
}

<?php

namespace TeamBundle\Utils;

class ControlTeam {

    protected $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function checkCompo( $players ) {
        $errors = array();

        $errors = array_merge( $errors, $this->checkPillier( $players ) );
        $errors = array_merge( $errors, $this->checkBannedClass( $players ) );
        $errors = array_merge( $errors, $this->checkDoublon( $players ) );

        return $errors;
    }

    private function checkPillier( $players ) {
        $errors = array();
        $pilliers = 0;

        foreach( $players as $k => $v ) {

            if( !$v->getIsRemplacant() && $v->getClass()->getIsPillier() )
                $pilliers++;
        }

        if( $pilliers > 1 )
            $errors[][ 'message' ] = "Vous ne pouvez pas avoir plus d'une classe pillier";

        return $errors;
    }

    private function checkBannedClass( $players ) {
        $errors = array();
        $bannedClass = 0;
        $classes = array();

        foreach( $players as $k => $v ) {
            if( !$v->getIsRemplacant() ) {
                $tempClassBanned = json_decode($v->getClass()->getBannedClass());
                $classes[] = $v->getClass();

                if (!is_null($tempClassBanned)) {
                    foreach ($tempClassBanned as $k2 => $v2) {
                        $temp = $this->em->getRepository('TeamBundle:Classe')->findOneBy(array('id' => $v2));

                        if (in_array($temp, $classes))
                            $bannedClass++;
                    }
                }
            }
        }

        if( $bannedClass > 0 )
            $errors[][ 'message' ] = "Vous avez des classes ne pouvant pas être jouées ensemble";

        return $errors;
    }

    private function checkDoublon( $players ) {
        $errors = array();
        $classes = array();

        foreach( $players as $k => $v ) {
            if( !$v->getIsRemplacant() )
                $classes[] = $v->getClass();
        }

        if( count( array_unique( $classes, SORT_REGULAR ) ) < count( $classes ) )
            $errors[][ 'message' ] = "Vous ne pouvez pas avoir de classe doublon";

        return $errors;
    }
}
<?php

namespace MatchBundle\Repository;

/**
 * MatchsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MatchsRepository extends \Doctrine\ORM\EntityRepository
{
    function findByTeam( $id, $ordered = false, $onlyThisEdition = false ) {
        $qb = $this->createQueryBuilder( 'm' );
        $qb->where( $qb->expr()->orX()
            ->add( 'm.attack = ?1' )
            ->add( 'm.defense = ?2' )
        );
        if( $onlyThisEdition ) {
            $qb->andWhere( 'm.date >= ?3' );
            $qb->andWhere( 'm.type != ?4' );
            $date = $this->getEntityManager()->getRepository( 'MainBundle:Edition' )->getDate( 'inscription' ,'end' );
            $qb->setParameter( '3', $date );
            $qb->setParameter( '4', 'Match de barrage' );
        }
        if( $ordered )
            $qb->orderBy( 'm.date ', 'DESC');
        $qb->setParameter( '1', $id );
        $qb->setParameter( '2', $id );

        return $qb->getQuery()->getResult();
    }

    function findByDate( \DateTime $time, $order = array(), $limit = null, $null = true, $withTime = false ) {
        $qb = $this->createQueryBuilder( 'm' );
        if( $null ) {
            $qb->where($qb->expr()->orX()
                ->add( 'm.date >= ?1' )
                ->add( 'm.date IS NULL' )
            );
        } else {
            $qb->where( 'm.date >= ?1' );
        }
        if( !empty( $order ) )
            $qb->orderBy( 'm.'.$order[ 'field' ], $order[ 'type' ] );

        if( $limit )
            $qb->setMaxResults( $limit );

        $qb->setParameter( '1', $time->format( $withTime ? 'Y-m-d H:i' : 'Y-m-d' ) );

        return $qb->getQuery()->getResult();
    }

    function findByDateInf( \DateTime $time, $order = array(), $limit = null, $null = true, $withTime = false ) {
        $qb = $this->createQueryBuilder( 'm' );
        if( $null ) {
            $qb->where($qb->expr()->orX()
                ->add( 'm.date <= ?1' )
                ->add( 'm.date IS NULL' )
            );
        } else {
            $qb->where( 'm.date <= ?1' );
        }
        if( !empty( $order ) )
            $qb->orderBy( 'm.'.$order[ 'field' ], $order[ 'type' ] );

        if( $limit )
            $qb->setMaxResults( $limit );

        $qb->setParameter( '1', $time->format( $withTime? 'Y-m-d H:i' : 'Y-m-d' ) );

        return $qb->getQuery()->getResult();
    }

    function findMatchsPhasesFinales( $edition ) {
        $qb = $this->createQueryBuilder( 'm' );

        $qb->where($qb->expr()->orX()
            ->add( 'm.type = ?1' )
            ->add( 'm.type = ?2' )
            ->add( 'm.type = ?3' )
            ->add( 'm.type = ?4' )
        );

        if( !empty( $edition ) ) {
            $qb->andWhere( 'm.edition = ?5' );
            $qb->setParameter( '5', $edition );
        }

        $qb->orderBy( 'm.id' );

        $qb->setParameter( '1', 'Quart de finale' );
        $qb->setParameter( '2', 'Demi-finale' );
        $qb->setParameter( '3', 'Finale' );
        $qb->setParameter( '4', 'Petite finale' );

        return $qb->getQuery()->getResult();
    }

    function findMatchWithoutResult( $edition ) {
        $qb = $this->createQueryBuilder( 'm' );

        $qb->leftJoin( 'm.matchResult', 'mr', 'mr.match = m.id' )
            ->where( 'mr.id IS NULL');

        if( !empty( $edition ) ) {
            $qb->andWhere( 'm.edition = ?1' );
            $qb->setParameter( '1', $edition );
        }

        return $qb->getQuery()->getResult();
    }

    function findMatchAfterBarrage( $edition ) {
        $qb = $this->createQueryBuilder( 'm' );

        $qb->where('m.defense is null' );

        if( !empty( $edition ) ) {
            $qb->andWhere( 'm.edition = ?1' );
            $qb->setParameter( '1', $edition );
        }

        return $qb->getQuery()->getSingleResult();
    }
}

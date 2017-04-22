<?php

namespace TeamBundle\Repository;

/**
 * PlayerHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PlayerHistoryRepository extends \Doctrine\ORM\EntityRepository
{
    public function retrievePlayerClass( $player, $edition ) {
        $qb = $this->createQueryBuilder( 'h' );

        $qb->where( $qb->expr()->andX()
                ->add( 'h.player = ?1')
                ->add( 'h.date <= ?2' )
        );

        $qb->orderBy( 'h.date', 'DESC');

        $qb->setParameter( '1', $player );
        $qb->setParameter( '2', $edition->getData()['date']->inscription->end );

        return $qb->getQuery()->getOneOrNullResult();
    }
}

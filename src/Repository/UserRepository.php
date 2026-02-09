<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return User[]
     */
    public function findForAdmin(?string $search, ?string $role, ?string $status, string $sort): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($search) {
            $qb
                ->andWhere('LOWER(u.email) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        if ($role === 'admin') {
            $qb->andWhere('u.roles LIKE :adminRole')->setParameter('adminRole', '%ROLE_ADMIN%');
        } elseif ($role === 'host') {
            $qb
                ->andWhere('u.roles LIKE :hostRole')
                ->andWhere('u.roles NOT LIKE :adminRole')
                ->setParameter('hostRole', '%ROLE_HOST%')
                ->setParameter('adminRole', '%ROLE_ADMIN%');
        } elseif ($role === 'guest') {
            $qb
                ->andWhere('u.roles NOT LIKE :adminRole')
                ->andWhere('u.roles NOT LIKE :hostRole')
                ->setParameter('adminRole', '%ROLE_ADMIN%')
                ->setParameter('hostRole', '%ROLE_HOST%');
        }

        if ($status === 'active') {
            $qb->andWhere('u.isVerified = true');
        } elseif ($status === 'inactive') {
            $qb->andWhere('u.isVerified = false');
        }

        $this->applySort($qb, $sort);

        return $qb->getQuery()->getResult();
    }

    public function getAdminStats(): array
    {
        $total = (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $active = (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.isVerified = true')
            ->getQuery()
            ->getSingleScalarResult();

        $inactive = (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.isVerified = false')
            ->getQuery()
            ->getSingleScalarResult();

        $admins = (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getSingleScalarResult();

        $hosts = (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.roles LIKE :role')
            ->andWhere('u.roles NOT LIKE :adminRole')
            ->setParameter('role', '%ROLE_HOST%')
            ->setParameter('adminRole', '%ROLE_ADMIN%')
            ->getQuery()
            ->getSingleScalarResult();

        $guests = max($total - $admins - $hosts, 0);

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'admins' => $admins,
            'hosts' => $hosts,
            'guests' => $guests,
        ];
    }

    private function applySort(\Doctrine\ORM\QueryBuilder $qb, string $sort): void
    {
        if ($sort === 'email_asc') {
            $qb->orderBy('u.email', 'ASC');
            return;
        }

        if ($sort === 'email_desc') {
            $qb->orderBy('u.email', 'DESC');
            return;
        }

        if ($sort === 'role') {
            $qb->addOrderBy(
                "CASE
                    WHEN u.roles LIKE '%ROLE_ADMIN%' THEN 0
                    WHEN u.roles LIKE '%ROLE_HOST%' THEN 1
                    ELSE 2
                END",
                'ASC'
            );
            $qb->addOrderBy('u.email', 'ASC');
            return;
        }

        if ($sort === 'status') {
            $qb->orderBy('u.isVerified', 'DESC')->addOrderBy('u.email', 'ASC');
            return;
        }

        if ($sort === 'oldest') {
            $qb->orderBy('u.id', 'ASC');
            return;
        }

        $qb->orderBy('u.id', 'DESC');
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

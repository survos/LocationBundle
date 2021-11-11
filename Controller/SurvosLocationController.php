<?php

namespace Survos\LocationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Survos\LocationBundle\Entity\Location;
use Survos\LocationBundle\Repository\LocationRepository;
use Survos\LocationBundle\Service\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SurvosLocationController extends AbstractController
{
    protected Service $service;
    private LocationRepository $locationRepository;

    public function __construct(Service $service = null, LocationRepository $locationRepository)
    {
        $this->service = $service;
        $this->locationRepository = $locationRepository;
    }


//    #[Route(path: '/location-json.{_format}', name: 'location_json', defaults: ['_format' => 'html'])]
    public function locationJson(Request $request)
    {
        $limit = $request->query->get('limit', 30);
//        $locationRepository = $this->getDoctrine()->getRepository(Location::class);
        /** @var QueryBuilder $qb */
        $qb = $this->locationRepository->createQueryBuilder('l');

        $lvl = $request->get('lvl', null);
        if (is_numeric($lvl)) {
            $qb->andWhere('l.lvl = :lvl')
                ->setParameter('lvl', $lvl);
        }

        // count the slashes to increase the level.  get parent
        if ($q = $request->get('q')) {
            $parts = explode('/', $q);

            // US/NC filters by US-NC

            $partCount = count($parts);


            foreach($parts as $idx=>$part) {
                if (empty($part)) {
                    continue;
                }
                // exact match on code
//                dd($parts, $partCount);
                // last item in search can be for names too
//                if ($idx == $partCount-1) {
//                    $qb->andWhere('(l.name LIKE :part) OR (l.code like :part)')
//                        ->setParameter('part', '%' . $part . '%');
//
//                }
                if (0)
                    if ($idx <= $partCount) {
                        // build up the parent here, add it later to the query.
                        $parent = $this->locationRepository->findOneBy(['code' => $part]);
                        assert($parent, "Invalid parent code " . $part);

                        $qb->andWhere("l.parent = :parent")
                            ->setParameter('parent', $parent);
                    }
//                if ($parentCode = $request->get('parentCode')) {
//                    $parent = $locationRepository->findBy(['code' => $parentCode]);
//                }

                // us/nc or /nc
//                    $qb->andWhere("(l.lvl= :partLvl$idx) and (l.alpha2 = :part$idx)")
//                        ->setParameter('partLvl' . $idx,  $idx)
//                        ->setParameter('part' . $idx, $part);
            }

            // us/nc us/north carolina
            // us//chicago
            // //chicago

//            $qb->andWhere('(l.name LIKE :q) OR (l.code like :q)')
//                ->setParameter('q', '%' . $q . '%');
        }

        if ($parentCode = $request->get('parentCode')) {
            $parent = $this->locationRepository->findBy(['code' => $parentCode]);
            $qb->andWhere('l.parent = :parent')
                ->setParameter('parent', $parent);
        }

        $qb->setMaxResults($limit);
//        dd($qb->getQuery(), $qb->getQuery()->getParameters());
        $locations = $qb->getQuery()->getResult();
//        dd($q, $qb->getQuery()->getParameters(), $qb->getQuery()->getDQL(), $qb->getQuery()->getSQL(), $qb->getQuery()->getSQL(), count($locations), $locations);
        $data = [];
        /** @var Location $location */
        foreach ($locations as $location) {
            $data[] = [
                'id' => $location->getCode(),
                'text' => trim(sprintf("%s %s (%s) / %d #%d",
                        $location->getCode(),
                        $location->getName(), $location->getParent() !== null ? $location->getParent()->getCode() : '~', $location->getLvl(), $location->getId())
                )
            ];
        }
        return $this->json($data);
    }

    public function foo(RequestStack $requestStack, $a, $b)
    {
        $request = $requestStack->getCurrentRequest();

        if (!$request->isXmlHttpRequest()) {
            throw new AccessDeniedException();
        }

        try {
            // TODO: Your service call
            $result = $this->service->foo($a, $b);
        } catch (AccessDeniedException $e) {
            // TODO: Catch exception access denied
            return $this->json($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            // TODO: Catch unknown exception
            return $this->json($e->getMessage(), $e->getCode());
        }

        return $this->json($result, 200);
    }

    public function createCar(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();

        if (!$request->isXmlHttpRequest()) {
            throw new AccessDeniedException();
        }

        try {
            // TODO: Example using DTO
            $carDTO = CarDTO::fromRequest($request);
            $brand = $carDTO->getBrand();
            $model = $carDTO->getModel();

            $car = $this->service->createCar($brand, $model);

            $carDTO = CarDTO::toDTO($car);
            $response = CarDTO::toResponse($carDTO);

        } catch (AccessDeniedException $e) {
            // TODO: Catch exception access denied
            return $this->json($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            // TODO: Catch unknown exception
            return $this->json('Unknown exception', $e->getCode());
        }

        return $response;
    }
}

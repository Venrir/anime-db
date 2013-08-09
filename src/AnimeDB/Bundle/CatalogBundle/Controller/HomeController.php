<?php
/**
 * AnimeDB package
 *
 * @package   AnimeDB
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDB\Bundle\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use AnimeDB\Bundle\CatalogBundle\Form\SearchSimple;
use AnimeDB\Bundle\CatalogBundle\Form\Search;
use Symfony\Component\HttpFoundation\Request;
use AnimeDB\Bundle\CatalogBundle\Entity\Type as TypeEntity;
use AnimeDB\Bundle\CatalogBundle\Entity\Country as CountryEntity;
use AnimeDB\Bundle\CatalogBundle\Entity\Genre as GenreEntity;
use AnimeDB\Bundle\CatalogBundle\Entity\Storage as StorageEntity;
use Doctrine\ORM\Query\Expr;
use AnimeDB\Bundle\CatalogBundle\Service\Pagination;

/**
 * Main page of the catalog
 *
 * @package AnimeDB\Bundle\CatalogBundle\Controller
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class HomeController extends Controller
{
    /**
     * Items per page on home page
     *
     * @var integer
     */
    const HOME_ITEMS_PER_PAGE = 8;

    /**
     * Items per page on search page
     *
     * @var integer
     */
    const SEARCH_ITEMS_PER_PAGE = 6;

    /**
     * Limits on the number of items per page for home page
     *
     * @var array
     */
    public static $home_show_limit = [8, 16, 32, -1];

    /**
     * Limits on the number of items per page for search page
     *
     * @var array
     */
    public static $search_show_limit = [6, 12, 24, -1];

    /**
     * Home
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        // current page for paging
        $page = $request->get('page', 1);
        $current_page = $page > 1 ? $page : 1;

        // get items limit
        $limit = (int)$request->get('limit', self::HOME_ITEMS_PER_PAGE);
        $limit = in_array($limit, self::$home_show_limit) ? $limit : self::HOME_ITEMS_PER_PAGE;

        // get query
        $repository = $this->getDoctrine()->getRepository('AnimeDBCatalogBundle:Item');
        $query = $repository->createQueryBuilder('i')->orderBy('i.id', 'DESC');

        $pagination = null;
        // show not all items
        if ($limit != -1) {
            $query
                ->setFirstResult(($current_page - 1) * $limit)
                ->setMaxResults($limit);

            // get count all items
            $count = $repository->createQueryBuilder('i')
                ->select('count(i.id)')
                ->getQuery()
                ->getSingleScalarResult();

            $that = $this;
            $pagination = $this->get('anime_db.pagination')->createNavigation(
                ceil($count/$limit),
                $current_page,
                Pagination::DEFAULT_LIST_LENGTH,
                function ($page) use ($that) {
                    return $that->generateUrl('home', ['page' => $page]);
                },
                $this->generateUrl('home')
            );
        }

        // get items
        $items = $query->getQuery()->getResult();

        // assembly parameters limit output
        $show_limit = [];
        foreach (self::$home_show_limit as $value) {
            $show_limit[] = [
                'link' => $this->generateUrl('home', ['limit' => $value]),
                'name' => $value != -1 ? $value : 'All',
                'count' => $value,
                'current' => $limit == $value
            ];
        }

        return $this->render('AnimeDBCatalogBundle:Home:index.html.twig', [
            'items' => $items,
            'show_limit' => $show_limit,
            'pagination' => $pagination
        ]);
    }

    /**
     * Search simple form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchSimpleFormAction()
    {
        return $this->render('AnimeDBCatalogBundle:Home:searchSimpleForm.html.twig', [
            'form' => $this->createForm(new SearchSimple())->createView(),
        ]);
    }

    /**
     * Autocomplete name
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function autocompleteNameAction(Request $request)
    {
        $term = $request->get('term');

        // TODO do search
        $value = ['Foo', 'Bar'];

        return new JsonResponse($value);
    }

    /**
     * Search item
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        /* @var $form \Symfony\Component\Form\Form */
        $form = $this->createForm(new Search());
        $items = [];
        $pagination = null;

        if ($request->query->count()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                // build query
                /* @var $selector \Doctrine\ORM\QueryBuilder */
                $selector = $this->getDoctrine()
                    ->getRepository('AnimeDBCatalogBundle:Item')
                    ->createQueryBuilder('i');

                // main name
                if ($data['name']) {
                    // TODO create index name for rapid and accurate search
                    $selector
                        ->innerJoin('i.names', 'n')
                        ->andWhere('i.name LIKE :name OR n.name LIKE :name')
                        ->setParameter('name', str_replace('%', '%%', $data['name']).'%');
                }
                // date start
                if ($data['date_start'] instanceof \DateTime) {
                    $selector->andWhere('i.date_start >= :date_start')
                        ->setParameter('date_start', $data['date_start']->format('Y-m-d'));
                }
                // date end
                if ($data['date_end'] instanceof \DateTime) {
                    $selector->andWhere('i.date_end <= :date_end')
                        ->setParameter('date_end', $data['date_end']->format('Y-m-d'));
                }
                // manufacturer
                if ($data['manufacturer'] instanceof CountryEntity) {
                    $selector->andWhere('i.manufacturer = :manufacturer')
                        ->setParameter('manufacturer', $data['manufacturer']->getId());
                }
                // storage
                if ($data['storage'] instanceof StorageEntity) {
                    $selector->andWhere('i.storage = :storage')
                        ->setParameter('storage', $data['storage']->getId());
                }
                // type
                if ($data['type'] instanceof TypeEntity) {
                    $selector->andWhere('i.type = :type')
                        ->setParameter('type', $data['type']->getId());
                }
                // genres
                if ($data['genres']->count()) {
                    $keys = [];
                    foreach ($data['genres'] as $key => $genre) {
                        $keys[] = ':genre'.$key;
                        $selector->setParameter('genre'.$key, $genre->getId());
                    }
                    $selector->innerJoin('i.genres', 'g')
                        ->andWhere('g.id IN ('.implode(',', $keys).')');
                }

                // get count all items
                $count = clone $selector;
                $count = $count
                    ->select('COUNT(DISTINCT i)')
                    ->getQuery()
                    ->getSingleScalarResult();

                // current page for paging
                $current_page = $request->get('page', 1);
                $current_page = $current_page > 1 ? $current_page : 1;

                // get items limit
                $limit = (int)$request->get('limit', self::SEARCH_ITEMS_PER_PAGE);
                $limit = in_array($limit, self::$search_show_limit) ? $limit : self::SEARCH_ITEMS_PER_PAGE;

                // add order
                $data['sort_field'] = $data['sort_field'] ?: 'date_update';
                $data['sort_direction'] = $data['sort_direction'] ?: 'DESC';
                $selector
                    ->orderBy('i.'.$data['sort_field'], $data['sort_direction'])
                    ->orderBy('i.id', $data['sort_direction']);

                if ($limit != -1) {
                    $selector
                        ->setFirstResult(($current_page - 1) * $limit)
                        ->setMaxResults($limit);

                    // build pagination
                    $that = $this;
                    $pagination = $this->get('anime_db.pagination')->createNavigation(
                        ceil($count/$limit),
                        $current_page,
                        Pagination::DEFAULT_LIST_LENGTH,
                        function ($page) use ($that, $request) {
                            return $that->generateUrl(
                                'home_search',
                                ['search_items' => $request->query->get('search_items'), 'page' => $page]
                            );
                        },
                        $this->generateUrl('home_search', ['search_items' => $request->query->get('search_items')])
                    );
                }

                // get items
                $items = $selector
                    ->groupBy('i')
                    ->getQuery()
                    ->getResult();
            }
        }

        // assembly parameters limit output
        $show_limit = [];
        foreach (self::$search_show_limit as $value) {
            $show_limit[] = [
                'link' => $this->generateUrl(
                    'home_search',
                    ['search_items' => $request->query->get('search_items'), 'limit' => $value]
                ),
                'name' => $value != -1 ? $value : 'All',
                'count' => $value,
                'current' => !empty($limit) && $limit == $value
            ];
        }

        return $this->render('AnimeDBCatalogBundle:Home:search.html.twig', [
            'form'  => $form->createView(),
            'items' => $items,
            'show_limit' => $show_limit,
            'pagination' => $pagination
        ]);
    }
}
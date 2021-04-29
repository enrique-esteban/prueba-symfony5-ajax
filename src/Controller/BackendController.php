<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\Query\ResultSetMapping;

use App\Entity\Job;
use App\Entity\Category;

class BackendController extends AbstractController
{
    /**
     * Carga la página de inicio.
     */
    public function index(): Response
    {
        return $this->render('backend/index.html.twig');
    }
    
    /**
     * Recoge una petición Ajax para cargar los datos (Jobs and Categories) de la base de datos para
     *    su presentación como una tabla en la página de inicio.
     * 
     * @return Jsonresponse|Response
     */
    public function ajaxLoadJobs (Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {    
            $resultMapping = new ResultSetMapping();
            $connection = $this->getDoctrine()->getConnection();
            $query = 'SELECT job.name, GROUP_CONCAT(category.name) AS categories
                      FROM job
                      LEFT JOIN jobs_categories ON jobs_categories.job_id = job.id
                      LEFT JOIN category ON jobs_categories.category_id = category.id
                      GROUP BY job.id';

            $stmt = $connection->prepare($query);
            $stmt->execute();
            $jobs = $stmt->fetchAllAssociative();

            $serializer = $this->container->get('serializer');
            $jobsJson = $serializer->serialize($jobs, 'json');

            return new JsonResponse($jobsJson);
        }
        else {
            return new Response(null, 403);
        }
    }

    /**
     * Responde a una llamada Ajax para guardar una nueva taréa en la base de datos.
     * 
     * @return Jsonresponse|Response
     */
    public function ajaxSaveJob (Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {         
            $jobArray = $request->request->get('job');

            $jobInclude = $this->getDoctrine()->getRepository(Job::class)->findOneBy(['name' => $jobArray['name']]);
            
            if (!isset($jobInclude)) {
                $job = new Job();

                $job->setName($jobArray['name']);

                if (isset($jobArray['categories'])) {
                    foreach ($jobArray['categories'] as $value) {
                        $category = new Category();
                        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['name' => $value]);
                        $job->addCategory($category);
                    }
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($job);
                $entityManager->flush();

                $serializer = $this->container->get('serializer');
                $jobJson = $serializer->serialize($jobArray, 'json');

                return new JsonResponse([ 'data' => $jobJson, 'error' => false ]);
            }
            else {
                return new JsonResponse([ 'data' => '', 'error' => 'Esa taréa ya existe, por favor introduce una tarea nueva' ]);
            }

        }
        else {
            return new Response(null, 403);            
        }
    }

    /**
     * Responde a una llamada Ajax para eliminar una taréa de la base de datos.
     * 
     * @return Jsonresponse|Response
     */
    public function ajaxRemoveJob (Request $request): JsonResponse
    {
        if ($request->isXMLHttpRequest()) {         
            $jobName = $request->request->get('jobName');
            
            $job = $this->getDoctrine()->getRepository(Job::class)->findOneBy(['name' => $jobName]);

            // dump($job);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($job);
            $entityManager->flush();
            
            return new JsonResponse($job);
        }
        else {
            return new Response(null, 403);
        }
    }
}

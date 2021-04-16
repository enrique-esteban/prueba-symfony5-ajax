<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

use App\Entity\Job;
use App\Entity\Category;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class BackendController extends AbstractController
{
    public function index(): Response
    {
        $jobs = $this->getDoctrine()->getRepository(Job::class)->findAll();

        return $this->render('backend/index.html.twig', ['jobs' => $jobs]);
    }
    
    public function ajaxLoadJobs (Request $request)
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
            dump($jobs, $jobsJson);

            return new JsonResponse($jobsJson);
        }
    }

    public function ajaxSaveJob (Request $request)
    {
        //dump($request->isXMLHttpRequest());
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

                // $encoders = [new JsonEncoder()];
                // $normalizers = [new ObjectNormalizer()];
                // $serializer = new Serializer($normalizers, $encoders);

                // // Serialize your object in Json
                // $jobJson = $serializer->serialize($job, 'json', [
                //     'circular_reference_handler' => function ($object) {
                //         return $object->getName();
                //     }
                // ]);
                // dump($jobJson);

               return new JsonResponse([ 'data' => $jobJson, 'error' => false ]);
            }
            else {
                return new JsonResponse([ 'data' => '', 'error' => 'Ese taréa ya existe' ]);
            }

        }
        else {
            return new JsonResponse([ 'data' => '', 'error' => "Error: ..." ]); // TODO
        }
    }

    public function ajaxRemoveJob (Request $request)
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
    }
}

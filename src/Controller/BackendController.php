<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
// use Doctrine\ODM\MongoDB\DocumentManager;
use App\Entity\Job;
//use Symfony\Component\Routing\Annotation\Route;

class BackendController extends AbstractController
{
    public function index(): Response
    {
        $jobs = $this->getDoctrine()->getRepository(Job::class)->findAll();

        // dump($jobs);

        return $this->render('backend/index.html.twig', [
            'jobs' => $jobs
        ]);
    }

    public function ajaxSaveJob (Request $request)
    {
        //dump($request->isXMLHttpRequest());
        if ($request->isXMLHttpRequest()) {         
            $jobArray = $request->request->get('job');
            //dump($jobArray['name']);
            
            $entityManager = $this->getDoctrine()->getManager();

            $job = new Job();
            $job->setName($jobArray['name']);
            $job->setCategories($jobArray['categories']);

            $entityManager->persist($job);
            $entityManager->flush();
            
            // Metodo alternativo usando mysqli nativo php
          /*   $connect = new \mysqli("localhost", "root", "root", "prueba");
            if ($connect->connect_error) {
                die("Connection failed: " . $connect->connect_error);
            }
            
            $sql = "INSERT INTO Job VALUES (".rand().", '".$jobArray['name']."', '".$jobArray['name']."')";

            if (mysqli_query($connect, $sql)) {
               echo "Registro ingresado correctamente";
            } else {
               echo "Error: " . $sql . "" . mysqli_error($connect);
            }
            $connect->close();
            */

            return new JsonResponse($job);
        }
    
        return new Response('Ups, Algo ha ido mal', 400);
    }
}
